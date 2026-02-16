<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,zip,doc,docx,txt|max:10240', // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'files.max' => 'Maximum 5 files allowed',
            'files.*.max' => 'Each file must not exceed 10MB',
        ];
    }
}
