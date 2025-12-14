<?php

namespace Mhmadahmd\Filasaas\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Mhmadahmd\Filasaas\Models\Plan;
use Mhmadahmd\Filasaas\Services\TenantBillingProvider;

class BillingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected string $view = 'filasaas::pages.billing';

    protected static ?string $navigationLabel = 'Billing';

    protected static ?string $title = 'Billing & Subscriptions';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('plan_id')
                    ->label('Select Plan')
                    ->options(function () {
                        return Plan::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($plan) {
                                $name = is_array($plan->name)
                                    ? ($plan->name[app()->getLocale()] ?? reset($plan->name))
                                    : $plan->name;

                                return [$plan->id => $name];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('gateway')
                    ->label('Payment Gateway')
                    ->options(function ($get) {
                        $planId = $get('plan_id');
                        if (! $planId) {
                            return [];
                        }

                        $plan = Plan::find($planId);
                        if (! $plan) {
                            return [];
                        }

                        $gatewayManager = app(\Mhmadahmd\Filasaas\Services\PaymentGatewayManager::class);
                        $availableGateways = $gatewayManager->getAvailableForPlan($plan);

                        $options = [];
                        foreach ($availableGateways as $identifier => $gateway) {
                            $options[$identifier] = $gateway->getName();
                        }

                        return $options;
                    })
                    ->required()
                    ->live(),
            ])
            ->statePath('data');
    }

    public function subscribe(): void
    {
        $data = $this->form->getState();
        $billingProvider = app(TenantBillingProvider::class);

        try {
            $subscription = $billingProvider->subscribeToPlan(
                $data['plan_id'],
                $data['gateway']
            );

            Notification::make()
                ->title('Subscription Created')
                ->success()
                ->send();

            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelSubscription(int $subscriptionId): void
    {
        $billingProvider = app(TenantBillingProvider::class);

        try {
            $billingProvider->cancelSubscription($subscriptionId);

            Notification::make()
                ->title('Subscription Canceled')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getAvailablePlans()
    {
        $billingProvider = app(TenantBillingProvider::class);

        return $billingProvider->getAvailablePlans();
    }

    public function getCurrentSubscriptions()
    {
        $billingProvider = app(TenantBillingProvider::class);

        return $billingProvider->getCurrentSubscriptions();
    }

    public function getPaymentHistory()
    {
        $billingProvider = app(TenantBillingProvider::class);

        return $billingProvider->getPaymentHistory(10);
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? 'Billing';
    }
}
