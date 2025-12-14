<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Available Plans -->
        <div>
            <h2 class="text-2xl font-bold mb-4">Available Plans</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($this->getAvailablePlans() as $plan)
                    <div class="border rounded-lg p-6">
                        <h3 class="text-xl font-semibold mb-2">{{ $plan->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ $plan->description }}</p>
                        <div class="text-3xl font-bold mb-4">
                            {{ number_format($plan->price, 2) }} {{ $plan->currency }}
                        </div>
                        <ul class="list-disc list-inside mb-4 space-y-2">
                            @foreach($plan->features as $feature)
                                <li>{{ $feature->name }}: {{ $feature->value }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Subscribe Form -->
        <div class="border rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Subscribe to a Plan</h2>
            <form wire:submit="subscribe">
                {{ $this->form }}
                <div class="mt-4">
                    <x-filament::button type="submit">
                        Subscribe
                    </x-filament::button>
                </div>
            </form>
        </div>

        <!-- Current Subscriptions -->
        <div>
            <h2 class="text-2xl font-bold mb-4">Current Subscriptions</h2>
            <div class="space-y-4">
                @forelse($this->getCurrentSubscriptions() as $subscription)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-semibold">{{ $subscription->plan->name }}</h3>
                                <p class="text-sm text-gray-600">
                                    Status: 
                                    @if($subscription->active())
                                        <span class="text-green-600">Active</span>
                                    @elseif($subscription->canceled())
                                        <span class="text-red-600">Canceled</span>
                                    @else
                                        <span class="text-gray-600">Inactive</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">
                                    Ends: {{ $subscription->ends_at?->format('Y-m-d') ?? 'Never' }}
                                </p>
                            </div>
                            @if($subscription->active() && !$subscription->canceled())
                                <x-filament::button 
                                    wire:click="cancelSubscription({{ $subscription->id }})"
                                    color="danger"
                                    size="sm"
                                    tag="button"
                                >
                                    Cancel
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">No active subscriptions.</p>
                @endforelse
            </div>
        </div>

        <!-- Payment History -->
        <div>
            <h2 class="text-2xl font-bold mb-4">Payment History</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">Date</th>
                            <th class="text-left p-2">Amount</th>
                            <th class="text-left p-2">Gateway</th>
                            <th class="text-left p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->getPaymentHistory() as $payment)
                            <tr class="border-b">
                                <td class="p-2">{{ $payment->created_at->format('Y-m-d') }}</td>
                                <td class="p-2">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td class="p-2">{{ ucfirst($payment->gateway) }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-1 rounded text-sm
                                        @if($payment->status === 'paid') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif
                                    ">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                    @if($payment->requires_approval && $payment->isPending())
                                        <span class="ml-2 text-xs text-orange-600">(Pending Approval)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-600">No payment history.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

