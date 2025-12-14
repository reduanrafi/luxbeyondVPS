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
        Schema::create('traveller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('passport_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('verification_status')->default('pending'); // pending, approved, rejected
            $table->text('documents')->nullable(); // JSON array of file paths
            $table->text('travel_history')->nullable(); // JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traveller_profiles');
    }
};
