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
        Schema::create('gig_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();
            $table->string('path'); // PDF or document path

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gig_documents');
    }
};
