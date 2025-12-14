<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filasaas_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('filasaas_plans')->onDelete('cascade');
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('value')->default('false'); // 'true', 'false', number, or 'unlimited'
            $table->integer('resettable_period')->nullable();
            $table->string('resettable_interval', 20)->nullable(); // day, week, month, year
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['plan_id', 'slug']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filasaas_plan_features');
    }
};
