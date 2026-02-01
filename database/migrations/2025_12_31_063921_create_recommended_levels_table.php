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
        Schema::create('recommended_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('referee_evaluations')->onDelete('cascade');
            $table->enum('level', ['NCAA D1', 'NCAA D2', 'NAIA', 'JUCO', 'HS', 'JH/ELEM']);
            $table->timestamps();

            $table->index('evaluation_id');
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommended_levels');
    }
};
