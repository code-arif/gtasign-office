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
        Schema::create('camp_evaluator_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained('camps')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('registration_note')->nullable(); // Evaluator's note
            $table->text('rejection_reason')->nullable(); // Director's rejection reason
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Director who approved
            $table->boolean('can_view_own_evaluations')->default(true);
            $table->timestamps();

            // Prevent duplicate registrations
            $table->unique(['camp_id', 'evaluator_id']);

            // Indexes
            $table->index(['camp_id', 'status']);
            $table->index('evaluator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_evaluator_registrations');
    }
};
