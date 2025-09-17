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
        Schema::create('innochannel_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index(); // debug, info, warning, error, critical
            $table->string('channel', 50)->index(); // booking, property, inventory, webhook, etc.
            $table->string('action', 100)->nullable(); // create_booking, update_property, etc.
            $table->text('message');
            $table->json('context')->nullable(); // Additional context data
            $table->json('extra')->nullable(); // Extra metadata
            $table->string('request_id', 100)->nullable()->index(); // For request tracing
            $table->string('user_id', 100)->nullable()->index(); // User who triggered the action
            $table->string('property_id', 100)->nullable()->index(); // Related property
            $table->string('booking_id', 100)->nullable()->index(); // Related booking
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['level', 'created_at']);
            $table->index(['channel', 'created_at']);
            $table->index(['request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('innochannel_logs');
    }
};