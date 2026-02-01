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
        // Referee check-ins for camps
        Schema::create('camp_referee_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained()->onDelete('cascade');
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->unique(['camp_id', 'referee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_referee_checkins');
    }
};
