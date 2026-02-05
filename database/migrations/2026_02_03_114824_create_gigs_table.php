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
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();

            // Owner of the gig (freelancer)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Category hierarchy
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Gig overview
            $table->string('title', 100);
            $table->text('scope')->nullable(); // Detailed description of services

            // Pricing & delivery
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedSmallInteger('delivery_days')->nullable(); // Delivery timeline in days

            // Requirements (kept as JSON for flexibility)
            $table->json('system_questions')->nullable(); // Auto-generated questions
            $table->json('custom_questions')->nullable(); // Seller-defined questions

            // Status & moderation
            $table->enum('status', ['draft','pending_approval','active','rejected'])->default('draft');

            $table->text('rejection_reason')->nullable();

            // Analytics counters (fast reads, no joins)
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('orders')->default(0);
            $table->unsignedInteger('cancellations')->default(0);

            $table->timestamp('published_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};
