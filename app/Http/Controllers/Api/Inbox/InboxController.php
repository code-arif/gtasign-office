<?php

namespace App\Http\Controllers\Api\Inbox;


use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendInboxMessageRequest;
use App\Http\Resources\Inbox\InboxMessageResource;
use App\Http\Resources\Inbox\RoomResource;
use App\Services\Gig\InboxService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InboxController extends Controller
{
    use ApiResponse;

    protected $inboxService;

    public function __construct(InboxService $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Get user's inbox list with recent conversations
     * GET v1/api/inbox
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            $search = $request->input('search');
            $perPage = $request->input('per_page', 20);

            // Get inbox rooms
            $rooms = $this->inboxService->getInboxList($user->id, $search, $perPage);

            return $this->success(
                'Inbox retrieved successfully',
                [
                    'rooms' => RoomResource::collection($rooms),
                    'pagination' => [
                        'total' => $rooms->total(),
                        'per_page' => $rooms->perPage(),
                        'current_page' => $rooms->currentPage(),
                        'last_page' => $rooms->lastPage(),
                    ]
                ]
            );
        } catch (Exception $e) {
            Log::error('Inbox index error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve inbox',
                500
            );
        }
    }

    /**
     * Get conversation with another user
     * GET v1/api/inbox/conversation/{roomId}
     */
    public function conversation(Request $request, int $roomId)
    {
        try {
            $user = auth('api')->user();
            $perPage = $request->input('per_page', 50);

            // Get conversation
            $conversation = $this->inboxService->getConversation($roomId, $user->id, $perPage);

            $messages = $conversation['messages'];

            return $this->success(
                'Conversation retrieved successfully',
                [
                    'room' => new RoomResource($conversation['room']),
                    // 'other_user' => [
                    //     'id' => $conversation['other_user']->id,
                    //     'name' => $conversation['other_user']->profile?->first_name . ' ' . $conversation['other_user']->profile?->last_name,
                    //     'username' => $conversation['other_user']->profile?->username,
                    //     'avatar' => $conversation['other_user']->profile?->avatar ? asset('storage/' . $conversation['other_user']->profile?->avatar) : asset('default/profile.jpg'),
                    // ],
                    // 'has_active_order' => $conversation['has_active_order'],
                    'messages' => InboxMessageResource::collection($messages),
                    'pagination' => [
                        'total' => $messages->total(),
                        'per_page' => $messages->perPage(),
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                    ]
                ]
            );
        } catch (Exception $e) {
            Log::error('Conversation error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Room not found' ? 404 : ($e->getMessage() === 'Unauthorized access to this room' ? 403 : 500)
            );
        }
    }

    /**
     * Send text message
     * POST /api/inbox/send/{receiverId}
     */
    public function sendMessage(SendInboxMessageRequest $request, int $receiverId)
    {
        try {
            $user = auth('api')->user();

            // Send message
            $message = $this->inboxService->sendMessage(
                $user->id,
                $receiverId,
                $request->validated()
            );

            return $this->success(
                'Message sent successfully',
                ['message' => new InboxMessageResource($message)]
            );

        } catch (Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Receiver not found' ? 404 : ($e->getMessage() === 'Cannot message yourself' ? 400 : 500)
            );
        }
    }

    /**
     * Start conversation with user
     * POST /api/inbox/start/{userId}
     */
    public function startConversation(int $userId)
    {
        try {
            $user = auth('api')->user();

            // Start conversation
            $data = $this->inboxService->startConversation($user->id, $userId);

            return $this->success(
                'Conversation started successfully',
                [
                    'room' => new RoomResource($data['room']),
                    'other_user' => [
                        'id' => $data['other_user']->id,
                        'name' => $data['other_user']->profile->full_name,
                        'username' => $data['other_user']->profile->username,
                        'avatar' => $data['other_user']->profile->avatar_url,
                    ],
                ]
            );
        } catch (Exception $e) {
            Log::error('Start conversation error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'User not found' ? 404 : ($e->getMessage() === 'Cannot start conversation with yourself' ? 400 : 500)
            );
        }
    }

    /**
     * Mark messages as read in a room
     * POST /api/inbox/{roomId}/mark-read
     */
    public function markAsRead(int $roomId)
    {
        try {
            $count = $this->inboxService->markAsRead($roomId, auth('api')->id());

            return $this->success(
                'Messages marked as read',
                ['marked_count' => $count]
            );
        } catch (Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to mark messages as read',
                500
            );
        }
    }
}
