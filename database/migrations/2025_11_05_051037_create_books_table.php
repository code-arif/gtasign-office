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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('author');
            $table->json('category_ids');
            $table->string('isbn')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();

            $table->enum('type', ['physical', 'ebook', 'premium']);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_subscription_only')->default(false);
            $table->integer('preview_pages')->default(10);
            $table->integer('total_pages')->nullable();

            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);


            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor'])->nullable();
            $table->integer('weight_gram')->nullable();
            $table->string('dimensions')->nullable();

            $table->string('pdf_file')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'published', 'rejected', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['type', 'status']);
            $table->index('is_premium');
            $table->index('price');
            $table->index('published_at');
            $table->index('rating_avg');
            $table->fullText(['title', 'author', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
