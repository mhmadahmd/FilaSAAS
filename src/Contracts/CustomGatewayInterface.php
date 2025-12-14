<?php

namespace Mhmadahmd\Filasaas\Contracts;

interface CustomGatewayInterface extends PaymentGatewayInterface
{
    /**
     * Configure the gateway with custom settings.
     */
    public function configure(array $config): void;

    /**
     * Validate the gateway configuration.
     */
    public function validateConfiguration(): bool;

    /**
     * Get the configuration fields required for this gateway.
     */
    public function getConfigurationFields(): array;
}
