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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            $table->text('text')->nullable();
            $table->string('file')->nullable();

            $table->enum('type', [
                'text',
                'custom_offer',
                'offer_accepted',
                'offer_rejected',
                'offer_withdrawn',
                'order_placed',
                'delivery_submitted',
                'delivery_approved',
                'delivery_rejected',
                'delivery_sent',
                'extension_request',
                'extension_approved',
                'extension_rejected',
                'revision_request',
                'order_completed',
                'order_cancelled',
                'system'
            ])->default('text');

            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('custom_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('order_deliveries')->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['sender_id', 'receiver_id']);
            $table->index(['room_id', 'created_at']);
            $table->index('type');
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
