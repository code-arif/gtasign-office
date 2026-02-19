<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'has_active_order',
        'last_message_at',
    ];

    protected $casts = [
        'has_active_order' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * First user in the room (initiator)
     */
    public function firstUser()
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    /**
     * Second user in the room (responder)
     */
    public function secondUser()
    {
        return $this->belongsTo(User::class, 'second_user_id');
    }

    /**
     * All messages in this room
     */
    public function messages()
    {
        return $this->hasMany(Chat::class)->orderBy('created_at');
    }

    /**
     * Latest message in room (for inbox preview)
     */
    public function latestMessage()
    {
        return $this->hasOne(Chat::class)->latestOfMany();
    }

    /**
     * All orders in this room
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Active orders (in progress, QA pending, delivered)
     */
    public function activeOrders()
    {
        return $this->hasMany(Order::class)
            ->whereIn('status', ['active', 'qa_pending', 'delivered']);
    }

    /**
     * Custom offers in this room
     */
    public function customOffers()
    {
        return $this->hasMany(CustomOffer::class);
    }

    /**
     * Pending custom offers (waiting for response)
     */
    public function pendingOffers()
    {
        return $this->hasMany(CustomOffer::class)
            ->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get rooms for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('first_user_id', $userId)
            ->orWhere('second_user_id', $userId);
    }

    /**
     * Get rooms with active orders
     */
    public function scopeWithActiveOrders($query)
    {
        return $query->where('has_active_order', true);
    }

    /**
     * Get rooms ordered by recent activity
     */
    public function scopeRecentActivity($query)
    {
        return $query->whereNotNull('last_message_at')
            ->orderBy('last_message_at', 'desc');
    }

    /**
     * Get room between two specific users
     */
    public function scopeBetweenUsers($query, int $userId1, int $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('first_user_id', $userId1)
                ->where('second_user_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('first_user_id', $userId2)
                ->where('second_user_id', $userId1);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the other user in the room (not the given user)
     */
    // public function getOtherUser(int $userId)
    // {
    //     if ($this->first_user_id == $userId) {
    //         return $this->secondUser;
    //     }

    //     if ($this->second_user_id == $userId) {
    //         return $this->firstUser;
    //     }

    //     return null;
    // }

    public function getOtherUserAttribute()
    {
        $authId = auth('api')->id();

        if (!$authId) {
            return null;
        }

        return $this->first_user_id == $authId
            ? $this->secondUser
            : $this->firstUser;
    }


    /**
     * Check if user is part of this room
     */
    public function hasUser(int $userId): bool
    {
        return $this->first_user_id == $userId || $this->second_user_id == $userId;
    }

    /**
     * Check if room has any active orders
     */
    public function hasActiveOrder(): bool
    {
        return $this->has_active_order;
    }

    /**
     * Update last message timestamp
     */
    public function updateLastMessageTime()
    {
        return $this->update(['last_message_at' => now()]);
    }

    /**
     * Update room order status
     */
    public function updateOrderStatus(bool $hasOrder)
    {
        return $this->update(['has_active_order' => $hasOrder]);
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->messages()
            ->where('receiver_id', $userId)
            ->where('status', '!=', 'read')
            ->count();
    }

    /**
     * Get both users in the room
     */
    public function getParticipants(): array
    {
        return [
            'first_user' => $this->firstUser,
            'second_user' => $this->secondUser,
        ];
    }

    /**
     * Check if room has any pending offers
     */
    public function hasPendingOffers(): bool
    {
        return $this->pendingOffers()->exists();
    }

    /**
     * Get room statistics
     */
    public function getStats(): array
    {
        return [
            'total_messages' => $this->messages()->count(),
            'total_orders' => $this->orders()->count(),
            'active_orders' => $this->activeOrders()->count(),
            'completed_orders' => $this->orders()->where('status', 'completed')->count(),
            'total_offers' => $this->customOffers()->count(),
            'pending_offers' => $this->pendingOffers()->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Check if room is active (has recent messages)
     */
    public function getIsActiveAttribute(): bool
    {
        if (!$this->last_message_at) {
            return false;
        }

        // Consider active if message within last 30 days
        return $this->last_message_at->isAfter(now()->subDays(30));
    }

    /**
     * Get human-readable last activity time
     */
    public function getLastActivityAttribute(): ?string
    {
        return $this->last_message_at ? $this->last_message_at->diffForHumans() : null;
    }
}
