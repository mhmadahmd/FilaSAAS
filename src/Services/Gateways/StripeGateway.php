<?php

namespace Mhmadahmd\Filasaas\Services\Gateways;

use Mhmadahmd\Filasaas\Contracts\PaymentGatewayInterface;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;

class StripeGateway implements PaymentGatewayInterface
{
    protected $stripe;

    public function __construct()
    {
        if (class_exists(\Laravel\Cashier\Cashier::class)) {
            $this->stripe = new \Stripe\StripeClient(config('filasaas.gateways.stripe.secret'));
        }
    }

    public function processPayment(SubscriptionPayment $payment, array $options = []): mixed
    {
        if (! $this->stripe) {
            throw new \Exception('Stripe is not configured. Please install laravel/cashier-stripe package.');
        }

        try {
            $subscription = $payment->subscription;
            $plan = $subscription->plan;

            // Check if this is a recurring subscription
            if ($plan->invoice_period > 0 && $subscription->stripe_id) {
                // Handle recurring subscription payment
                // Stripe will handle this via webhooks
                $payment->status = SubscriptionPayment::STATUS_PENDING;
                $payment->save();

                return $payment;
            }

            // Create a one-time payment intent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($payment->amount * 100), // Convert to cents
                'currency' => strtolower($payment->currency),
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                ],
            ]);

            $payment->gateway_transaction_id = $paymentIntent->id;
            $payment->gateway_response = $paymentIntent->toArray();
            $payment->status = SubscriptionPayment::STATUS_PENDING;
            $payment->save();

            return [
                'payment_intent' => $paymentIntent,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (\Exception $e) {
            $payment->status = SubscriptionPayment::STATUS_FAILED;
            $payment->gateway_response = ['error' => $e->getMessage()];
            $payment->save();

            throw $e;
        }
    }

    public function refundPayment(SubscriptionPayment $payment, ?float $amount = null): bool
    {
        if (! $this->stripe || ! $payment->gateway_transaction_id) {
            return false;
        }

        try {
            $refundData = [
                'payment_intent' => $payment->gateway_transaction_id,
            ];

            if ($amount !== null) {
                $refundData['amount'] = (int) ($amount * 100);
            }

            $refund = $this->stripe->refunds->create($refundData);

            $payment->markAsRefunded();
            $payment->gateway_response = array_merge(
                $payment->gateway_response ?? [],
                ['refund' => $refund->toArray()]
            );
            $payment->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPaymentStatus(SubscriptionPayment $payment): string
    {
        if (! $this->stripe || ! $payment->gateway_transaction_id) {
            return $payment->status;
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($payment->gateway_transaction_id);

            return match ($paymentIntent->status) {
                'succeeded' => SubscriptionPayment::STATUS_PAID,
                'canceled', 'requires_payment_method' => SubscriptionPayment::STATUS_FAILED,
                default => SubscriptionPayment::STATUS_PENDING,
            };
        } catch (\Exception $e) {
            return $payment->status;
        }
    }

    public function supportsRecurring(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'Stripe';
    }

    public function getIdentifier(): string
    {
        return 'stripe';
    }
}
