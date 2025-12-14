<?php

namespace Mhmadahmd\Filasaas\Services\Gateways;

use Mhmadahmd\Filasaas\Contracts\PaymentGatewayInterface;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalGateway implements PaymentGatewayInterface
{
    protected $paypal;

    public function __construct()
    {
        $config = config('filasaas.gateways.paypal');

        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials([
            'mode' => $config['mode'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);

        $this->paypal->getAccessToken();
    }

    public function processPayment(SubscriptionPayment $payment, array $options = []): mixed
    {
        try {
            $subscription = $payment->subscription;
            $plan = $subscription->plan;

            // Check if this is a recurring subscription
            if ($plan->invoice_period > 0 && $plan->paypal_plan_id) {
                // Handle recurring subscription via PayPal Billing Plans
                $response = $this->paypal->createSubscription([
                    'plan_id' => $plan->paypal_plan_id,
                    'subscriber' => [
                        'name' => [
                            'given_name' => $subscription->subscriber->name ?? 'Customer',
                        ],
                        'email_address' => $subscription->subscriber->email ?? null,
                    ],
                ]);

                if (isset($response['id'])) {
                    $subscription->paypal_subscription_id = $response['id'];
                    $subscription->save();

                    $payment->gateway_transaction_id = $response['id'];
                    $payment->gateway_response = $response;
                    $payment->status = SubscriptionPayment::STATUS_PENDING;
                    $payment->save();
                }

                return $response;
            }

            // Create a one-time payment order
            $order = $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $payment->currency,
                            'value' => (string) $payment->amount,
                        ],
                        'description' => 'Subscription Payment',
                    ],
                ],
            ]);

            if (isset($order['id'])) {
                $payment->gateway_transaction_id = $order['id'];
                $payment->gateway_response = $order;
                $payment->status = SubscriptionPayment::STATUS_PENDING;
                $payment->save();
            }

            return $order;
        } catch (\Exception $e) {
            $payment->status = SubscriptionPayment::STATUS_FAILED;
            $payment->gateway_response = ['error' => $e->getMessage()];
            $payment->save();

            throw $e;
        }
    }

    public function refundPayment(SubscriptionPayment $payment, ?float $amount = null): bool
    {
        if (! $payment->gateway_transaction_id) {
            return false;
        }

        try {
            $refundData = [
                'transaction_id' => $payment->gateway_transaction_id,
            ];

            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency_code' => $payment->currency,
                    'value' => (string) $amount,
                ];
            }

            $refund = $this->paypal->refundCapturedPayment($refundData);

            if (isset($refund['status']) && $refund['status'] === 'COMPLETED') {
                $payment->markAsRefunded();
                $payment->gateway_response = array_merge(
                    $payment->gateway_response ?? [],
                    ['refund' => $refund]
                );
                $payment->save();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPaymentStatus(SubscriptionPayment $payment): string
    {
        if (! $payment->gateway_transaction_id) {
            return $payment->status;
        }

        try {
            $order = $this->paypal->showOrderDetails($payment->gateway_transaction_id);

            if (isset($order['status'])) {
                return match ($order['status']) {
                    'COMPLETED' => SubscriptionPayment::STATUS_PAID,
                    'CANCELLED', 'FAILED' => SubscriptionPayment::STATUS_FAILED,
                    default => SubscriptionPayment::STATUS_PENDING,
                };
            }

            return $payment->status;
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
        return 'PayPal';
    }

    public function getIdentifier(): string
    {
        return 'paypal';
    }
}
