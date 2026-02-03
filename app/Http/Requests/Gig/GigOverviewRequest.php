<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

class GigOverviewRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search_tags' => ['nullable', 'array', 'max:5'],
            'search_tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Gig title is required',
            'title.max' => 'Gig title cannot exceed 100 characters',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category is invalid',
            'sub_category_id.exists' => 'Selected sub-category is invalid',
            'search_tags.max' => 'You can add maximum 5 search tags',
            'search_tags.*.max' => 'Each tag cannot exceed 50 characters',
        ];
    }
}
