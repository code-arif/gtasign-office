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
            'message' => 'nullable|string|max:4000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,zip,doc,docx,txt|max:102400', // 100MB
        ];
    }

    public function messages(): array
    {
        return [
            'files.max' => 'Maximum 10 files allowed',
            'files.*.max' => 'Each file must not exceed 10MB',
        ];
    }
}
