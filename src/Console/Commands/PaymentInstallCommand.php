<?php

namespace ShamimStack\WwwPay\Console\Commands;

use Illuminate\Console\Command;
use ShamimStack\WwwPay\Console\Installers\ConsoleStyle;
use ShamimStack\WwwPay\Console\Installers\GatewaySelector;

class PaymentInstallCommand extends Command
{
    protected $signature = 'payment:install 
                            {--gateway= : Specific gateway to configure}
                            {--region= : Configure all gateways in a region}
                            {--skip-migrations : Skip publishing migrations}
                            {--skip-env : Skip .env configuration}
                            {--force : Force overwrite existing config}';

    protected $description = 'Install and configure wwwpay payment gateways interactively';

    protected ConsoleStyle $io;

    public function handle(): int
    {
        $this->io = new ConsoleStyle($this->input, $this->output);

        $this->io->header('WWWPAY INSTALLER');

        $this->showWelcome();

        $step = 1;
        $totalSteps = 3;

        $this->io->stepComplete($step++, $totalSteps, 'Publishing configuration...');
        $this->publishConfig();

        $this->io->stepComplete($step++, $totalSteps, 'Configuring gateways...');
        $selectedGateways = $this->selectGateways();

        if (!$this->option('skip-env')) {
            $this->io->stepComplete($step, $totalSteps, 'Updating .env file...');
            $this->configureEnv($selectedGateways);
        }

        if (!$this->option('skip-migrations')) {
            $this->publishMigrations();
        }

        $this->showSummary($selectedGateways);

        return Command::SUCCESS;
    }

    protected function showWelcome(): void
    {
        $this->io->section('Welcome to wwwpay Installer');

        $this->io->writeln('This wizard will help you:');
        $this->io->listing([
            'Select payment gateways to configure',
            'Publish configuration files',
            'Set up environment variables',
            'Publish database migrations',
        ]);

        $this->io->newLine();
    }

    protected function publishConfig(): void
    {
        $configPath = config_path('payment.php');
        
        if (file_exists($configPath) && !$this->option('force')) {
            if (!$this->io->confirm('Config file exists. Overwrite?', false)) {
                $this->io->warning('Keeping existing config file.');
                return;
            }
        }

        $this->call('vendor:publish', [
            '--provider' => 'ShamimStack\WwwPay\Providers\PaymentServiceProvider',
            '--tag' => 'payment-config',
            '--force' => true,
        ]);

        $this->io->successBox('Configuration published', "Created: {$configPath}");
    }

    protected function selectGateways(): array
    {
        $this->io->section('Select Payment Gateways');

        $this->io->writeln('Choose how to select gateways:');
        $this->io->listing([
            '<fg=cyan>[1]</> Select by region (recommended)',
            '<fg=cyan>[2]</> Select individual gateways',
            '<fg=cyan>[3]</> Select all gateways',
        ]);

        $selectionType = $this->io->choice('Select option', [1, 2, 3], 1);

        $selectedGateways = [];

        switch ($selectionType) {
            case 1:
                $selectedGateways = $this->selectByRegion();
                break;
            case 2:
                $selectedGateways = $this->selectIndividually();
                break;
            case 3:
                $selectedGateways = GatewaySelector::getAllGatewayNames();
                break;
        }

        return $selectedGateways;
    }

    protected function selectByRegion(): array
    {
        $this->io->writeln('Select regions to enable (comma-separated, or "all"):');
        $this->io->newLine();

        $regionOptions = [];
        $regionKeys = array_keys(GatewaySelector::REGIONS);

        foreach (GatewaySelector::REGIONS as $key => $region) {
            $gateways = implode(', ', $region['gateways']);
            $this->io->writeln("  <fg=cyan>[{$key}]</> {$region['name']}");
            $this->io->writeln("      <fg=gray>{$region['description']}</>");
            $this->io->newLine();
        }

        $selection = $this->io->ask('Select regions (e.g., 1,2,3 or all)', 'all');

        if (strtolower($selection) === 'all') {
            $selectedGateways = GatewaySelector::getAllGatewayNames();
        } else {
            $selectedGateways = [];
            $selections = array_map('trim', explode(',', strtolower($selection)));
            
            foreach ($selections as $sel) {
                if (isset(GatewaySelector::REGIONS[$sel])) {
                    $selectedGateways = array_merge(
                        $selectedGateways,
                        GatewaySelector::REGIONS[$sel]['gateways']
                    );
                }
            }
        }

        return array_unique($selectedGateways);
    }

    protected function selectIndividually(): array
    {
        $allGateways = GatewaySelector::ALL_GATEWAYS;
        $gatewayKeys = array_keys($allGateways);

        $this->io->table(
            ['Key', 'Gateway', 'Region'],
            array_map(function ($key) use ($allGateways) {
                return [$key, $allGateways[$key]['name'], $allGateways[$key]['region']];
            }, $gatewayKeys)
        );

        $selection = $this->io->ask('Enter gateway names (comma-separated)', 'stripe,paypal');
        $selectedGateways = array_map('trim', explode(',', strtolower($selection)));

        return array_filter($selectedGateways, function ($g) use ($gatewayKeys) {
            return in_array($g, $gatewayKeys);
        });
    }

    protected function configureEnv(array $gateways): void
    {
        $this->io->section('Environment Configuration');

        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->io->errorBox('Environment file not found', $envFile);
            return;
        }

        $this->io->writeln('Enter credentials for selected gateways (press Enter to skip):');
        $this->io->newLine();

        $envLines = [];

        foreach ($gateways as $gateway) {
            $this->io->gatewayHeader(
                GatewaySelector::ALL_GATEWAYS[$gateway]['name'] ?? ucfirst($gateway),
                GatewaySelector::getRegionForGateway($gateway)
            );

            $config = GatewaySelector::GATEWAY_CONFIG_TEMPLATES[$gateway] ?? [];
            
            foreach ($config as $key => $envVar) {
                $value = $this->io->ask("  {$envVar}", '');
                if (!empty($value)) {
                    $envLines[] = "{$envVar}={$value}";
                }
            }

            $this->io->newLine();
        }

        if (!empty($envLines)) {
            $envContent = "\n# Payment Gateway Credentials (wwwpay)\n" . implode("\n", $envLines) . "\n";
            file_put_contents($envFile, $envContent, FILE_APPEND);
            $this->io->successBox('Environment file updated');
        }
    }

    protected function publishMigrations(): void
    {
        $this->io->section('Database Migrations');

        if (!$this->io->confirm('Publish payment migrations?', true)) {
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => 'ShamimStack\WwwPay\Providers\PaymentServiceProvider',
            '--tag' => 'payment-migrations',
            '--force' => true,
        ]);

        $this->io->successBox('Migrations published');
        $this->io->writeln('  Run <fg=cyan>php artisan migrate</> to create tables.');
        $this->io->newLine();
    }

    protected function showSummary(array $gateways): void
    {
        $this->io->section('Installation Complete');

        $this->io->successBox('wwwpay installed successfully!');

        $this->io->writeln('Next steps:');
        $this->io->listing([
            '<fg=cyan>1.</> Configure your gateway credentials in <fg=green>.env</>',
            '<fg=cyan>2.</> Run <fg=cyan>php artisan migrate</> to create database tables',
            '<fg=cyan>3.</> Use the Payment facade: <fg=green>Payment::gateway(\'stripe\')</>',
            '<fg=cyan>4.</> View all commands: <fg=cyan>php artisan list payment</>',
        ]);

        $this->io->newLine();
        $this->io->writeln("Selected <fg=cyan>" . count($gateways) . "</> gateways:");
        
        $gatewayGroups = [];
        foreach ($gateways as $gateway) {
            $region = GatewaySelector::getRegionForGateway($gateway);
            if (!isset($gatewayGroups[$region])) {
                $gatewayGroups[$region] = [];
            }
            $gatewayGroups[$region][] = $gateway;
        }

        foreach ($gatewayGroups as $region => $gwList) {
            $this->io->writeln("  <fg=gray>{$region}:</> " . implode(', ', $gwList));
        }

        $this->io->newLine();
    }
}
