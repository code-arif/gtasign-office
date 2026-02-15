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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            // Participants (2 users only - expert & client)
            $table->foreignId('first_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('second_user_id')->constrained('users')->cascadeOnDelete();

            // Room metadata
            $table->boolean('has_active_order')->default(false); // Quick check if order exists
            $table->timestamp('last_message_at')->nullable(); // For sorting rooms

            $table->timestamps();

            // Indexes
            $table->unique(['first_user_id', 'second_user_id']);
            $table->index('has_active_order');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
