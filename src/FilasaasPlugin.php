<?php

namespace Mhmadahmd\Filasaas;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mhmadahmd\Filasaas\Filament\Resources\FeatureResource;
use Mhmadahmd\Filasaas\Filament\Resources\PlanResource;
use Mhmadahmd\Filasaas\Filament\Resources\SubscriptionPaymentResource;
use Mhmadahmd\Filasaas\Filament\Resources\SubscriptionResource;

class FilasaasPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filasaas';
    }

    public function register(Panel $panel): void
    {
        // register resources
        $panel->resources([
            PlanResource::class,
            SubscriptionResource::class,
            SubscriptionPaymentResource::class,
            FeatureResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
