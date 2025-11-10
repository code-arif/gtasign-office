<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'user', 'seller'])->default('user');

            $table->string('house')->nullable();
            $table->string('road')->nullable();
            $table->string('city')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->string('otp')->nullable();
            $table->boolean('is_otp_verified')->default(false);
            $table->timestamp('otp_expires_at')->nullable();

            $table->string('avatar')->nullable();
            $table->string('gender')->nullable();

            $table->foreignId('plan_id')->nullable();
            $table->string('subscription_status')->nullable();
            $table->enum('payment_way', ['web', 'app', 'other'])->default('other');
            $table->timestamp('last_activity_at')->nullable();

            $table->string('google_id')->nullable();
            $table->boolean('is_agree_termsconditions')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_social_logged')->default(false);
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
