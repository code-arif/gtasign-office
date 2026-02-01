<?php

namespace App\Http\Controllers\Api\Frontend;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AnnouncementNotification;

class AnnouncementController extends Controller
{
    use ApiResponse;

    /**
     * Make announcement and send notifications
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'announcement_to' => 'required|in:all,referees,evaluators,specific',
            'specific_user_ids' => 'required_if:announcement_to,specific|array'
        ]);

        DB::beginTransaction();

        try {
            // Create announcement
            $announcement = Announcement::create([
                'created_by' => auth()->id(),
                'subject' => $request->subject,
                'message' => $request->message,
                'announcement_to' => $request->announcement_to,
                'status' => 'sent',
                'sent_at' => now()
            ]);

            // Get recipients
            $recipients = $this->getRecipients($request->announcement_to, $request->specific_user_ids);

            // Send notifications to all recipients
            Notification::send($recipients, new AnnouncementNotification($announcement));

            DB::commit();

            return $this->success('Announcement sent successfully', [
                'announcement' => $announcement,
                'recipients_count' => count($recipients)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to send announcement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get recipients based on announcement type
     */
    private function getRecipients($announcementTo, $specificUserIds = [])
    {
        switch ($announcementTo) {
            case 'all':
                return User::all();

            case 'referees':
                return User::role('referee')->get();

            case 'evaluators':
                return User::role('evaluator')->get();

            case 'specific':
                return User::whereIn('id', $specificUserIds)->get();

            default:
                return collect([]);
        }
    }

    /**
     * Delete announcement - only creator/director can delete
     */
    public function destroy($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->error([], 'Announcement not found', 404);
        }

        // Check if user is creator or has director role
        if ($announcement->created_by !== auth()->id() && !auth()->user()->hasRole('director')) {
            return $this->error([], 'Unauthorized to delete this announcement', 403);
        }

        DB::beginTransaction();

        try {
            // Delete related notifications
            DB::table('notifications')
                ->where('type', 'App\Notifications\AnnouncementNotification')
                ->whereJsonContains('data->announcement_id', $announcement->id)
                ->delete();

            // Soft delete announcement
            $announcement->delete();

            DB::commit();

            return $this->success('Announcement deleted successfully', [], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to delete announcement: ' . $e->getMessage(), 500);
        }
    }
}
