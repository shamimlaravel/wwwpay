<?php

namespace ShamimStack\WwwPay\Console\Installers;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleStyle extends SymfonyStyle
{
    protected $input;
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
        $this->input = $input;
        $this->output = $output;
    }

    public function header(string $message): void
    {
        $this->newLine();
        $this->comment('╔══════════════════════════════════════════════════════════════════╗');
        $this->comment('║  ' . str_pad($message, 64) . '║');
        $this->comment('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    public function section(string $title): void
    {
        $this->newLine();
        $this->info("▸ {$title}");
        $this->newLine();
    }

    public function gatewayHeader(string $gateway, string $region): void
    {
        $this->newLine();
        $this->writeln("  <fg=cyan>{$gateway}</> <fg=gray>({$region})</>");
    }

    public function successBox(string $title, ?string $message = null): void
    {
        $this->newLine();
        $this->block("✓ {$title}", 'OK', 'fg=white;bg=green', ' ', true);
        if ($message) {
            $this->writeln("  {$message}");
        }
    }

    public function errorBox(string $title, ?string $message = null): void
    {
        $this->newLine();
        $this->block("✗ {$title}", 'ERROR', 'fg=white;bg=red', ' ', true);
        if ($message) {
            $this->writeln("  {$message}");
        }
    }

    public function warningBox(string $title, ?string $message = null): void
    {
        $this->newLine();
        $this->block("⚠ {$title}", 'WARNING', 'fg=black;bg=yellow', ' ', true);
        if ($message) {
            $this->writeln("  {$message}");
        }
    }

    public function infoBox(string $title, ?string $message = null): void
    {
        $this->newLine();
        $this->block("ℹ {$title}", 'INFO', 'fg=black;bg=cyan', ' ', true);
        if ($message) {
            $this->writeln("  {$message}");
        }
    }

    public function progressStart(int $total): void
    {
        $this->newLine();
        $this->progressBar = $this->createProgressBar($total);
        $this->progressBar->setFormat(' %current%/%max% %bar% %percent:3s%% %elapsed:6s%/%estimated:-6s%');
    }

    public function tableFormatted(array $headers, array $rows): void
    {
        $this->table($headers, $rows);
    }

    public function envKey(string $key, ?string $value = null): void
    {
        $display = $value ? "{$key}={$value}" : "# {$key}=YOUR_VALUE_HERE";
        $this->text("  <fg=green>{$key}</>");
    }

    public function confirmGateway(string $gateway): bool
    {
        return $this->confirm("Do you want to configure <fg=cyan>{$gateway}</>?", true);
    }

    public function askForEnvValue(string $key, ?string $default = null): ?string
    {
        return $this->ask("Enter <fg=green>{$key}</>", $default);
    }

    public function stepComplete(int $step, int $total, string $message): void
    {
        $this->writeln("  [<fg=green>{$step}/{$total}</>] {$message}");
    }
}
