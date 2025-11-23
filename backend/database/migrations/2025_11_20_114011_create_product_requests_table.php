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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->string('currency');
            $table->decimal('declared_shipping_cost', 10, 2)->default(0);
            $table->boolean('is_inside_city')->default(false);
            $table->string('status')->default('pending'); // pending, approved, confirmed, paid, processing, completed, rejected
            $table->string('admin_image_url')->nullable();
            $table->text('admin_note')->nullable();
            $table->decimal('total_amount_bdt', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
