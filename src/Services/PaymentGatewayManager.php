<?php

namespace Mhmadahmd\Filasaas\Services;

use Illuminate\Contracts\Foundation\Application;
use Mhmadahmd\Filasaas\Contracts\PaymentGatewayInterface;
use Mhmadahmd\Filasaas\Models\Plan;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;
use Mhmadahmd\Filasaas\Services\Gateways\CashGateway;
use Mhmadahmd\Filasaas\Services\Gateways\PayPalGateway;
use Mhmadahmd\Filasaas\Services\Gateways\StripeGateway;

class PaymentGatewayManager
{
    protected array $gateways = [];

    public function __construct(Application $app)
    {
        $this->registerDefaultGateways($app);
    }

    protected function registerDefaultGateways(Application $app): void
    {
        // Register Cash Gateway
        if (config('filasaas.gateways.cash.enabled', true)) {
            $this->register('cash', new CashGateway);
        }

        // Register Stripe Gateway
        if (config('filasaas.gateways.stripe.enabled', false)) {
            try {
                $this->register('stripe', new StripeGateway);
            } catch (\Exception $e) {
                // Stripe not configured, skip
            }
        }

        // Register PayPal Gateway
        if (config('filasaas.gateways.paypal.enabled', false)) {
            try {
                $this->register('paypal', new PayPalGateway);
            } catch (\Exception $e) {
                // PayPal not configured, skip
            }
        }
    }

    public function register(string $identifier, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$identifier] = $gateway;
    }

    public function get(string $identifier): ?PaymentGatewayInterface
    {
        return $this->gateways[$identifier] ?? null;
    }

    public function getAll(): array
    {
        return $this->gateways;
    }

    public function getAvailableForPlan(Plan $plan): array
    {
        $allowedGateways = $plan->getAllowedGateways();
        $available = [];

        foreach ($allowedGateways as $gatewayIdentifier) {
            $gateway = $this->get($gatewayIdentifier);
            
            // Skip if gateway is not registered
            if (! $gateway) {
                continue;
            }

            // For cash gateway, always include if it's in allowed gateways
            if ($gatewayIdentifier === 'cash') {
                $available[$gatewayIdentifier] = $gateway;
                continue;
            }

            // For other gateways, check if they're enabled in config
            $configKey = "filasaas.gateways.{$gatewayIdentifier}.enabled";
            if (config($configKey, false)) {
                $available[$gatewayIdentifier] = $gateway;
            }
        }

        return $available;
    }

    public function processPayment(SubscriptionPayment $payment): mixed
    {
        $gateway = $this->get($payment->gateway);

        if (! $gateway) {
            throw new \Exception("Gateway '{$payment->gateway}' not found.");
        }

        return $gateway->processPayment($payment);
    }

    public function isGatewayAvailable(string $gateway, Plan $plan): bool
    {
        // Check if gateway is registered
        if (! $this->get($gateway)) {
            return false;
        }

        // Check if gateway is enabled in config
        $configKey = "filasaas.gateways.{$gateway}.enabled";
        if (! config($configKey, false) && $gateway !== 'cash') {
            return false;
        }

        // Check if plan allows this gateway
        $allowedGateways = $plan->getAllowedGateways();
        if (! in_array($gateway, $allowedGateways)) {
            return false;
        }

        return true;
    }
}
