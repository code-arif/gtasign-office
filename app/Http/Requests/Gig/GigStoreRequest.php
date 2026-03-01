<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

class GigStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Overview
            'title' => 'required|string|max:100',
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'nullable|integer|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',

            // Pricing
            'scope' => 'required|string|max:5000',
            'price' => 'required|numeric|min:5|max:999999',
            'delivery_days' => 'required|integer|min:1|max:365',

            // Requirements
            'system_questions' => 'nullable|array',
            'custom_questions' => 'nullable|array',

            // Gallery
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'documents' => 'nullable|array|max:3',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'images.required' => 'At least one gig image is required',
            'images.min' => 'At least one gig image is required',
            'images.max' => 'Maximum 5 images allowed',
            'price.min' => 'Minimum price is $5',
        ];
    }
}
