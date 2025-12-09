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
        Schema::table('product_requests', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'active', 'partial'])->default('pending')->after('status');
            $table->string('payment_reference')->nullable()->after('payment_status'); // For bank transaction ID or Gateway Payment ID
            $table->string('bkash_trx_id')->nullable()->after('payment_reference');
            $table->string('payment_slip')->nullable()->after('bkash_trx_id');
            $table->timestamp('paid_at')->nullable()->after('payment_slip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_reference', 'bkash_trx_id', 'payment_slip', 'paid_at']);
        });
    }
};
