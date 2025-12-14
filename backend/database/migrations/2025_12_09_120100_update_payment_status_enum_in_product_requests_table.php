<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL because Doctrine DBAL has trouble with ENUMs sometimes
        DB::statement("ALTER TABLE product_requests MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'active', 'partial', 'unpaid', 'processing') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE product_requests MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'active', 'partial') NOT NULL DEFAULT 'pending'");
    }
};
