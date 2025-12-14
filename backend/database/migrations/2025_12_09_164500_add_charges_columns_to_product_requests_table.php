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
            $table->decimal('tax', 10, 2)->default(0)->after('total_amount_bdt');
            $table->decimal('delivery_charge', 10, 2)->default(0)->after('tax');
            $table->decimal('payment_processing_fee', 10, 2)->default(0)->after('delivery_charge');
            $table->decimal('additional_charges', 10, 2)->default(0)->after('payment_processing_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn(['tax', 'delivery_charge', 'payment_processing_fee', 'additional_charges']);
        });
    }
};
