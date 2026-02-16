<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendInboxMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required_without:file|nullable|string|max:5000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required_without' => 'Message or file is required',
        ];
    }
}
