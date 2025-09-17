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
        Schema::create('innochannel_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100)->index(); // reservation.created, property.updated, etc.
            $table->string('webhook_id', 100)->unique(); // Unique identifier from Innochannel
            $table->json('payload'); // The webhook payload
            $table->string('status', 20)->default('pending')->index(); // pending, processing, completed, failed
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('headers')->nullable(); // Request headers
            $table->string('signature', 255)->nullable(); // Webhook signature for verification
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['attempts', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('innochannel_webhooks');
    }
};
