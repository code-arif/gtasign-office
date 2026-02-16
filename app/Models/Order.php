<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'gig_id',
        'custom_offer_id',
        'buyer_id',
        'seller_id',
        'room_id',
        'price',
        'platform_fee',
        'seller_earnings',
        'delivery_days',
        'expected_delivery_at',
        'max_revisions',
        'revision_count',
        'requirements',
        'status',
        'payment_method',
        'payment_intent_id',
        'paid_at',
        'funds_in_escrow',
        'escrow_released_at',
        'cancellation_reason',
        'cancelled_by',
        'auto_complete_enabled',
        'auto_complete_at',
        'started_at',
        'qa_submitted_at',
        'qa_approved_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'requirements' => 'string',
        'price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'auto_complete_enabled' => 'boolean',
        'funds_in_escrow' => 'boolean',
        'expected_delivery_at' => 'datetime',
        'paid_at' => 'datetime',
        'escrow_released_at' => 'datetime',
        'auto_complete_at' => 'datetime',
        'started_at' => 'datetime',
        'qa_submitted_at' => 'datetime',
        'qa_approved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function customOffer()
    {
        return $this->belongsTo(CustomOffer::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class)->orderBy('delivery_number');
    }

    public function latestDelivery()
    {
        return $this->hasOne(OrderDelivery::class)->latestOfMany();
    }

    public function qaReviews()
    {
        return $this->hasMany(OrderQaReview::class);
    }

    public function latestQaReview()
    {
        return $this->hasOne(OrderQaReview::class)->latestOfMany();
    }

    public function extensionRequests()
    {
        return $this->hasMany(ExtensionRequest::class);
    }

    public function messages()
    {
        return $this->hasMany(Chat::class)->orderBy('created_at');
    }

    public function review()
    {
        return $this->hasOne(OrderReview::class);
    }

    public function earning()
    {
        return $this->hasOne(SellerEarnings::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeQaPending($query)
    {
        return $query->where('status', 'qa_pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInEscrow($query)
    {
        return $query->where('funds_in_escrow', true)
                     ->whereNull('escrow_released_at');
    }

    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeForSeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeInRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    // Status Helpers
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isQaPending()
    {
        return $this->status === 'qa_pending';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canSubmitToQa()
    {
        return in_array($this->status, ['active', 'qa_rejected']);
    }

    public function canRequestRevision()
    {
        return $this->status === 'delivered'
               && $this->revision_count < $this->max_revisions;
    }

    public function canAccept()
    {
        return $this->status === 'delivered';
    }

    public function canRequestExtension()
    {
        return $this->status === 'active' && !$this->isLate();
    }

    public function isLate()
    {
        return $this->status === 'active'
               && $this->expected_delivery_at
               && $this->expected_delivery_at->isPast();
    }

    public function daysUntilDelivery()
    {
        if (!$this->expected_delivery_at) {
            return null;
        }

        return now()->diffInDays($this->expected_delivery_at, false);
    }

    public function isOwnedByBuyer($userId)
    {
        return $this->buyer_id == $userId;
    }

    public function isOwnedBySeller($userId)
    {
        return $this->seller_id == $userId;
    }

    public function isEscrowReleased()
    {
        return $this->funds_in_escrow && $this->escrow_released_at !== null;
    }

    public function canReleaseEscrow()
    {
        return $this->isCompleted()
               && $this->funds_in_escrow
               && $this->completed_at
               && $this->completed_at->addDays(14)->isPast();
    }

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber()
    {
        do {
            $number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }
}
