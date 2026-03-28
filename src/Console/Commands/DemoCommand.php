<?php

namespace ShamimStack\WwwPay\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use ShamimStack\WwwPay\Console\Installers\ConsoleStyle;
use ShamimStack\WwwPay\Console\Installers\GatewaySelector;

class DemoCommand extends Command
{
    protected $signature = 'payment:demo 
                            {type=store : Demo type (store, subscription, p2p, b2b)}
                            {--output= : Output directory}';

    protected $description = 'Generate demo pages for wwwpay payment integration';

    protected ConsoleStyle $io;
    protected Filesystem $files;

    protected array $demos = [
        'store' => [
            'name' => 'E-Commerce Store',
            'description' => 'Full store with checkout, cart, and multiple payment gateways',
            'files' => ['controller', 'views'],
        ],
        'subscription' => [
            'name' => 'Subscription Billing',
            'description' => 'Recurring payments and subscription management',
            'files' => ['controller', 'views'],
        ],
        'p2p' => [
            'name' => 'P2P Payments',
            'description' => 'Peer-to-peer transfers and money requests',
            'files' => ['controller', 'views'],
        ],
        'b2b' => [
            'name' => 'B2B Payments',
            'description' => 'Business-to-business invoices and wire transfers',
            'files' => ['controller', 'views'],
        ],
    ];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->io = new ConsoleStyle($this->input, $this->output);

        $type = $this->argument('type');

        if (!isset($this->demos[$type])) {
            return $this->showDemoList();
        }

        return $this->generateDemo($type);
    }

    protected function showDemoList(): int
    {
        $this->io->header('AVAILABLE DEMOS');

        $rows = [];
        foreach ($this->demos as $key => $demo) {
            $rows[] = [$key, $demo['name'], $demo['description']];
        }

        $this->io->table(['Key', 'Demo', 'Description'], $rows);

        $this->io->newLine();
        $this->io->writeln('Usage:');
        $this->io->writeln('  <fg=cyan>php artisan payment:demo store</> - Generate e-commerce demo');
        $this->io->writeln('  <fg=cyan>php artisan payment:demo subscription</> - Generate subscription demo');

        return Command::SUCCESS;
    }

    protected function generateDemo(string $type): int
    {
        $demo = $this->demos[$type];

        $this->io->header("GENERATING: {$demo['name']}");

        $this->io->writeln("Description: {$demo['description']}");
        $this->io->newLine();

        $outputDir = $this->option('output') ?? app_path();

        $this->generateController($type, $outputDir);
        $this->generateViews($type, $outputDir);
        $this->generateRoutes($type);
        $this->showIntegrationGuide($type);

        $this->io->successBox('Demo generated successfully!');

        return Command::SUCCESS;
    }

    protected function generateController(string $type, string $outputDir): void
    {
        $this->io->section('Creating Controller');

        $controllerName = ucfirst($type) . 'PaymentController';
        $controllerPath = "{$outputDir}/Http/Controllers/{$controllerName}.php";

        $stub = $this->getControllerStub($type);
        $content = $this->replacePlaceholders($stub, [
            'DummyController' => $controllerName,
        ]);

        $this->ensureDirectoryExists(dirname($controllerPath));
        
        if ($this->files->exists($controllerPath) && !$this->io->confirm('Controller exists. Overwrite?', false)) {
            $this->io->writeln("  <fg=yellow>Skipped existing controller</>");
            return;
        }

        $this->files->put($controllerPath, $content);
        $this->io->writeln("  <fg=green>✓</> Created: {$controllerPath}");
    }

    protected function generateViews(string $type, string $outputDir): void
    {
        $this->io->section('Creating Views');

        $viewsDir = "{$outputDir}/../resources/views/payments/{$type}";
        $this->ensureDirectoryExists($viewsDir);

        $views = $this->getViewsForType($type);

        foreach ($views as $viewName => $content) {
            $viewPath = "{$viewsDir}/{$viewName}.blade.php";
            
            if ($this->files->exists($viewPath) && !$this->io->confirm("View {$viewName} exists. Overwrite?", false)) {
                $this->io->writeln("  <fg=yellow>Skipped existing view: {$viewName}</>");
                continue;
            }

            $this->files->put($viewPath, $content);
            $this->io->writeln("  <fg=green>✓</> Created: resources/views/payments/{$type}/{$viewName}.blade.php");
        }
    }

    protected function generateRoutes(string $type): void
    {
        $this->io->section('Route Configuration');

        $this->io->writeln("Add these routes to <fg=green>routes/web.php</>:");
        $this->io->newLine();

        $routes = $this->getRoutesForType($type);
        foreach ($routes as $route) {
            $this->io->writeln("  <fg=cyan>{$route}</>");
        }

        $this->io->newLine();
    }

    protected function showIntegrationGuide(string $type): void
    {
        $guides = [
            'store' => [
                'Use Payment Facade: Payment::gateway(\'stripe\')->pay([...])',
                'Redirect to gateway checkout page',
                'Handle return/cancel URLs',
                'Process webhook for payment confirmation',
            ],
            'subscription' => [
                'Use Payment::gateway(\'stripe\')->subscribe([...])',
                'Manage subscription status via webhooks',
                'Handle failed payment retries',
            ],
            'p2p' => [
                'Use P2PPayment class for transfers',
                'P2PPayment::sendMoney([...])',
                'P2PPayment::requestMoney([...])',
            ],
            'b2b' => [
                'Use B2BPayment class for business payments',
                'B2BPayment::createInvoice([...])',
                'B2BPayment::wireTransfer([...])',
            ],
        ];

        $this->io->newLine();
        $this->io->section('Integration Guide');

        foreach ($guides[$type] ?? [] as $guide) {
            $this->io->writeln("  <fg=gray>▸</> {$guide}");
        }
    }

    protected function getControllerStub(string $type): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ShamimStack\WwwPay\Facades\Payment;
use ShamimStack\WwwPay\P2P\P2PPayment;
use ShamimStack\WwwPay\B2B\B2BPayment;

class DummyController extends Controller
{
    public function index()
    {
        return view('payments.{$type}.index');
    }

    public function checkout(Request \$request)
    {
        \$data = \$request->validate([
            'gateway' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        try {
            \$response = Payment::gateway(\$data['gateway'])->pay([
                'amount' => \$data['amount'],
                'currency' => \$data['currency'],
                'return_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
            ]);

            if (\$response->isRedirect()) {
                return redirect(\$response->getRedirectUrl());
            }

            if (\$response->isSuccessful()) {
                return redirect()->route('payment.success')->with('transaction_id', \$response->getTransactionId());
            }

            return redirect()->route('payment.cancel')->with('error', \$response->getErrorMessage());
        } catch (\\Exception \$e) {
            return redirect()->route('payment.cancel')->with('error', \$e->getMessage());
        }
    }

    public function success(Request \$request)
    {
        \$transactionId = \$request->session()->get('transaction_id');
        return view('payments.{$type}.success', compact('transactionId'));
    }

    public function cancel(Request \$request)
    {
        \$error = \$request->session()->get('error', 'Payment was cancelled');
        return view('payments.{$type}.cancel', compact('error'));
    }

    public function webhook(Request \$request, string \$gateway)
    {
        \$verified = Payment::gateway(\$gateway)->handleWebhook(\$request);
        return response()->json(['success' => \$verified]);
    }
}
PHP;
    }

    protected function getViewsForType(string $type): array
    {
        $baseView = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Payment - {{ucfirst('$type')}}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .btn { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
HTML;

        return [
            'index' => $baseView,
            'checkout' => str_replace('@yield(\'content\')', <<<HTML
<div class="card">
    <h1>Complete Your Payment</h1>
    <form method="POST" action="{{ route('payment.checkout') }}">
        @csrf
        <p>Amount: <strong>\${{ number_format(\$amount ?? 100, 2) }}</strong></p>
        <input type="hidden" name="gateway" value="{{ \$gateway ?? 'stripe' }}">
        <input type="hidden" name="amount" value="{{ \$amount ?? 100 }}">
        <input type="hidden" name="currency" value="{{ \$currency ?? 'USD' }}">
        <button type="submit" class="btn">Pay Now</button>
    </form>
</div>
HTML, $baseView),
            'success' => str_replace('@yield(\'content\')', <<<HTML
<div class="card" style="border-color: #22c55e;">
    <h1 style="color: #22c55e;">✓ Payment Successful!</h1>
    <p>Transaction ID: <strong>{{ \$transaction_id ?? 'N/A' }}</strong></p>
    <a href="/" class="btn">Back to Home</a>
</div>
HTML, $baseView),
            'cancel' => str_replace('@yield(\'content\')', <<<HTML
<div class="card" style="border-color: #ef4444;">
    <h1 style="color: #ef4444;">Payment Cancelled</h1>
    <p>{{ \$error ?? 'Your payment was cancelled.' }}</p>
    <a href="/" class="btn">Try Again</a>
</div>
HTML, $baseView),
        ];
    }

    protected function getRoutesForType(string $type): array
    {
        $controller = ucfirst($type) . 'PaymentController';
        
        return [
            "Route::get('/payment/{$type}', [{$controller}::class, 'index'])->name('payment.{$type}');",
            "Route::post('/payment/checkout', [{$controller}::class, 'checkout'])->name('payment.checkout');",
            "Route::get('/payment/success', [{$controller}::class, 'success'])->name('payment.success');",
            "Route::get('/payment/cancel', [{$controller}::class, 'cancel'])->name('payment.cancel');",
            "Route::post('/webhook/{gateway}', [{$controller}::class, 'webhook'])->name('payment.webhook');",
        ];
    }

    protected function replacePlaceholders(string $content, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }
        return $content;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }
}
