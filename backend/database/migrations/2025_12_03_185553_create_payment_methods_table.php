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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // bKash, Bank Transfer, Nagad, etc.
            $table->string('type'); // bkash, bank_transfer, nagad, cash_on_delivery, stripe, paypal
            $table->string('sub_type')->nullable(); // For bKash: 'api' or 'manual', For bank: 'transfer'
            $table->text('description')->nullable();
            $table->json('config')->nullable(); // Store method-specific config (account numbers, API keys, etc.)
            $table->string('account_number')->nullable(); // For manual bKash or bank account
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable(); // For bank transfer
            $table->string('branch_name')->nullable(); // For bank transfer
            $table->string('routing_number')->nullable(); // For bank transfer
            $table->string('swift_code')->nullable(); // For bank transfer
            $table->text('instructions')->nullable(); // Payment instructions for customers
            $table->string('icon')->nullable(); // Icon/image for the payment method
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false); // API-based payment (true) or manual (false)
            $table->integer('sort_order')->default(0);
            $table->decimal('fee', 10, 2)->default(0); // Processing fee
            $table->decimal('fee_percentage', 5, 2)->default(0); // Percentage fee
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
