<?php

namespace ShamimStack\WwwPay\Console\Commands;

use Illuminate\Console\Command;
use ShamimStack\WwwPay\Console\Installers\ConsoleStyle;
use ShamimStack\WwwPay\Console\Installers\GatewaySelector;

class GatewayListCommand extends Command
{
    protected $signature = 'payment:gateway:list 
                            {--region= : Filter by region}
                            {--json : Output as JSON}';

    protected $description = 'List all available payment gateways';

    protected ConsoleStyle $io;

    public function handle(): int
    {
        $this->io = new ConsoleStyle($this->input, $this->output);

        if ($this->option('json')) {
            return $this->outputJson();
        }

        $this->showList();

        return Command::SUCCESS;
    }

    protected function showList(): void
    {
        $this->io->header('AVAILABLE PAYMENT GATEWAYS');

        $region = $this->option('region');

        if ($region) {
            $this->showRegion($region);
        } else {
            $this->showAllRegions();
        }
    }

    protected function showAllRegions(): void
    {
        foreach (GatewaySelector::REGIONS as $key => $region) {
            $this->io->section($region['name'] . ' - ' . $region['description']);
            
            $rows = [];
            foreach ($region['gateways'] as $gateway) {
                $info = GatewaySelector::getGatewayInfo($gateway);
                $rows[] = [
                    $gateway,
                    $info['name'] ?? ucfirst($gateway),
                    $this->getGatewayStatus($gateway),
                ];
            }

            $this->io->table(['Key', 'Gateway', 'Status'], $rows);
        }

        $this->io->newLine();
        $this->showSummary();
    }

    protected function showRegion(string $regionKey): void
    {
        if (!isset(GatewaySelector::REGIONS[$regionKey])) {
            $this->io->errorBox('Region not found', "Available: " . implode(', ', array_keys(GatewaySelector::REGIONS)));
            return;
        }

        $region = GatewaySelector::REGIONS[$regionKey];
        $this->io->header(strtoupper($region['name']) . ' GATEWAYS');

        $this->io->writeln("Description: {$region['description']}");
        $this->io->newLine();

        $rows = [];
        foreach ($region['gateways'] as $gateway) {
            $info = GatewaySelector::getGatewayInfo($gateway);
            $rows[] = [
                $gateway,
                $info['name'] ?? ucfirst($gateway),
                $this->getGatewayStatus($gateway),
            ];
        }

        $this->io->table(['Key', 'Gateway', 'Status'], $rows);

        $this->showEnvVars($region['gateways']);
    }

    protected function showEnvVars(array $gateways): void
    {
        $this->io->section('Required Environment Variables');
        $this->io->newLine();

        foreach ($gateways as $gateway) {
            $config = GatewaySelector::GATEWAY_CONFIG_TEMPLATES[$gateway] ?? [];
            if (!empty($config)) {
                $this->io->gatewayHeader(
                    GatewaySelector::getGatewayInfo($gateway)['name'] ?? ucfirst($gateway),
                    $gateway
                );
                foreach ($config as $key => $env) {
                    $this->io->envKey($env);
                }
                $this->io->newLine();
            }
        }
    }

    protected function getGatewayStatus(string $gateway): string
    {
        $config = config("payment.gateways.{$gateway}");
        
        if (empty($config)) {
            return '<fg=yellow>Not configured</>';
        }

        $hasCredentials = false;
        foreach ($config as $value) {
            if (!empty($value) && $value !== 'sandbox' && $value !== 'test' && $value !== 'dev') {
                $hasCredentials = true;
                break;
            }
        }

        return $hasCredentials 
            ? '<fg=green>Configured</>' 
            : '<fg=yellow>Sandbox mode</>';
    }

    protected function showSummary(): void
    {
        $total = count(GatewaySelector::getAllGatewayNames());
        $configured = 0;
        
        foreach (GatewaySelector::getAllGatewayNames() as $gateway) {
            if (!empty(config("payment.gateways.{$gateway}"))) {
                $configured++;
            }
        }

        $this->io->section('Summary');
        $this->io->writeln("Total Gateways: <fg=cyan>{$total}</>");
        $this->io->writeln("Configured: <fg=green>{$configured}</>");
        $this->io->writeln("Available: <fg=yellow>" . ($total - $configured) . "</>");
        $this->io->newLine();
    }

    protected function outputJson(): int
    {
        $gateways = [];

        foreach (GatewaySelector::ALL_GATEWAYS as $key => $info) {
            $gateways[$key] = [
                'name' => $info['name'],
                'region' => $info['region'],
                'configured' => !empty(config("payment.gateways.{$key}")),
                'env_prefix' => $info['env_prefix'],
            ];
        }

        $output = [
            'total_gateways' => count($gateways),
            'gateways' => $gateways,
            'regions' => array_keys(GatewaySelector::REGIONS),
        ];

        $this->output->write(json_encode($output, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
