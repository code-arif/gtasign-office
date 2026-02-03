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
        Schema::create('gig_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();

            $table->string('path'); // Image storage path
            $table->boolean('is_primary')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('gig_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gig_images');
    }
};
