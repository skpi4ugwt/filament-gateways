<?php
// src/Console/InstallCommand.php
namespace Labify\Gateways\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'labify:payments:install {--filament=4 : Filament major version (3 or 4)}';
    protected $description = 'Install Labify Gateways: publish config, migrations, and Filament resource.';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'labify-payments-config']);
        $this->call('migrate');

        $ver = (string) $this->option('filament');
        $tag = $ver === '3' ? 'labify-payments-filament3' : 'labify-payments-filament4';
        $this->call('vendor:publish', ['--tag' => $tag]);

        $this->info("Published Filament v{$ver} resource stubs.");
        return self::SUCCESS;
    }
}
