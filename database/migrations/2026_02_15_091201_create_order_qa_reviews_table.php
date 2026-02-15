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
        Schema::create('order_qa_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('order_deliveries')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'revision_required'
            ])->default('pending');

            $table->text('feedback')->nullable();
            $table->json('issues')->nullable();

            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_qa_reviews');
    }
};
