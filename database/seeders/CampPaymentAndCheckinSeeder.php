<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CampPaymentAndCheckinSeeder extends Seeder
{
    public function run(): void
    {
        $campIds = DB::table('camps')->pluck('id')->toArray();

        // referee role_id = 4 (based on your previous seeder)
        $refereeIds = DB::table('model_has_roles')
            ->where('role_id', 4)
            ->pluck('model_id')
            ->toArray();

        if (empty($campIds) || empty($refereeIds)) {
            return;
        }

        foreach ($campIds as $campId) {

            // per camp 10–15 referees
            $selectedReferees = collect($refereeIds)
                ->shuffle()
                ->take(rand(30, 60));

            foreach ($selectedReferees as $refereeId) {

                $amount = rand(100, 500);
                $now = now();

                /* ==========================
                 * Payment Attempt
                 * ========================== */
                $paymentAttemptId = DB::table('camp_payment_attempts')->insertGetId([
                    'camp_id' => $campId,
                    'referee_id' => $refereeId,
                    'stripe_session_id' => 'cs_test_' . Str::uuid(),
                    'amount' => $amount,
                    'status' => 'completed',
                    'expires_at' => $now->copy()->addMinutes(30),
                    'completed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                /* ==========================
                 * Payment
                 * ========================== */
                $paymentId = DB::table('camp_payments')->insertGetId([
                    'camp_id' => $campId,
                    'referee_id' => $refereeId,
                    'payment_attempt_id' => $paymentAttemptId,
                    'stripe_payment_intent_id' => 'pi_' . Str::uuid(),
                    'stripe_session_id' => 'cs_test_' . Str::uuid(),
                    'amount' => $amount,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                    'paid_at' => $now,
                    'metadata' => json_encode([
                        'source' => 'seed',
                        'env' => 'local',
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                /* ==========================
                 * Registration / Check-in
                 * ========================== */

                $checkedIn = rand(1, 100) <= 60; // 60% already checked-in

                DB::table('camp_referee_checkins')->insert([
                    'camp_id' => $campId,
                    'referee_id' => $refereeId,
                    'payment_id' => $paymentId,
                    'registration_status' => $checkedIn ? 'checked_in' : 'registered',
                    'registered_at' => $now->copy()->subDays(rand(1, 5)),
                    'checked_in_at' => $checkedIn ? $now->copy()->subHours(rand(1, 5)) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
