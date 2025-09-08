<?php

namespace Labify\Gateways\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ScaffoldFilamentResources extends Command
{
    protected $signature = 'labify:payments:scaffold {--filament=4 : Filament major version (3 or 4)}';
    protected $description = 'Scaffold PaymentGatewaySetting Resource, Pages, and Schema into the app.';

    public function handle(Filesystem $fs): int
    {
        $ver = (string) $this->option('filament');
        $pkg = dirname(__DIR__, 2); // package root

        // Source paths inside the package (put the *filled* versions here)
        $srcResource = $pkg.'/resources/scaffolds/filament/v'.$ver.'/PaymentGatewaySettingResource.php';
        $srcPages    = $pkg.'/resources/scaffolds/filament/v'.$ver.'/PaymentGatewaySettingResource/Pages';
        $srcSchema   = $pkg.'/resources/scaffolds/filament/v'.$ver.'/PaymentGatewaySettings/Schemas';

        // Dest paths in the app
        $appResource = app_path('Filament/Resources/PaymentGatewaySettingResource.php');
        $appPagesDir = app_path('Filament/Resources/PaymentGatewaySettingResource/Pages');
        $appSchemaDir= app_path('Filament/Resources/PaymentGatewaySettings/Schemas');

        // Ensure dirs
        $fs->ensureDirectoryExists(dirname($appResource));
        $fs->ensureDirectoryExists($appPagesDir);
        $fs->ensureDirectoryExists($appSchemaDir);

        // Copy resource
        $fs->copy($srcResource, $appResource);

        // Copy pages
        foreach ($fs->files($srcPages) as $file) {
            $fs->copy($file->getPathname(), $appPagesDir.'/'.basename($file));
        }

        // Copy schema helper(s)
        foreach ($fs->files($srcSchema) as $file) {
            $fs->copy($file->getPathname(), $appSchemaDir.'/'.basename($file));
        }

        $this->components->info('Filament resources scaffolded.');
        $this->components->info('Run: php artisan optimize:clear && php artisan filament:assets');

        return self::SUCCESS;
    }
}
