<?php

namespace Mhmadahmd\Filasaas\Contracts;

use Mhmadahmd\Filasaas\Models\SubscriptionPayment;

interface PaymentGatewayInterface
{
    /**
     * Process a payment through the gateway.
     */
    public function processPayment(SubscriptionPayment $payment, array $options = []): mixed;

    /**
     * Refund a payment through the gateway.
     */
    public function refundPayment(SubscriptionPayment $payment, ?float $amount = null): bool;

    /**
     * Get the current status of a payment from the gateway.
     */
    public function getPaymentStatus(SubscriptionPayment $payment): string;

    /**
     * Check if the gateway supports recurring payments.
     */
    public function supportsRecurring(): bool;

    /**
     * Get the display name of the gateway.
     */
    public function getName(): string;

    /**
     * Get the unique identifier of the gateway.
     */
    public function getIdentifier(): string;
}
