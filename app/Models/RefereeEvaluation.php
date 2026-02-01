<?php

namespace App\Models;

use Modules\Director\Models\Camp;
use Modules\Director\Models\GameSlot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefereeEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'referee_id',
        'evaluator_id',
        'camp_id',
        'game_slot_id',
        'call_accuracy',
        'communication_skills',
        'consistency_of_calls',
        'court_position_mechanics',
        'fitness_mobility',
        'game_awareness',
        'total_score',
        'average_score',
        'private_comments',
        'referee_feedback',
        'recommended_highest_level',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'total_score' => 'decimal:2',
        'average_score' => 'decimal:2',
    ];

    protected $hidden = [
        'private_comments', // Hidden by default, shown conditionally
    ];

    // Relationships
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function gameSlot(): BelongsTo
    {
        return $this->belongsTo(GameSlot::class);
    }

    // Auto calculate total score and average score before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            // Calculate total score
            $evaluation->total_score = collect([
                $evaluation->call_accuracy,
                $evaluation->communication_skills,
                $evaluation->consistency_of_calls,
                $evaluation->court_position_mechanics,
                $evaluation->fitness_mobility,
                $evaluation->game_awareness,
            ])->filter()->sum();

            // Calculate average score
            if ($evaluation->total_score > 0) {
                $evaluation->average_score = round($evaluation->total_score / 6, 2);
            } else {
                $evaluation->average_score = 0;
            }
        });
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeForReferee($query, $refereeId)
    {
        return $query->where('referee_id', $refereeId);
    }

    public function scopeForCamp($query, $campId)
    {
        return $query->where('camp_id', $campId);
    }

    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Permission methods
    public function canBeEditedBy(User $user): bool
    {
        // Director can edit any evaluation in their camps
        if ($user->hasRole('director')) {
            $camp = $this->camp;
            return $camp && $camp->director_id === $user->id;
        }

        // Evaluator can edit their own evaluations
        if ($user->hasRole('evaluator')) {
            return $this->evaluator_id === $user->id;
        }

        return false;
    }

    public function canBeViewedBy(User $user): bool
    {
        // Referee can view their own submitted evaluations
        if ($user->hasRole('referee') && $this->referee_id === $user->id && $this->status === 'submitted') {
            return true;
        }

        // Director can view evaluations in their camps
        if ($user->hasRole('director')) {
            $camp = $this->camp;
            return $camp && $camp->director_id === $user->id;
        }

        // Evaluator can view their own evaluations
        if ($user->hasRole('evaluator') && $this->evaluator_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if evaluator is registered and approved for this camp
     */
    public static function canEvaluateInCamp(User $evaluator, $campId): bool
    {
        // Directors can always evaluate in their own camps
        if ($evaluator->hasRole('director')) {
            $camp = Camp::find($campId);
            return $camp && $camp->director_id === $evaluator->id;
        }

        // Evaluators must be registered and approved
        if ($evaluator->hasRole('evaluator')) {
            return CampEvaluatorRegistration::where('camp_id', $campId)
                ->where('evaluator_id', $evaluator->id)
                ->where('status', 'approved')
                ->exists();
        }

        return false;
    }

    /**
     * recomanded lavel relation
     */
    public function recommendedLevels()
    {
        return $this->hasMany(RecommendedLevel::class, 'evaluation_id');
    }
}
