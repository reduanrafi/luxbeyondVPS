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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, BDT, CNY, etc.
            $table->string('name'); // United States Dollar
            $table->string('symbol', 10); // $, ৳, ¥
            $table->decimal('rate_to_base', 10, 4)->default(1); // Rate to base currency (BDT)
            $table->boolean('is_base')->default(false); // Is this the base currency?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
