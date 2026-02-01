<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Director\Models\Camp;
use App\Models\CampEvaluatorRegistration;

class CampEvaluatorRegistrationSeeder extends Seeder
{
    public function run()
    {
        $camps = Camp::pluck('id')->toArray();

        if (count($camps) < 3) {
            dd("You must have at least 3 camps to seed evaluator registrations.");
        }

        // Spatie Role Query
        $evaluators = User::role('evaluator', 'api')->get();
        $directors  = User::role('director', 'api')->pluck('id')->toArray();

        if ($evaluators->count() == 0) {
            dd("No evaluators found.");
        }

        $totalInserted = 0;

        foreach ($evaluators as $evaluator) {

            $assignedCampIds = collect($camps)->random(3); // each evaluator -> min 10 camps

            foreach ($assignedCampIds as $campId) {

                CampEvaluatorRegistration::updateOrCreate(
                    [
                        'camp_id'      => $campId,
                        'evaluator_id' => $evaluator->id,
                    ],
                    [
                        'status'                   => 'approved',
                        'registration_note'        => 'Auto approved seeder data',
                        'approved_at'              => now(),
                        'approved_by'              => $directors ? collect($directors)->random() : null,
                        'can_view_own_evaluations' => true,
                    ]
                );

                $totalInserted++;

                if ($totalInserted >= 50) {
                    break 2;
                }
            }
        }

        dump("$totalInserted evaluator camp registrations created successfully!");
    }
}
