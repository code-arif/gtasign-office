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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('code', 50)->nullable(); // e.g., 'C01'
            $table->string('color_name', 255)->nullable(); // e.g., 'Classic Black'
            $table->decimal('price', 10, 2)->notNull();
            $table->integer('stock')->notNull()->default(0);
            $table->string('image_url', 255)->nullable(); // Variant-specific image
            $table->json('additional_info')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
