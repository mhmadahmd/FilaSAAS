<?php

namespace Mhmadahmd\Filasaas\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mhmadahmd\Filasaas\Models\Subscription;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCompleted($resource);

                break;

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePaymentFailed($resource);

                break;

            case 'BILLING.SUBSCRIPTION.CREATED':
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handleSubscriptionActivated($resource);

                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.EXPIRED':
                $this->handleSubscriptionCancelled($resource);

                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $this->handleSubscriptionPaymentFailed($resource);

                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentCompleted(array $resource)
    {
        $transactionId = $resource['id'] ?? null;

        if (! $transactionId) {
            return;
        }

        $payment = SubscriptionPayment::where('gateway_transaction_id', $transactionId)
            ->where('gateway', SubscriptionPayment::GATEWAY_PAYPAL)
            ->first();

        if ($payment) {
            $payment->markAsPaid();
            $payment->gateway_response = array_merge(
                $payment->gateway_response ?? [],
                ['webhook' => $resource]
            );
            $payment->save();
        }
    }

    protected function handlePaymentFailed(array $resource)
    {
        $transactionId = $resource['id'] ?? null;

        if (! $transactionId) {
            return;
        }

        $payment = SubscriptionPayment::where('gateway_transaction_id', $transactionId)
            ->where('gateway', SubscriptionPayment::GATEWAY_PAYPAL)
            ->first();

        if ($payment) {
            $payment->markAsFailed();
            $payment->gateway_response = array_merge(
                $payment->gateway_response ?? [],
                ['webhook' => $resource]
            );
            $payment->save();
        }
    }

    protected function handleSubscriptionActivated(array $resource)
    {
        $subscriptionId = $resource['id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->renew();
        }
    }

    protected function handleSubscriptionCancelled(array $resource)
    {
        $subscriptionId = $resource['id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->cancel();
        }
    }

    protected function handleSubscriptionPaymentFailed(array $resource)
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? $resource['id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            // Create a failed payment record
            $payment = new SubscriptionPayment;
            $payment->subscription_id = $subscription->id;
            $payment->gateway = SubscriptionPayment::GATEWAY_PAYPAL;
            $payment->payment_method = SubscriptionPayment::METHOD_ONLINE;
            $payment->amount = $subscription->plan->price;
            $payment->currency = $subscription->plan->currency;
            $payment->status = SubscriptionPayment::STATUS_FAILED;
            $payment->gateway_response = $resource;
            $payment->save();
        }
    }
}
