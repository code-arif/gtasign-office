<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'subject',
        'message',
        'announcement_to',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(AnnouncementRecipient::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'announcement_recipients')
            ->withPivot('is_read', 'read_at')
            ->withTimestamps();
    }
}
