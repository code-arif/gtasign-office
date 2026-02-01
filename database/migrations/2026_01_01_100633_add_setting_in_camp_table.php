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
            $table->boolean('publish_ranking_for_evaluators')->default(true)->after('status');
            $table->boolean('hide_evaluator_name_from_referees')->default(false)->after('publish_ranking_for_evaluators');
            $table->boolean('hide_ranking_numbers_from_referees')->default(false)->after('hide_evaluator_name_from_referees');
            $table->boolean('publish_ranking_for_referees')->default(false)->after('hide_ranking_numbers_from_referees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camps', function (Blueprint $table) {
            $table->dropColumn([
                'publish_ranking_for_evaluators',
                'hide_evaluator_name_from_referees',
                'hide_ranking_numbers_from_referees',
                'publish_ranking_for_referees',
            ]);
        });
    }
};
