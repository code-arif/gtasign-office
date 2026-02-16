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
            'action' => ['required', Rule::in(['accept', 'reject'])],
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ];
    }
}
