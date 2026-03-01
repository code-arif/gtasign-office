<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WithdrawalRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'withdrawal_number',
        'amount',
        'fee',
        'net_amount',
        'stripe_account_id',
        'stripe_transfer_id',
        'status',
        'failure_reason',
        'notes',
        'requested_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function earnings()
    {
        return $this->hasMany(SellerEarnings::class, 'withdrawal_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawal) {
            if (empty($withdrawal->withdrawal_number)) {
                $withdrawal->withdrawal_number = self::generateWithdrawalNumber();
            }

            if (empty($withdrawal->requested_at)) {
                $withdrawal->requested_at = now();
            }
        });
    }

    public static function generateWithdrawalNumber()
    {
        do {
            $number = 'WD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (self::where('withdrawal_number', $number)->exists());

        return $number;
    }
}
