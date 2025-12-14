<?php

namespace Mhmadahmd\Filasaas\Services\Gateways;

use Mhmadahmd\Filasaas\Contracts\CustomGatewayInterface;

abstract class CustomLocalGateway implements CustomGatewayInterface
{
    protected array $config = [];

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    public function validateConfiguration(): bool
    {
        // Override this method in your custom gateway implementation
        return ! empty($this->config);
    }

    public function getConfigurationFields(): array
    {
        // Override this method to return the required configuration fields
        // Example:
        // return [
        //     'api_key' => ['type' => 'text', 'label' => 'API Key', 'required' => true],
        //     'merchant_id' => ['type' => 'text', 'label' => 'Merchant ID', 'required' => true],
        // ];
        return [];
    }

    public function supportsRecurring(): bool
    {
        // Override if your gateway supports recurring payments
        return false;
    }

    /**
     * Get a configuration value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
