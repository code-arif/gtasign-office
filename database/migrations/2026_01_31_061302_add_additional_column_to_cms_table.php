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
        Schema::table('c_m_s', function (Blueprint $table) {
            $table->string('layout')->nullable()->after('section');
            $table->json('blocks')->nullable()->after('metadata');
            $table->json('extra')->nullable()->after('blocks');
            $table->integer('order')->default(0)->after('is_display');
            $table->string('component')->nullable()->after('layout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_m_s', function (Blueprint $table) {
            $table->dropColumn(['layout', 'blocks', 'extra', 'order', 'component']);
        });
    }
};
