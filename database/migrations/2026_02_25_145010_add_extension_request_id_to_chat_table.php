<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->foreignId('extension_request_id')
                ->nullable()
                ->after('delivery_id')
                ->constrained('extension_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['extension_request_id']);
            $table->dropColumn('extension_request_id');
        });
    }
};
