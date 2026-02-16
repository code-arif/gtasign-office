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
            // come form UI
            'quantity' => 'required|integer|min:1|max:100',

            // optional extras
            'extras' => 'nullable|array',
            'extras.fast_delivery' => 'nullable|boolean',
        ];
    }
}
