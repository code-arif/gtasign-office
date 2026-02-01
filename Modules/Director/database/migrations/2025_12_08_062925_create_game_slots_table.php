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
        Schema::create('game_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_location_id')->constrained()->onDelete('cascade');
            $table->date('game_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('court_name'); // Court 1, Court 2, etc.
            $table->integer('court_number');
            $table->enum('status', ['available', 'assigned', 'completed'])->default('available');
            $table->boolean('is_block')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_slots');
    }
};
