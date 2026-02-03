<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country'           => 'nullable|string|max:150',
            'institution_name'  => 'required|string|max:255',
            'degree'            => 'required|string|max:100',
            'major'             => 'nullable|string|max:150',
            'graduation_year'   => 'nullable|digits:4|integer|min:1950|max:' . date('Y'),
        ];
    }
}
