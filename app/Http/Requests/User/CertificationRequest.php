<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // return [
        //     'name' => 'required|string|max:255',
        //     'awarded_by' => 'required|string|max:255',
        //     'year' => 'required|digits:4|integer',
        //     'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
        // ];

        return [
            'certifications' => ['required', 'array', 'min:1'],

            'certifications.*.id' => ['nullable', 'exists:certifications,id'],

            'certifications.*.name' => ['required', 'string', 'max:255'],
            'certifications.*.awarded_by' => ['required', 'string', 'max:255'],
            'certifications.*.year' => ['required', 'digits:4', 'integer'],

            'certifications.*.file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
