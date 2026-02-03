<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

class GigGalleryRequest extends FormRequest
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
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB max

            'documents' => ['nullable', 'array', 'max:2'],
            'documents.*' => ['file', 'mimes:pdf', 'max:10240'], // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'images.max' => 'You can upload maximum 3 images',
            'images.*.image' => 'File must be an image',
            'images.*.mimes' => 'Image must be jpg, jpeg, png, or webp format',
            'images.*.max' => 'Each image must not exceed 5MB',

            'documents.max' => 'You can upload maximum 2 documents',
            'documents.*.mimes' => 'Document must be a PDF file',
            'documents.*.max' => 'Each document must not exceed 10MB',
        ];
    }
}
