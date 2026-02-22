<?php

namespace App\Http\Requests\CustomOffer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondToOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'nullable|string|max:500',
        ];
    }
}
