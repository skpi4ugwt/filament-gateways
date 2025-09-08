<?php
// src/Providers/PaymentServiceProvider.php
namespace Labify\Gateways\Providers;

use Illuminate\Support\ServiceProvider;
use Labify\Gateways\Payments\GatewayManager;
use Labify\Gateways\Payments\PaymentSettingsRepository;
use Labify\Gateways\Console\InstallCommand;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2) . '/config/payments.php', 'payments');

        $this->app->singleton(PaymentSettingsRepository::class);
        $this->app->singleton(GatewayManager::class, fn($app) =>
            new GatewayManager($app->make(PaymentSettingsRepository::class))
        );
    }

    public function boot(): void
    {
        // base dirs
        $pkgRoot    = dirname(__DIR__, 2);              // .../labify/filament-gateways
        $configDir  = $pkgRoot . '/config';
        $migrations = $pkgRoot . '/database/migrations';
        $filamentV3 = dirname(__DIR__) . '/Support/Filament/Resources/v3';
        $filamentV4 = dirname(__DIR__) . '/Support/Filament/Resources/v4';

        // routes & migrations
        $this->loadRoutesFrom($pkgRoot . '/routes/api.php');
        $this->loadMigrationsFrom($migrations);

        // publishables
        $this->publishes([
            $configDir . '/payments.php' => config_path('payments.php'),
        ], 'labify-payments-config');

        $this->publishes([
            $filamentV3 => app_path('Filament/Resources'),
        ], 'labify-payments-filament3');

        $this->publishes([
            $filamentV4 => app_path('Filament/Resources'),
        ], 'labify-payments-filament4');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Labify\Gateways\Console\ScaffoldFilamentResources::class,
            ]);
        }
    }
}
