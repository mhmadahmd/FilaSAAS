<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filasaas_subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('filasaas_subscriptions')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('filasaas_plan_features')->onDelete('cascade');
            $table->integer('used')->default(0);
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'feature_id']);
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filasaas_subscription_usage');
    }
};
