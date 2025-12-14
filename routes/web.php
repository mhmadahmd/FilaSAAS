<?php

use Illuminate\Support\Facades\Route;
use Mhmadahmd\Filasaas\Http\Controllers\Webhooks\CustomGatewayWebhookController;
use Mhmadahmd\Filasaas\Http\Controllers\Webhooks\PayPalWebhookController;

$webhookPrefix = config('filasaas.webhooks.prefix', 'webhooks/billing');
$webhookMiddleware = config('filasaas.webhooks.middleware', ['api']);

Route::prefix($webhookPrefix)
    ->middleware($webhookMiddleware)
    ->group(function () {
        // PayPal webhook
        Route::post('paypal', [PayPalWebhookController::class, 'handle'])
            ->name('filasaas.webhooks.paypal');

        // Custom gateway webhooks
        Route::post('{gateway}', [CustomGatewayWebhookController::class, 'handle'])
            ->name('filasaas.webhooks.custom');
    });
