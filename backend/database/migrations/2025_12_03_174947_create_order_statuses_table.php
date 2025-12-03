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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // pending, processing, shipped, delivered, cancelled, etc.
            $table->string('label'); // Pending, Processing, Shipped, etc.
            $table->string('color', 7)->default('#6B7280'); // Hex color for UI
            $table->string('icon')->nullable(); // Icon name
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false); // Default status for new orders
            $table->boolean('is_final')->default(false); // Final status (completed, cancelled)
            $table->boolean('is_active')->default(true);
            $table->json('allowed_next_statuses')->nullable(); // Array of status IDs that can follow this
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
