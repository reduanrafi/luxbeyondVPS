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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('position', ['hero', 'sidebar', 'both'])->default('hero');
            $table->string('url')->nullable(); // Custom URL for event
            $table->string('button_text')->default('Shop Now');
            $table->string('button_color')->default('#7c3aed'); // Primary color
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('priority')->default(0); // Higher priority shows first
            $table->json('meta')->nullable(); // Additional metadata
            $table->timestamps();
            
            // Indexes for performance
            $table->index('slug');
            $table->index('is_active');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

