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
        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', [
                'order_placed',
                'payment_received',
                'seller_started',
                'delivery_submitted',
                'qa_approved',
                'qa_rejected',
                'delivered_to_client',
                'revision_requested',
                'extension_requested',
                'extension_approved',
                'extension_rejected',
                'order_completed',
                'order_cancelled',
                'escrow_released',
                'auto_completed'
            ]);

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_activities');
    }
};
