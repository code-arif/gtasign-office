<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'title',
        'scope',
        'price',
        'delivery_days',
        'system_questions',
        'custom_questions',
        'status',
        'rejection_reason',
        'published_at',
    ];

    protected $casts = [
        'system_questions' => 'array',
        'custom_questions' => 'array',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The freelancer who owns this gig.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primary category of the gig.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Optional sub-category.
     */
    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    /**
     * Images associated with the gig.
     */
    public function images()
    {
        return $this->hasMany(GigImage::class);
    }

    /**
     * Primary image for listing pages.
     */
    public function primaryImage()
    {
        return $this->hasOne(GigImage::class)->where('is_primary', true);
    }

    /**
     * Documents attached to the gig.
     */
    public function documents()
    {
        return $this->hasMany(GigDocument::class);
    }

    /**
     * Search tags for discovery and SEO.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }


    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }


    /**
     * Accessors
     */
    public function getCancellationRateAttribute(): string
    {
        if ($this->orders == 0) {
            return '0%';
        }

        $rate = ($this->cancellations / $this->orders) * 100;
        return round($rate) . '%';
    }

    /**
     * Helpers
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions');
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    public function incrementOrders(): void
    {
        $this->increment('orders');
    }

    public function incrementCancellations(): void
    {
        $this->increment('cancellations');
    }
}
