<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_id',
        'text',
        'file',
        'type',
        'order_id',
        'custom_offer_id',
        'delivery_id',
        'metadata',
        'status',
        'extension_request_id'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Message sender
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Message receiver
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Room this message belongs to
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Related order (if message is order-related)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Related custom offer (if message is offer-related)
     */
    public function customOffer()
    {
        return $this->belongsTo(CustomOffer::class);
    }

    /**
     * Related delivery (if message is delivery-related)
     */
    public function delivery()
    {
        return $this->belongsTo(OrderDelivery::class, 'delivery_id');
    }

    /**
     * Relation with extenstion request
     */
    public function extensionRequest(): BelongsTo
    {
        return $this->belongsTo(ExtensionRequest::class, 'extension_request_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get messages in a specific room
     */
    public function scopeInRoom($query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Get messages sent by a user
     */
    public function scopeFromSender($query, int $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Get messages received by a user
     */
    public function scopeToReceiver($query, int $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    /**
     * Get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('status', '!=', 'read');
    }

    /**
     * Get messages between two users
     */
    public function scopeBetweenUsers($query, int $userId1, int $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)
                ->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)
                ->where('receiver_id', $userId1);
        });
    }

    /**
     * Get messages of specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get text messages only
     */
    public function scopeTextOnly($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Get system messages
     */
    public function scopeSystemMessages($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Get order-related messages
     */
    public function scopeOrderRelated($query)
    {
        return $query->whereNotNull('order_id');
    }

    /**
     * Get offer-related messages
     */
    public function scopeOfferRelated($query)
    {
        return $query->whereNotNull('custom_offer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Type Check Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if message is a text message
     */
    public function isTextMessage(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if message is a custom offer
     */
    public function isCustomOffer(): bool
    {
        return $this->type === 'custom_offer';
    }

    /**
     * Check if message is offer accepted
     */
    public function isOfferAccepted(): bool
    {
        return $this->type === 'offer_accepted';
    }

    /**
     * Check if message is offer rejected
     */
    public function isOfferRejected(): bool
    {
        return $this->type === 'offer_rejected';
    }

    /**
     * Check if message is order placed
     */
    public function isOrderPlaced(): bool
    {
        return $this->type === 'order_placed';
    }

    /**
     * Check if message is delivery-related
     */
    public function isDelivery(): bool
    {
        return in_array($this->type, [
            'delivery_submitted',
            'delivery_approved',
            'delivery_rejected',
            'delivery_sent'
        ]);
    }

    /**
     * Check if message is extension request
     */
    public function isExtensionRequest(): bool
    {
        return $this->type === 'extension_request';
    }

    /**
     * Check if message is revision request
     */
    public function isRevisionRequest(): bool
    {
        return $this->type === 'revision_request';
    }

    /**
     * Check if message is order completed
     */
    public function isOrderCompleted(): bool
    {
        return $this->type === 'order_completed';
    }

    /**
     * Check if message is system message
     */
    public function isSystemMessage(): bool
    {
        return $this->type === 'system';
    }

    /*
    |--------------------------------------------------------------------------
    | Status Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if message is unread
     */
    public function isUnread(): bool
    {
        return $this->status !== 'read';
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): bool
    {
        if ($this->status !== 'read') {
            return $this->update(['status' => 'read']);
        }

        return true;
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered(): bool
    {
        if ($this->status === 'sent') {
            return $this->update(['status' => 'delivered']);
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user is the sender
     */
    public function isSentBy(int $userId): bool
    {
        return $this->sender_id === $userId;
    }

    /**
     * Check if user is the receiver
     */
    public function isReceivedBy(int $userId): bool
    {
        return $this->receiver_id === $userId;
    }

    /**
     * Check if message has attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->file);
    }

    /**
     * Get file URL
     */
    public function getFileUrl(): ?string
    {
        if (!$this->file) {
            return null;
        }

        return Storage::disk('public')->url($this->file);
    }

    /**
     * Get file name
     */
    public function getFileName(): ?string
    {
        if (!$this->file) {
            return null;
        }

        return basename($this->file);
    }

    /**
     * Check if message is actionable (requires user action)
     */
    public function isActionable(): bool
    {
        return in_array($this->type, [
            'custom_offer',
            'extension_request',
            'delivery_submitted',
        ]);
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabel(): string
    {
        $labels = [
            'text' => 'Message',
            'custom_offer' => 'Custom Offer',
            'offer_accepted' => 'Offer Accepted',
            'offer_rejected' => 'Offer Rejected',
            'order_placed' => 'Order Placed',
            'delivery_submitted' => 'Delivery Submitted',
            'delivery_approved' => 'Delivery Approved',
            'delivery_rejected' => 'Delivery Rejected',
            'delivery_sent' => 'Delivery Sent',
            'extension_request' => 'Extension Request',
            'extension_approved' => 'Extension Approved',
            'extension_rejected' => 'Extension Rejected',
            'revision_request' => 'Revision Request',
            'order_completed' => 'Order Completed',
            'order_cancelled' => 'Order Cancelled',
            'system' => 'System Message',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('h:i A');
    }

    /**
     * Get human-readable time
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get short preview text (for notifications)
     */
    public function getPreviewAttribute(): string
    {
        if ($this->text) {
            return Str::limit($this->text, 50);
        }

        if ($this->hasAttachment()) {
            return '📎 Attachment: ' . $this->getFileName();
        }

        return $this->getTypeLabel();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        // Auto-delete file when message is deleted
        static::deleting(function ($message) {
            if ($message->file && Storage::disk('public')->exists($message->file)) {
                Storage::disk('public')->delete($message->file);
            }
        });

        // Update room's last_message_at when message is created
        static::created(function ($message) {
            if ($message->room) {
                $message->room->update(['last_message_at' => now()]);
            }
        });
    }
}
