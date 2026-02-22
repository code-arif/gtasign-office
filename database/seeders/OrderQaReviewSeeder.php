<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class OrderQaReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command->warn('OrderQaReviewSeeder skipped — not in local.');
            return;
        }

        // Admins act as QA reviewers
        $qaUsers = User::role('admin', 'web')->pluck('id');

        if ($qaUsers->isEmpty()) {
            $this->command->error('No QA reviewers found.');
            return;
        }

        // Only deliveries that entered QA stage
        $deliveries = DB::table('order_deliveries')
            ->whereIn('status', ['qa_approved', 'qa_rejected'])
            ->get();

        if ($deliveries->isEmpty()) {
            $this->command->warn('No QA-ready deliveries found.');
            return;
        }

        $reviews = [];

        foreach ($deliveries as $delivery) {

            $status = $delivery->status === 'qa_approved' ? 'approved': 'rejected';

            $submittedAt = Carbon::parse($delivery->submitted_at);

            $reviews[] = [
                'order_id' => $delivery->order_id,
                'delivery_id' => $delivery->id,
                'reviewed_by' => $qaUsers->random(),

                'status' => $status,

                'feedback' => $status === 'approved'
                    ? 'Delivery meets all QA requirements.'
                    : 'Layout, typography and responsiveness issues found.',

                'issues' => $status === 'rejected'
                    ? json_encode(['alignment_issue', 'font_mismatch'])
                    : null,

                'submitted_at' => $submittedAt,
                'reviewed_at' => $submittedAt->copy()->addHours(rand(4, 12)),

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('order_qa_reviews')->insert($reviews);

        $this->command->info('QA Reviews Seeded Successfully.');
    }
}
