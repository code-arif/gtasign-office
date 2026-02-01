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
        // Crew members (junction table for many-to-many)
        Schema::create('crew_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained()->onDelete('cascade');
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // A referee can only be in ONE crew per camp at a time
            // This will be enforced at application level
            $table->unique(['crew_id', 'referee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_members');
    }
};
