<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderQaReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_id',
        'reviewed_by',
        'status',
        'feedback',
        'issues',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'issues' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery()
    {
        return $this->belongsTo(OrderDelivery::class, 'delivery_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }
}
