<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewed_user_id',
        'gig_id',
        'rating',
        'communication_rating',
        'service_rating',
        'delivery_rating',
        'review',
        'seller_reply',
        'is_public',
        'replied_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function getAverageRatingAttribute()
    {
        $ratings = array_filter([
            $this->communication_rating,
            $this->service_rating,
            $this->delivery_rating,
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : $this->rating;
    }
}
