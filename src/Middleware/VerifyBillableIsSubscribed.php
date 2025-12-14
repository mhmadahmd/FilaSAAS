<?php

namespace Mhmadahmd\Filasaas\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBillableIsSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $billingProvider = app(\Mhmadahmd\Filasaas\Services\TenantBillingProvider::class);

        if (! $billingProvider->hasActiveSubscription($user)) {
            return redirect()->route('filament.pages.billing');
        }

        return $next($request);
    }
}
