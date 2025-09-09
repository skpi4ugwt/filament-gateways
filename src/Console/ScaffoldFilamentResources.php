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

        // package root
        $pkgRoot = dirname(__DIR__, 2);

        // source scaffold files inside the package
        $srcResource = $pkgRoot . "/resources/scaffolds/filament/v{$ver}/PaymentGatewaySettingResource.php";
        $srcPagesDir = $pkgRoot . "/resources/scaffolds/filament/v{$ver}/PaymentGatewaySettingResource/Pages";
        $srcSchemaDir= $pkgRoot . "/resources/scaffolds/filament/v{$ver}/PaymentGatewaySettings/Schemas";

        // destination inside the app
        $appResource = app_path('Filament/Resources/PaymentGatewaySettingResource.php');
        $appPagesDir = app_path('Filament/Resources/PaymentGatewaySettingResource/Pages');
        $appSchemaDir= app_path('Filament/Resources/PaymentGatewaySettings/Schemas');

        // ensure directories
        $fs->ensureDirectoryExists(dirname($appResource));
        $fs->ensureDirectoryExists($appPagesDir);
        $fs->ensureDirectoryExists($appSchemaDir);

        // copy resource
        if (!$fs->exists($srcResource)) {
            $this->components->error("Scaffold source not found: {$srcResource}");
            return self::FAILURE;
        }
        $fs->copy($srcResource, $appResource);

        // copy pages
        foreach ($fs->files($srcPagesDir) as $file) {
            $fs->copy($file->getPathname(), $appPagesDir . '/' . $file->getFilename());
        }

        // copy schema
        foreach ($fs->files($srcSchemaDir) as $file) {
            $fs->copy($file->getPathname(), $appSchemaDir . '/' . $file->getFilename());
        }

        $this->components->info('Filament resources scaffolded.');
        $this->components->info('Run: php artisan optimize:clear && php artisan filament:assets');
        return self::SUCCESS;
    }
}