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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('transaction_id');
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('amount', 15, 4);
            $table->string('currency', 3);
            $table->string('status');
            $table->text('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'transaction_id']);
            $table->index(['gateway_transaction_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};