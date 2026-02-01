<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefereeEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'referee_id' => 'required|exists:users,id',
            'camp_id' => 'required|exists:camps,id',
            'game_slot_id' => 'nullable|exists:game_slots,id',

            // All criteria are optional but must be between 1-10 if provided
            'call_accuracy' => 'nullable|integer|min:1|max:10',
            'communication_skills' => 'nullable|integer|min:1|max:10',
            'consistency_of_calls' => 'nullable|integer|min:1|max:10',
            'court_position_mechanics' => 'nullable|integer|min:1|max:10',
            'fitness_mobility' => 'nullable|integer|min:1|max:10',
            'game_awareness' => 'nullable|integer|min:1|max:10',

            'private_comments' => 'nullable|string|max:5000',
            'referee_feedback' => 'nullable|string|max:5000',

            'recommended_level' => ['nullable', Rule::in([
                'NCAA D1',
                'NCAA D2',
                'NAIA',
                'JUCO',
                'HS',
                'JH/ELEM'
            ])],

            'status' => 'nullable|in:draft,submitted',
        ];
    }

    public function messages(): array
    {
        return [
            'referee_id.required' => 'Referee selection is required.',
            'referee_id.exists' => 'Selected referee does not exist.',
            'camp_id.required' => 'Camp selection is required.',
            'camp_id.exists' => 'Selected camp does not exist.',
            'game_slot_id.exists' => 'Selected game slot does not exist.',

            'call_accuracy.min' => 'Call accuracy must be at least 1.',
            'call_accuracy.max' => 'Call accuracy cannot exceed 10.',
            'communication_skills.min' => 'Communication skills must be at least 1.',
            'communication_skills.max' => 'Communication skills cannot exceed 10.',
            'consistency_of_calls.min' => 'Consistency of calls must be at least 1.',
            'consistency_of_calls.max' => 'Consistency of calls cannot exceed 10.',
            'court_position_mechanics.min' => 'Court position/mechanics must be at least 1.',
            'court_position_mechanics.max' => 'Court position/mechanics cannot exceed 10.',
            'fitness_mobility.min' => 'Fitness/mobility must be at least 1.',
            'fitness_mobility.max' => 'Fitness/mobility cannot exceed 10.',
            'game_awareness.min' => 'Game awareness must be at least 1.',
            'game_awareness.max' => 'Game awareness cannot exceed 10.',

            'status.in' => 'Status must be either draft or submitted.',
        ];
    }
}
