<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('gateway_subscription_id');
            $table->string('plan_id');
            $table->string('customer_id');
            $table->string('status');
            $table->decimal('amount', 15, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'gateway_subscription_id']);
            $table->index(['customer_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_subscriptions');
    }
};