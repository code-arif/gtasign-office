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
        Schema::create('user_security_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('identifier'); // email / phone
            $table->string('token_hash');
            $table->enum('type', [
                'email_verification',
                'password_reset',
                'login_otp',
                '2fa'
            ]);

            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_security_tokens');
    }
};
