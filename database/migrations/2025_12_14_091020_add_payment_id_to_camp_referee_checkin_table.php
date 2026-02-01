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
        // Add payment_id to camp_referee_checkins table
        Schema::table('camp_referee_checkins', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->constrained('camp_payments')->onDelete('set null');
             // Add new columns
            $table->enum('registration_status', ['registered', 'checked_in'])->default('registered')->after('referee_id');
            $table->timestamp('registered_at')->nullable()->after('registration_status');
            $table->timestamp('checked_in_at')->nullable()->change();
            $table->string('checked_in_by')->nullable(); // 'self' or 'director'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camp_referee_checkins', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
            $table->dropColumn(['registration_status', 'registered_at']);
        });
    }
};
