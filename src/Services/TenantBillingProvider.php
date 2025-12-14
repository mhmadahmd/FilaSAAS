<?php

namespace Mhmadahmd\Filasaas\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Mhmadahmd\Filasaas\Models\Plan;
use Mhmadahmd\Filasaas\Models\Subscription;
use Mhmadahmd\Filasaas\Models\SubscriptionPayment;

class TenantBillingProvider
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {}

    public function subscribeToPlan(int $planId, string $gateway, array $options = []): Subscription
    {
        $plan = Plan::findOrFail($planId);
        $billable = $options['billable'] ?? Auth::user();

        if (! $billable) {
            throw new \Exception('No billable model found.');
        }

        // Check if gateway is available for this plan
        if (! $this->gatewayManager->isGatewayAvailable($gateway, $plan)) {
            throw new \Exception("Gateway '{$gateway}' is not available for this plan.");
        }

        // Create subscription
        $subscription = $billable->newPlanSubscription(
            $options['name'] ?? config('filasaas.default_subscription_name'),
            $plan
        );

        // Calculate dates
        $period = new Period($plan->invoice_interval, $plan->invoice_period);
        $subscription->starts_at = $period->getStartDate();
        $subscription->ends_at = $period->getEndDate();

        // Set trial period if applicable
        if ($plan->hasTrial()) {
            $trialPeriod = new Period($plan->trial_interval, $plan->trial_period);
            $subscription->trial_ends_at = $trialPeriod->getEndDate();
        }

        $subscription->save();

        // Create payment
        $payment = new SubscriptionPayment;
        $payment->subscription_id = $subscription->id;
        $payment->gateway = $gateway;
        $payment->payment_method = $this->getPaymentMethodForGateway($gateway);
        $payment->amount = $plan->price + $plan->signup_fee;
        $payment->currency = $plan->currency;
        $payment->status = SubscriptionPayment::STATUS_PENDING;
        $payment->save();

        // Process payment through gateway
        $this->gatewayManager->processPayment($payment);

        return $subscription->fresh();
    }

    public function cancelSubscription(int $subscriptionId, bool $immediately = false): Subscription
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        return $subscription->cancel($immediately);
    }

    public function switchPlan(int $subscriptionId, int $newPlanId, array $options = []): Subscription
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $newPlan = Plan::findOrFail($newPlanId);

        return $subscription->changePlan($newPlan, $options);
    }

    public function getCurrentSubscriptions($billable = null): Collection
    {
        $billable = $billable ?? Auth::user();

        if (! $billable) {
            return collect();
        }

        return Subscription::ofSubscriber($billable)->get();
    }

    public function getActiveSubscriptions($billable = null): Collection
    {
        $billable = $billable ?? Auth::user();

        if (! $billable) {
            return collect();
        }

        return Subscription::ofSubscriber($billable)
            ->findActive()
            ->get();
    }

    public function getAvailablePlans(): Collection
    {
        return Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getPaymentHistory(?int $limit = 10, $billable = null): Collection
    {
        $billable = $billable ?? Auth::user();

        if (! $billable) {
            return collect();
        }

        $subscriptionIds = Subscription::ofSubscriber($billable)->pluck('id');

        return SubscriptionPayment::whereIn('subscription_id', $subscriptionIds)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function hasActiveSubscription($billable = null): bool
    {
        return $this->getActiveSubscriptions($billable)->isNotEmpty();
    }

    public function isSubscribedTo(int $planId, $billable = null): bool
    {
        $billable = $billable ?? Auth::user();

        if (! $billable) {
            return false;
        }

        return $billable->subscribedTo($planId);
    }

    protected function getPaymentMethodForGateway(string $gateway): string
    {
        return match ($gateway) {
            'cash' => SubscriptionPayment::METHOD_CASH,
            'stripe', 'paypal' => SubscriptionPayment::METHOD_ONLINE,
            default => SubscriptionPayment::METHOD_ONLINE,
        };
    }
}
