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
        Schema::create('game_slot_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_slot_id')->constrained('game_slots')->onDelete('cascade');

            // Polymorphic: either a crew OR individual referees
            $table->nullableMorphs('assignable');

            $table->enum('assignment_type', ['crew', 'individual'])->index();
            $table->string('position')->nullable();
            $table->boolean('is_auto_assigned')->default(false);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // Custom short name for unique constraint
            $table->unique(
                ['game_slot_id', 'assignable_id', 'assignable_type'],
                'slot_assignable_unique' // Short custom name
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_slot_assignments');
    }
};
