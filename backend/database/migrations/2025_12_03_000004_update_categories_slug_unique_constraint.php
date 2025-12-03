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
        Schema::table('categories', function (Blueprint $table) {
            // Drop unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add composite unique constraint on slug and parent_id
            $table->unique(['slug', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique(['slug', 'parent_id']);
            
            // Restore unique constraint on slug
            $table->unique('slug');
        });
    }
};
