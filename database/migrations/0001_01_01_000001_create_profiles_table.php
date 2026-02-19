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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('slug')->unique();

            $table->string('tagline')->nullable();
            $table->text('biography')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();

            $table->string('stripe_account_id')->nullable();
            $table->timestamp('stripe_onboarded_at')->nullable();

            $table->boolean('is_pinned')->default(false);
            $table->string('level')->nullable();
            $table->string('level')->nullable();

            // last activity tracking
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('is_online')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
