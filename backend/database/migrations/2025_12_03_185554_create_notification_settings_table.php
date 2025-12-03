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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // order_placed, order_status_changed, payment_received, etc.
            $table->string('channel'); // email, sms, push, in_app, webhook
            $table->string('recipient_type')->default('customer'); // customer, admin, both
            $table->boolean('enabled')->default(true);
            $table->string('template_name')->nullable(); // Email/SMS template identifier
            $table->text('subject')->nullable(); // Email subject or SMS prefix
            $table->text('message_template')->nullable(); // Message template with variables
            $table->json('variables')->nullable(); // Available variables for template
            $table->json('conditions')->nullable(); // Conditions for sending (e.g., order amount > 1000)
            $table->json('recipients')->nullable(); // Specific recipients (emails, phone numbers)
            $table->integer('delay_minutes')->default(0); // Delay before sending
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['event_type', 'channel', 'recipient_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
