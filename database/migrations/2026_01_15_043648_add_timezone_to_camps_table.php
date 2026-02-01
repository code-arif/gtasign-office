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
        Schema::table('camps', function (Blueprint $table) {
            // Add timezone column to store camp's physical location timezone
            $table->string('timezone', 50)->default('UTC')->after('longitude');

            // Add index for better query performance
            $table->index('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camps', function (Blueprint $table) {
            $table->dropIndex(['timezone']);
            $table->dropColumn('timezone');
        });
    }
};
