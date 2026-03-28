<?php

namespace ShamimStack\WwwPay\Console\Commands;

use Illuminate\Console\Command;
use ShamimStack\WwwPay\Console\Installers\ConsoleStyle;
use ShamimStack\WwwPay\Console\Installers\GatewaySelector;
use ShamimStack\WwwPay\PaymentManager;
use Exception;

class GatewayTestCommand extends Command
{
    protected $signature = 'payment:gateway:test 
                            {gateway : The gateway name to test}
                            {--amount=100 : Test amount}
                            {--currency=USD : Test currency}';

    protected $description = 'Test a payment gateway connection';

    protected ConsoleStyle $io;

    public function handle(PaymentManager $manager): int
    {
        $this->io = new ConsoleStyle($this->input, $this->output);

        $gatewayName = strtolower($this->argument('gateway'));
        $amount = (float) $this->option('amount');
        $currency = strtoupper($this->option('currency'));

        $this->io->header("TESTING GATEWAY: " . strtoupper($gatewayName));

        $info = GatewaySelector::getGatewayInfo($gatewayName);
        if (!$info) {
            $this->io->errorBox('Gateway not found', $gatewayName);
            $this->showAvailableGateways();
            return Command::FAILURE;
        }

        $this->io->writeln([
            "Gateway: <fg=cyan>{$info['name']}</>",
            "Region: <fg=cyan>{$info['region']}</>",
            "Amount: <fg=cyan>{$amount} {$currency}</>",
        ]);
        $this->io->newLine();

        if (!$this->isConfigured($gatewayName)) {
            $this->io->warningBox('Gateway not configured', "Run 'php artisan payment:install' or add credentials to .env");
            $this->showEnvVars($gatewayName);
            return Command::FAILURE;
        }

        return $this->runTest($manager, $gatewayName, $amount, $currency);
    }

    protected function isConfigured(string $gateway): bool
    {
        $config = config("payment.gateways.{$gateway}");
        
        if (empty($config)) {
            return false;
        }

        $hasRealValue = false;
        foreach ($config as $value) {
            if (!empty($value) && !in_array($value, ['sandbox', 'test', 'dev'])) {
                $hasRealValue = true;
                break;
            }
        }

        return $hasRealValue;
    }

    protected function runTest(PaymentManager $manager, string $gateway, float $amount, string $currency): int
    {
        $this->io->writeln('Running test transaction...');
        $this->io->newLine();

        try {
            $testData = $this->getTestData($gateway, $amount, $currency);
            
            $response = $manager->gateway($gateway)->pay($testData);

            if ($response->isSuccessful()) {
                $this->io->successBox(
                    'Test Successful',
                    "Transaction ID: {$response->getTransactionId()}"
                );
                return Command::SUCCESS;
            } else {
                $this->io->errorBox(
                    'Test Failed',
                    $response->getErrorMessage() ?? 'Unknown error'
                );
                return Command::FAILURE;
            }
        } catch (Exception $e) {
            $this->io->errorBox(
                'Connection Error',
                $e->getMessage()
            );
            
            $this->io->newLine();
            $this->io->writeln('Troubleshooting:');
            $this->io->listing([
                'Check your API credentials in .env',
                'Ensure you are using sandbox/test credentials',
                'Verify your internet connection',
                'Check gateway-specific requirements',
            ]);

            return Command::FAILURE;
        }
    }

    protected function getTestData(string $gateway, float $amount, string $currency): array
    {
        $baseData = [
            'amount' => $amount,
            'currency' => $currency,
            'return_url' => url('/payment/test/return'),
            'cancel_url' => url('/payment/test/cancel'),
        ];

        return match ($gateway) {
            'stripe' => array_merge($baseData, [
                'payment_method' => 'pm_card_visa',
            ]),
            'paystack' => array_merge($baseData, [
                'email' => 'test@example.com',
            ]),
            'bkash' => array_merge($baseData, [
                'email' => 'test@example.com',
            ]),
            'flutterwave' => array_merge($baseData, [
                'email' => 'test@example.com',
                'name' => 'Test User',
            ]),
            'mercadopago' => array_merge($baseData, [
                'email' => 'test@example.com',
            ]),
            'paytm' => array_merge($baseData, [
                'email' => 'test@example.com',
                'phone' => '9999999999',
            ]),
            'phonepe' => array_merge($baseData, [
                'email' => 'test@example.com',
                'phone' => '9999999999',
            ]),
            'jazzcash' => array_merge($baseData, [
                'email' => 'test@example.com',
                'phone' => '03001234567',
            ]),
            'paypal' => array_merge($baseData, [
                'email' => 'test@example.com',
            ]),
            'bitcoin', 'ethereum' => array_merge($baseData, [
                'wallet_address' => 'test_wallet',
            ]),
            default => $baseData,
        };
    }

    protected function showEnvVars(string $gateway): void
    {
        $this->io->newLine();
        $this->io->section('Required Environment Variables');
        
        $config = GatewaySelector::GATEWAY_CONFIG_TEMPLATES[$gateway] ?? [];
        foreach ($config as $key => $env) {
            $this->io->envKey($env);
        }
        
        $this->io->newLine();
    }

    protected function showAvailableGateways(): void
    {
        $this->io->newLine();
        $this->io->writeln('Available gateways:');
        
        foreach (GatewaySelector::REGIONS as $region) {
            $gateways = implode(', ', $region['gateways']);
            $this->io->writeln("  <fg=gray>{$region['name']}:</> {$gateways}");
        }
        
        $this->io->newLine();
        $this->io->writeln("Run <fg=cyan>php artisan payment:gateway:list</> for details.");
    }
}
