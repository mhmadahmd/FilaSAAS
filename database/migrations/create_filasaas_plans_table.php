<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filasaas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('signup_fee', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            // Trial settings
            $table->integer('trial_period')->default(0);
            $table->string('trial_interval', 20)->default('day'); // day, week, month, year

            // Billing settings
            $table->integer('invoice_period')->default(1);
            $table->string('invoice_interval', 20)->default('month'); // day, week, month, year

            // Grace period
            $table->integer('grace_period')->default(0);
            $table->string('grace_interval', 20)->default('day');

            // Proration settings
            $table->integer('prorate_day')->nullable();
            $table->integer('prorate_period')->nullable();
            $table->boolean('prorate_extend_due')->default(false);

            // Limits
            $table->integer('active_subscribers_limit')->nullable();
            $table->integer('sort_order')->default(0);

            // Gateway configuration
            $table->boolean('cash_auto_approve')->default(false);
            $table->json('allowed_payment_gateways')->nullable();

            // External gateway IDs
            $table->string('stripe_price_id')->nullable();
            $table->string('paddle_price_id')->nullable();
            $table->string('paypal_plan_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filasaas_plans');
    }
};
