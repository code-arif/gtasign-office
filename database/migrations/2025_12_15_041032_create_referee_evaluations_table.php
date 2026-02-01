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
        Schema::create('referee_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('camp_id')->constrained('camps')->onDelete('cascade');
            $table->foreignId('game_slot_id')->nullable()->constrained('game_slots')->onDelete('set null');

            // Performance Criteria (10 = Excellent, 1 = Poor)
            $table->tinyInteger('call_accuracy')->nullable(); // 10=Excellent; 1=Poor
            $table->tinyInteger('communication_skills')->nullable(); // 10=Excellent; 1=Poor
            $table->tinyInteger('consistency_of_calls')->nullable(); // 10=Excellent; 1=Poor
            $table->tinyInteger('court_position_mechanics')->nullable(); // 10=Excellent; 1=Poor
            $table->tinyInteger('fitness_mobility')->nullable(); // 10=Excellent; 1=Poor
            $table->tinyInteger('game_awareness')->nullable(); // 10=Excellent; 1=Poor

            // Total and avarage Score (auto calculated)
            $table->decimal('total_score', 5, 2)->nullable();
            $table->decimal('average_score', 5, 2)->nullable();

            // Comments & Feedback
            $table->text('private_comments')->nullable(); // Only evaluator can see
            $table->text('referee_feedback')->nullable(); // Referee can see

            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // Indexes for faster queries
            $table->index('referee_id');
            $table->index('evaluator_id');
            $table->index('camp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referee_evaluations');
    }
};
