<?php

namespace Mhmadahmd\Filasaas;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Mhmadahmd\Filasaas\Commands\FilasaasCommand;
use Mhmadahmd\Filasaas\Testing\TestsFilasaas;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilasaasServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filasaas';

    public static string $viewNamespace = 'filasaas';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('mhmadahmd/filasaas');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // Register service bindings
        $this->app->singleton(
            \Mhmadahmd\Filasaas\Services\PaymentGatewayManager::class,
            function ($app) {
                return new \Mhmadahmd\Filasaas\Services\PaymentGatewayManager($app);
            }
        );

        $this->app->singleton(
            \Mhmadahmd\Filasaas\Services\TenantBillingProvider::class,
            function ($app) {
                return new \Mhmadahmd\Filasaas\Services\TenantBillingProvider(
                    $app->make(\Mhmadahmd\Filasaas\Services\PaymentGatewayManager::class)
                );
            }
        );
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Register Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filasaas/{$file->getFilename()}"),
                ], 'filasaas-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilasaas);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mhmadahmd/filasaas';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filasaas', __DIR__ . '/../resources/dist/components/filasaas.js'),
            Css::make('filasaas-styles', __DIR__ . '/../resources/dist/filasaas.css'),
            Js::make('filasaas-scripts', __DIR__ . '/../resources/dist/filasaas.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilasaasCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filasaas_plans_table',
            'create_filasaas_plan_features_table',
            'create_filasaas_subscriptions_table',
            'create_filasaas_subscription_payments_table',
            'create_filasaas_subscription_usage_table',
        ];
    }
}
