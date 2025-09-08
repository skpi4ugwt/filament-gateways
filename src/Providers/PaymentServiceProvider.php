<?php
// src/Providers/PaymentServiceProvider.php
namespace Labify\Gateways\Providers;

use Illuminate\Support\ServiceProvider;
use Labify\Gateways\Payments\{GatewayManager, PaymentSettingsRepository};
use Labify\Gateways\Console\InstallCommand;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__."/../../config/payments.php", "payments");
        $this->app->singleton(PaymentSettingsRepository::class);
        $this->app->singleton(GatewayManager::class, fn($app) =>
            new GatewayManager($app->make(PaymentSettingsRepository::class))
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__."/../../routes/api.php");
        $this->loadMigrationsFrom(__DIR__."/../../database/migrations");

        $this->publishes([__DIR__."/../../config/payments.php" => config_path("payments.php")], "labify-payments-config");

        // Publish Filament resources (you’ll choose v3 or v4 via installer)
        $this->publishes([__DIR__."/../../src/Support/Filament/Resources/v3" => app_path("Filament/Resources")], "labify-payments-filament3");
        $this->publishes([__DIR__."/../../src/Support/Filament/Resources/v4" => app_path("Filament/Resources")], "labify-payments-filament4");

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }
}
