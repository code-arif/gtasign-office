<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('proficiency_level')->nullable();
            // $table->enum('group', ['native', 'fluent', 'learning'])->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles_languages');
    }
};
