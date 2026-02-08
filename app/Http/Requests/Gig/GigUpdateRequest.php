<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

class GigUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:100',
            'category_id' => 'sometimes|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',

            'scope' => 'sometimes|string|max:5000',
            'price' => 'sometimes|numeric|min:5|max:999999',
            'delivery_days' => 'sometimes|integer|min:1|max:365',

            'system_questions' => 'nullable|array',
            'custom_questions' => 'nullable|array',

            // Allow adding new images/documents
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'documents' => 'nullable|array|max:3',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
        ];
    }
}
