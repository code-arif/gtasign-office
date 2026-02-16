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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();

            // Relationships
            $table->foreignId('gig_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('custom_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('seller_earnings', 10, 2);

            // Delivery Info
            $table->unsignedSmallInteger('delivery_days');
            $table->timestamp('expected_delivery_at')->nullable();
            $table->unsignedTinyInteger('max_revisions')->default(1);
            $table->unsignedTinyInteger('revision_count')->default(0);

            // Requirements
            $table->text('requirements')->nullable();

            // Order Status
            $table->enum('status', ['pending_payment','active','qa_pending','qa_rejected','delivered','revision_requested','completed','cancelled','disputed'])->default('pending_payment');

            // Payment & Escrow
            $table->string('payment_method')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('funds_in_escrow')->default(false);
            $table->timestamp('escrow_released_at')->nullable();

            // Cancellation
            $table->text('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['buyer', 'seller', 'mutual', 'admin'])->nullable();

            // Auto-completion
            $table->boolean('auto_complete_enabled')->default(true);
            $table->timestamp('auto_complete_at')->nullable();

            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('qa_submitted_at')->nullable();
            $table->timestamp('qa_approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index('order_number');
            $table->index('status');
            $table->index('funds_in_escrow');
            $table->index('expected_delivery_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
