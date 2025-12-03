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
            // Drop unique constraint on name
            $table->dropUnique(['name']);
            
            // Add composite unique constraint on name and parent_id
            $table->unique(['name', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique(['name', 'parent_id']);
            
            // Restore unique constraint on name
            $table->unique('name');
        });
    }
};
