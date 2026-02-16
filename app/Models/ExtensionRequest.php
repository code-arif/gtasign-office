<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtensionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'requested_by',
        'room_id',
        'additional_days',
        'reason',
        'status',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($extension) {
            if (empty($extension->requested_at)) {
                $extension->requested_at = now();
            }
        });
    }
}
