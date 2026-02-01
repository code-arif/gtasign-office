<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schedules table
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained()->onDelete('cascade');
            $table->integer('game_duration'); // in minutes
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });

        // Schedule locations (courts)
        Schema::create('schedule_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('court_count')->default(1);
            $table->timestamps();
        });

        // Daily time ranges
        Schema::create('schedule_time_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_time_ranges');
        Schema::dropIfExists('schedule_locations');
        Schema::dropIfExists('schedules');
    }
};
