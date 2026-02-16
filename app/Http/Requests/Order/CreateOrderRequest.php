<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gig_id' => 'required_without:custom_offer_id|nullable|exists:gigs,id',
            'custom_offer_id' => 'required_without:gig_id|nullable|exists:custom_offers,id',
            'requirements' => 'nullable|array',
            'requirements.*.question' => 'required|string',
            'requirements.*.answer' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'gig_id.required_without' => 'Either gig or custom offer is required',
            'custom_offer_id.required_without' => 'Either gig or custom offer is required',
        ];
    }
}
