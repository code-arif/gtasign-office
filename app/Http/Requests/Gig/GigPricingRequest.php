<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class GigPricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'scope' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:5', 'max:999999.99'],
            'delivery_days' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'scope.max' => 'Scope cannot exceed 1000 characters',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be at least $5',
            'price.max' => 'Price cannot exceed $999,999.99',
            'delivery_days.required' => 'Delivery timeline is required',
            'delivery_days.in' => 'Delivery days must be 1, 2, 3, 4, or 5 days',
        ];
    }
}
