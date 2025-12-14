<?php

namespace Mhmadahmd\Filasaas\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mhmadahmd\Filasaas\Models\Plan;
use Mhmadahmd\Filasaas\Models\Subscription;

trait HasPlanSubscriptions
{
    public function planSubscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    public function activePlanSubscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriber')
            ->where(function ($query) {
                $query->where('ends_at', '>', now())
                    ->orWhereNull('ends_at');
            })
            ->whereNull('canceled_at');
    }

    public function subscribedTo(int $planId): bool
    {
        return $this->activePlanSubscriptions()
            ->where('plan_id', $planId)
            ->exists();
    }

    public function newPlanSubscription(string $name, Plan $plan): Subscription
    {
        $subscription = new Subscription;
        $subscription->subscriber_type = get_class($this);
        $subscription->subscriber_id = $this->id;
        $subscription->plan_id = $plan->id;
        $subscription->name = $name;
        $subscription->slug = \Illuminate\Support\Str::slug($name);

        return $subscription;
    }
}
