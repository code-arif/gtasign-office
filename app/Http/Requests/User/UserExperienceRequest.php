<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // return [
        //     'skill_name' => 'required|string|max:255',
        //     'level' => 'required|string|max:255',
        // ];

        return [
            'skills' => ['required', 'array', 'min:1'],

            'skills.*.id' => ['nullable', 'exists:user_experiences,id'],

            'skills.*.skill_name' => ['required', 'string', 'max:255'],
            'skills.*.level' => ['required', 'string', 'max:255'],
        ];
    }
}
