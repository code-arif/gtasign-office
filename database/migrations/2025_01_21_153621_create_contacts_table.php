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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // User Name
            $table->string('company_name')->nullable(); // Company Name
            $table->string('phone', 30)->nullable(); // Phone Number

            $table->string('email'); // Email

            $table->string('subject'); // Dropdown Subject (Support / Sales / etc.)
            $table->longText('message'); // User Query

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
