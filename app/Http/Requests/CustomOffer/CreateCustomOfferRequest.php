<?php

namespace App\Http\Requests\CustomOffer;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gig_id' => 'nullable|exists:gigs,id',
            'client_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:5|max:10000',
            'delivery_days' => 'required|integer|min:1|max:90',
            'revisions' => 'nullable|integer|min:0|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Client is required',
            'client_id.exists' => 'Client not found',
            'price.min' => 'Minimum offer price is $5',
            'delivery_days.max' => 'Maximum delivery time is 90 days',
        ];
    }
}
