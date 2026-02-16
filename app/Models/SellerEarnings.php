<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerEarnings extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'order_id',
        'gross_amount',
        'platform_fee',
        'net_amount',
        'status',
        'withdrawal_id',
        'available_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'available_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(WithdrawalRequest::class, 'withdrawal_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeClearing($query)
    {
        return $query->where('status', 'clearing');
    }

    public function scopeForSeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
