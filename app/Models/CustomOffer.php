<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'gig_id',
        'expert_id',
        'client_id',
        'room_id',
        'title',
        'description',
        'price',
        'delivery_days',
        'revisions',
        'status',
        'expires_at',
        'accepted_at',
        'rejected_at',
        'order_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast() && $this->status === 'pending';
    }

    public function canAccept()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($offer) {
            if (empty($offer->expires_at)) {
                $offer->expires_at = now()->addDays(3);
            }
        });
    }
}
