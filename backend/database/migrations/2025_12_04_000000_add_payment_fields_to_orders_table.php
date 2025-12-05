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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('bkash_trx_id')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('bkash_trx_id');
            $table->string('payment_slip')->nullable()->after('payment_reference'); // Path to uploaded payment slip
            $table->timestamp('paid_at')->nullable()->after('payment_slip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bkash_trx_id', 'payment_reference', 'payment_slip', 'paid_at']);
        });
    }
};

