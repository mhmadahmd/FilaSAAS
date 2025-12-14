<?php

namespace Mhmadahmd\Filasaas\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mhmadahmd\Filasaas\Services\PaymentGatewayManager;

class CustomGatewayWebhookController extends Controller
{
    public function handle(Request $request, string $gateway)
    {
        $gatewayManager = app(PaymentGatewayManager::class);
        $gatewayInstance = $gatewayManager->get($gateway);

        if (! $gatewayInstance) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        // Allow custom gateways to handle their own webhooks
        // Developers can extend this controller or register custom webhook handlers
        if (method_exists($gatewayInstance, 'handleWebhook')) {
            return $gatewayInstance->handleWebhook($request);
        }

        return response()->json(['status' => 'webhook received'], 200);
    }
}
