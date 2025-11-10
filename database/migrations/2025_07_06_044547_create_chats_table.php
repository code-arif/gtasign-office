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
        Schema::create('chats', function (Blueprint $table) {

            $table->id();                                                           // Unique message ID
            $table->unsignedBigInteger('sender_id');                                // Sender User ID
            $table->unsignedBigInteger('receiver_id');                              // Receiver User ID
            $table->unsignedBigInteger('room_id');                                  // Room ID (foreign key)
            $table->text('text')->nullable();                                       // Message Text
            $table->string('file')->nullable();                                     // File (if any, like image/video)
            $table->enum('status', ['sent', 'read', 'delivered'])->default('sent'); // Message status
            $table->timestamps();                                                   // Timestamps for created_at and updated_at
            $table->softDeletes();

            // Foreign keys referencing the Users and Rooms tables
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');

            // Index for quicker querying
            $table->index(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
