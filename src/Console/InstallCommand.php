<?php
namespace Labify\Gateways\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'labify:payments:install {--filament=4 : Filament major version (3 or 4)}';
    protected $description = 'Publish config, run migrations, publish (or scaffold) Filament resources.';

    public function handle(): int
    {
        $ver = (string) $this->option('filament');

        $this->call('vendor:publish', ['--tag' => 'labify-payments-config']);
        $this->call('migrate');

        // If you are using vendor:publish tags:
        if ($ver === '3') {
            $this->call('vendor:publish', ['--tag' => 'labify-payments-filament3']);
        } else {
            $this->call('vendor:publish', ['--tag' => 'labify-payments-filament4']);
        }

        // If you added the scaffold command (optional), you can call it instead (or as well):
        // $this->call('labify:payments:scaffold', ['--filament' => $ver]);

        $this->components->info('Labify payments installed.');
        return self::SUCCESS;
    }
}