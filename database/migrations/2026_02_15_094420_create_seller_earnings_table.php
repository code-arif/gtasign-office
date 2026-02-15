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
        Schema::create('seller_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained('withdrawal_requests')->nullOnDelete();

            $table->decimal('gross_amount', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('net_amount', 10, 2);

            $table->enum('status', [
                'pending',
                'clearing',
                'available',
                'withdrawn',
                'refunded'
            ])->default('pending');

            $table->timestamp('available_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();

            $table->timestamps();

            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_earnings');
    }
};
