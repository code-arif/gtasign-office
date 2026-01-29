<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username')->unique();
            $table->string('image')->nullable();
            $table->text('tag_line')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
       
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
