<?php

namespace ShamimStack\WwwPay\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use ShamimStack\WwwPay\Console\Installers\ConsoleStyle;
use ShamimStack\WwwPay\Console\Installers\GatewaySelector;

class WebhookSetupCommand extends Command
{
    protected $signature = 'payment:webhook:setup 
                            {--gateway= : Setup webhook for specific gateway}
                            {--all : Setup webhooks for all configured gateways}';

    protected $description = 'Setup webhook routes for payment gateways';

    protected ConsoleStyle $io;
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->io = new ConsoleStyle($this->input, $this->output);

        $gateway = $this->option('gateway');
        $all = $this->option('all');

        $this->io->header('WEBHOOK SETUP');

        if ($gateway) {
            return $this->setupGateway($gateway);
        }

        if ($all) {
            return $this->setupAll();
        }

        return $this->interactiveSetup();
    }

    protected function setupGateway(string $gateway): int
    {
        $gateway = strtolower($gateway);

        if (!GatewaySelector::getGatewayInfo($gateway)) {
            $this->io->errorBox('Unknown gateway', $gateway);
            return Command::FAILURE;
        }

        $info = GatewaySelector::getGatewayInfo($gateway);
        $this->io->section("Setting up webhook for {$info['name']}");

        $route = $this->generateWebhookRoute($gateway);
        $this->showWebhookRoute($gateway, $route);

        if ($this->io->confirm('Add webhook route to routes/web.php?', true)) {
            $this->addRouteToWeb($gateway, $route);
        }

        $this->showWebhookInstructions($gateway);

        return Command::SUCCESS;
    }

    protected function setupAll(): int
    {
        $this->io->writeln('Setting up webhooks for all configured gateways...');
        $this->io->newLine();

        $configuredGateways = $this->getConfiguredGateways();

        if (empty($configuredGateways)) {
            $this->io->warningBox('No gateways configured', 'Run payment:install first');
            return Command::FAILURE;
        }

        $routes = [];
        foreach ($configuredGateways as $gateway) {
            $info = GatewaySelector::getGatewayInfo($gateway);
            $route = $this->generateWebhookRoute($gateway);
            $routes[] = [$gateway, $info['name'] ?? $gateway, $route];
        }

        $this->io->table(['Key', 'Gateway', 'Webhook URL'], $routes);

        if ($this->io->confirm('Add all webhook routes to routes/web.php?', true)) {
            foreach ($configuredGateways as $gateway) {
                $route = $this->generateWebhookRoute($gateway);
                $this->addRouteToWeb($gateway, $route);
            }
            $this->io->successBox('All webhooks added');
        }

        $this->showGlobalWebhookInstructions();

        return Command::SUCCESS;
    }

    protected function interactiveSetup(): int
    {
        $configuredGateways = $this->getConfiguredGateways();

        if (empty($configuredGateways)) {
            $this->io->warningBox('No gateways configured', 'Run payment:install first');
            return Command::FAILURE;
        }

        $this->io->section('Select Gateway');
        
        $choices = [];
        foreach ($configuredGateways as $gateway) {
            $info = GatewaySelector::getGatewayInfo($gateway);
            $choices[] = "{$gateway} ({$info['name']})";
        }
        $choices[] = 'All gateways';

        $selected = $this->io->choice('Select gateway', $choices, 0);
        
        if (str_contains($selected, 'All gateways')) {
            return $this->setupAll();
        }

        $gatewayKey = explode(' ', $selected)[0];
        return $this->setupGateway($gatewayKey);
    }

    protected function getConfiguredGateways(): array
    {
        $configured = [];
        $allGateways = GatewaySelector::getAllGatewayNames();

        foreach ($allGateways as $gateway) {
            $config = config("payment.gateways.{$gateway}");
            if (!empty($config)) {
                $hasRealValue = false;
                foreach ($config as $value) {
                    if (!empty($value) && !in_array($value, ['sandbox', 'test', 'dev'])) {
                        $hasRealValue = true;
                        break;
                    }
                }
                if ($hasRealValue) {
                    $configured[] = $gateway;
                }
            }
        }

        return $configured;
    }

    protected function generateWebhookRoute(string $gateway): string
    {
        return "/webhook/{$gateway}";
    }

    protected function showWebhookRoute(string $gateway, string $route): void
    {
        $this->io->newLine();
        $this->io->writeln("Webhook URL:");
        $this->io->writeln("  <fg=cyan>" . url($route) . "</>");
        $this->io->newLine();
    }

    protected function addRouteToWeb(string $gateway, string $route): void
    {
        $routeFile = base_path('routes/web.php');
        
        if (!$this->files->exists($routeFile)) {
            $this->io->errorBox('routes/web.php not found');
            return;
        }

        $controller = '\\ShamimStack\\WwwPay\\Http\\Controllers\\WebhookController';
        $routeCode = "\n// wwwpay webhook - {$gateway}\nRoute::post('{$route}', [{$controller}::class, 'handle'])->name('payment.webhook.{$gateway}');\n";

        $content = $this->files->get($routeFile);
        
        if (str_contains($content, "payment.webhook.{$gateway}")) {
            $this->io->writeln("  <fg=yellow>Route already exists for {$gateway}</>");
            return;
        }

        $content = rtrim($content) . $routeCode;
        $this->files->put($routeFile, $content);

        $this->io->successBox('Route added', "Added to routes/web.php");
    }

    protected function showWebhookInstructions(string $gateway): void
    {
        $this->io->newLine();
        $this->io->section('Next Steps');

        $instructions = match ($gateway) {
            'stripe' => [
                'Go to Stripe Dashboard > Developers > Webhooks',
                'Add endpoint: ' . url("/webhook/{$gateway}"),
                'Select events: payment_intent.succeeded, payment_intent.payment_failed',
                'Copy the webhook signing secret to STRIPE_WEBHOOK_SECRET',
            ],
            'paypal' => [
                'Go to PayPal Developer Dashboard > My Apps & Credentials',
                'Create or select your app',
                'Add webhook URL: ' . url("/webhook/{$gateway}"),
                'Subscribe to events: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED',
            ],
            'paystack' => [
                'Go to Paystack Dashboard > Settings > Webhooks',
                'Add webhook URL: ' . url("/webhook/{$gateway}"),
            ],
            'flutterwave' => [
                'Go to Flutterwave Dashboard > Settings > Webhooks',
                'Add webhook URL: ' . url("/webhook/{$gateway}"),
                'Select all payment events',
            ],
            'braintree' => [
                'Go to Braintree Control Panel > Settings > Webhooks',
                'Create webhook endpoint: ' . url("/webhook/{$gateway}"),
                'Select payment notifications',
            ],
            default => [
                'Configure the webhook URL in your gateway dashboard:',
                url("/webhook/{$gateway}"),
            ],
        };

        foreach ($instructions as $instruction) {
            $this->io->writeln("  <fg=gray>▸</> {$instruction}");
        }

        $this->io->newLine();
    }

    protected function showGlobalWebhookInstructions(): void
    {
        $this->io->newLine();
        $this->io->section('Important Notes');

        $this->io->listing([
            'Register each webhook URL in the respective gateway dashboard',
            'Ensure your application URL is set correctly in .env (APP_URL)',
            'Use HTTPS in production for webhook security',
            'Test webhooks using gateway-provided testing tools',
        ]);

        $this->io->newLine();
    }
}
