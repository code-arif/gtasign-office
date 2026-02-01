<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get all notifications for authenticated user
     * Supports filtering by type
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $type = $request->input('type'); // optional filter: announcement, camp, schedule, etc.

        $query = auth()->user()->notifications();

        // Filter by type if provided
        if ($type) {
            $notificationClass = $this->getNotificationClass($type);
            if ($notificationClass) {
                $query->where('type', $notificationClass);
            }
        }

        $notifications = $query->latest()->paginate($perPage);

        $formattedNotifications = $notifications->map(function ($notification) {
            return $this->formatNotification($notification);
        });

        $response = [
            'notifications' => $formattedNotifications,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'unread_count' => auth()->user()->unreadNotifications()->count()
        ];

        return $this->success('Notifications retrieved successfully', $response, 200);
    }

    /**
     * Get only unread notifications
     */
    public function unread(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $type = $request->input('type');

        $query = auth()->user()->unreadNotifications();

        if ($type) {
            $notificationClass = $this->getNotificationClass($type);
            if ($notificationClass) {
                $query->where('type', $notificationClass);
            }
        }

        $notifications = $query->latest()->paginate($perPage);

        $formattedNotifications = $notifications->map(function ($notification) {
            return $this->formatNotification($notification);
        });

        $response = [
            'notifications' => $formattedNotifications,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ];

        return $this->success('Unread notifications retrieved successfully', $response, 200);
    }

    /**
     * Get notification counts by type
     */
    public function counts()
    {
        $user = auth()->user();

        $counts = [
            'total_unread' => $user->unreadNotifications()->count(),
            'announcements' => $user->unreadNotifications()
                ->where('type', 'App\Notifications\AnnouncementNotification')
                ->count(),
            // Future notification types
            // 'camps' => $user->unreadNotifications()
            //     ->where('type', 'App\Notifications\CampNotification')
            //     ->count(),
            // 'schedules' => $user->unreadNotifications()
            //     ->where('type', 'App\Notifications\ScheduleNotification')
            //     ->count(),
        ];

        return $this->success('Notification counts retrieved', $counts, 200);
    }

    /**
     * Get single notification detail
     */
    public function show($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return $this->error([], 'Notification not found', 404);
        }

        // Mark as read when viewed
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        $formattedNotification = $this->formatNotification($notification);

        return $this->success('Notification retrieved successfully', $formattedNotification, 200);
    }

    /**
     * Mark specific notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return $this->error([], 'Notification not found', 404);
        }

        $notification->markAsRead();

        return $this->success('Marked as read', [], 200);
    }

    /**
     * Mark all notifications as read
     * Supports filtering by type
     */
    public function markAllAsRead(Request $request)
    {
        $type = $request->input('type');

        $query = auth()->user()->unreadNotifications();

        if ($type) {
            $notificationClass = $this->getNotificationClass($type);
            if ($notificationClass) {
                $query->where('type', $notificationClass);
            }
        }

        $updated = $query->update(['read_at' => now()]);

        return $this->success('Notifications marked as read', [
            'marked_count' => $updated
        ], 200);
    }

    /**
     * Delete a notification
     */
    public function destroy($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return $this->error([], 'Notification not found', 404);
        }

        $notification->delete();

        return $this->success('Notification deleted successfully', [], 200);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $deleted = auth()->user()
            ->readNotifications()
            ->delete();

        return $this->success('Read notifications cleared', [
            'deleted_count' => $deleted
        ], 200);
    }

    /**
     * Format notification based on its type
     */
    private function formatNotification($notification)
    {
        $data = $notification->data;
        $type = class_basename($notification->type);

        // Base structure
        $formatted = [
            'id' => $notification->id,
            'type' => $this->getNotificationType($notification->type),
            'is_read' => $notification->read_at !== null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];

        // Add type-specific data
        switch ($notification->type) {
            case 'App\Notifications\AnnouncementNotification':
                $formatted['data'] = [
                    'announcement_id' => $data['announcement_id'] ?? null,
                    'subject' => $data['subject'] ?? null,
                    'message' => $data['message'] ?? null,
                    'created_by' => $data['created_by'] ?? null,
                    'creator_name' => $data['creator_name'] ?? 'Director',
                    'sent_at' => $data['sent_at'] ?? null,
                ];
                break;

            // Future notification types
            // case 'App\Notifications\CampNotification':
            //     $formatted['data'] = [
            //         'camp_id' => $data['camp_id'] ?? null,
            //         'camp_name' => $data['camp_name'] ?? null,
            //         'action' => $data['action'] ?? null, // created, updated, cancelled
            //         'message' => $data['message'] ?? null,
            //     ];
            //     break;

            default:
                $formatted['data'] = $data;
                break;
        }

        return $formatted;
    }

    /**
     * Get notification class from type string
     */
    private function getNotificationClass($type)
    {
        $types = [
            'announcement' => 'App\Notifications\AnnouncementNotification',
            // 'camp' => 'App\Notifications\CampNotification',
            // 'schedule' => 'App\Notifications\ScheduleNotification',
            // 'evaluation' => 'App\Notifications\EvaluationNotification',
        ];

        return $types[$type] ?? null;
    }

    /**
     * Get user-friendly type name from notification class
     */
    private function getNotificationType($class)
    {
        $types = [
            'App\Notifications\AnnouncementNotification' => 'announcement',
            // 'App\Notifications\CampNotification' => 'camp',
            // 'App\Notifications\ScheduleNotification' => 'schedule',
            // 'App\Notifications\EvaluationNotification' => 'evaluation',
        ];

        return $types[$class] ?? 'general';
    }
}
