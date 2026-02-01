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
        Schema::create('camp_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained('camps')->onDelete('cascade');
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_attempt_id')->constrained('camp_payment_attempts')->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_session_id')->unique();
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('usd');
            $table->enum('status', ['succeeded', 'refunded', 'partially_refunded'])->default('succeeded');
            $table->timestamp('paid_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['camp_id', 'referee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_payments');
    }
};
