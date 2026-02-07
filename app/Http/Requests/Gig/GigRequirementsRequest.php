<?php

namespace App\Http\Requests\Gig;

use Illuminate\Foundation\Http\FormRequest;

class GigRequirementsRequest extends FormRequest
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
        // return [
        //     'system_questions' => ['nullable', 'array'],
        //     'system_questions.*.question' => ['required_with:system_questions', 'string', 'max:500'],
        //     'system_questions.*.type' => ['required_with:system_questions', 'string', 'in:text,textarea,select,radio,checkbox'],
        //     'system_questions.*.options' => ['nullable', 'array'],
        //     'system_questions.*.required' => ['nullable', 'boolean'],

        //     'custom_questions' => ['nullable', 'array'],
        //     'custom_questions.*.question' => ['required_with:custom_questions', 'string', 'max:500'],
        //     'custom_questions.*.type' => ['required_with:custom_questions', 'string', 'in:text,textarea,select,radio,checkbox,file'],
        //     'custom_questions.*.options' => ['nullable', 'array'],
        //     'custom_questions.*.required' => ['nullable', 'boolean'],
        // ];
        return [
            'system_questions' => ['nullable', 'array'],
            'system_questions.*.question' => ['required_with:system_questions', 'string', 'max:500'],
            'system_questions.*.answer' => ['required_with:system_questions', 'string'],

            'custom_questions' => ['nullable', 'array'],
            'custom_questions.*.question' => ['required_with:custom_questions', 'string', 'max:500'],
            'custom_questions.*.answer' => ['required_with:custom_questions', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'system_questions.*.question.max' => 'Question cannot exceed 500 characters',
            'system_questions.*.type.in' => 'Invalid question type',
            'custom_questions.*.question.max' => 'Question cannot exceed 500 characters',
            'custom_questions.*.type.in' => 'Invalid question type',
        ];
    }
}
