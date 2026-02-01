<?php

namespace App\Models;

use Modules\Director\Models\Camp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampEvaluatorRegistration extends Model
{
    protected $fillable = [
        'camp_id',
        'evaluator_id',
        'status',
        'registration_note',
        'rejection_reason',
        'registered_at',
        'approved_at',
        'rejected_at',
        'approved_by',
        'can_view_own_evaluations',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForCamp($query, $campId)
    {
        return $query->where('camp_id', $campId);
    }

    public function scopeForEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $director): bool
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $director->id,
            'rejection_reason' => null,
        ]);

        return true;
    }

    public function reject(string $reason = null): bool
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Toggle visibility permission
     */
    public function toggleVisibility(): bool
    {
        $this->update([
            'can_view_own_evaluations' => !$this->can_view_own_evaluations,
        ]);

        return $this->can_view_own_evaluations;
    }

    /**
     * Check if evaluator can view their evaluations
     */
    public function canViewEvaluations(): bool
    {
        return $this->status === 'approved' && $this->can_view_own_evaluations;
    }
}
