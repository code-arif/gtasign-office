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
        Schema::create('order_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('service_rating')->nullable();
            $table->unsignedTinyInteger('delivery_rating')->nullable();

            $table->text('review')->nullable();
            $table->text('seller_reply')->nullable();

            $table->boolean('is_public')->default(true);

            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            $table->unique('order_id');
            $table->index('gig_id');
            $table->index(['reviewed_user_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_reviews');
    }
};
