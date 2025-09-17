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
        Schema::create('innochannel_sync_status', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50)->index(); // reservation, property, inventory
            $table->string('entity_id', 100)->index(); // ID of the entity being synced
            $table->string('pms_system', 50)->index(); // opera, fidelio, etc.
            $table->string('sync_type', 50); // full, partial, delta
            $table->string('status', 20)->default('pending')->index(); // pending, in_progress, completed, failed
            $table->json('sync_data')->nullable(); // Data being synced
            $table->json('response_data')->nullable(); // Response from PMS
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            // Composite indexes
            $table->index(['entity_type', 'entity_id']);
            $table->index(['status', 'next_retry_at']);
            $table->index(['pms_system', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('innochannel_sync_status');
    }
};
