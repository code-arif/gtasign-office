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
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('delivery_number')->default(1);
            $table->text('message')->nullable();
            $table->json('files')->nullable();

            $table->enum('status', [
                'pending_qa',
                'qa_approved',
                'qa_rejected',
                'delivered_to_client',
                'accepted',
                'revision_requested'
            ])->default('pending_qa');

            $table->text('revision_reason')->nullable();
            $table->text('qa_feedback')->nullable();

            $table->timestamp('submitted_at');
            $table->timestamp('qa_reviewed_at')->nullable();
            $table->timestamp('delivered_to_client_at')->nullable();
            $table->timestamp('client_reviewed_at')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
