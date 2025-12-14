<?php

namespace Mhmadahmd\Filasaas\Services\Gateways;

use Mhmadahmd\Filasaas\Contracts\PaymentGatewayInterface;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;

class CashGateway implements PaymentGatewayInterface
{
    public function processPayment(SubscriptionPayment $payment, array $options = []): mixed
    {
        $plan = $payment->subscription->plan;

        // Check if plan has auto-approve enabled
        if ($plan && $plan->cash_auto_approve) {
            $payment->markAsPaid();
        } else {
            $payment->requires_approval = true;
            $payment->status = SubscriptionPayment::STATUS_PENDING;
            $payment->save();
        }

        return $payment;
    }

    public function refundPayment(SubscriptionPayment $payment, ?float $amount = null): bool
    {
        // Cash payments cannot be refunded through the system
        // Manual refunds should be handled by admins
        return false;
    }

    public function getPaymentStatus(SubscriptionPayment $payment): string
    {
        return $payment->status;
    }

    public function supportsRecurring(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'Cash';
    }

    public function getIdentifier(): string
    {
        return 'cash';
    }
}
