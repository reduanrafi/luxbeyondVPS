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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Shipping Charge, Weight Charge, Tax, etc.
            $table->string('type'); // shipping, weight, tax, service_fee, handling, etc.
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 10, 2)->default(0); // Fixed amount or percentage
            $table->decimal('min_value', 10, 2)->nullable(); // Minimum charge
            $table->decimal('max_value', 10, 2)->nullable(); // Maximum charge
            $table->json('conditions')->nullable(); // Additional conditions (weight ranges, distance, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
