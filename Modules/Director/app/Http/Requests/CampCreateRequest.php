<?php

namespace Modules\Director\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampCreateRequest extends FormRequest
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
            'sports_type_id'   => 'required|exists:sports_types,id',
            'camp_name'        => 'required|string|max:255',
            'sports_type_name' => 'nullable|string|max:255',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'location'         => 'required|string|max:255',
            'camp_details'     => 'nullable|string',
            'camp_logo'        => 'nullable|file|image|max:2048',
            'price'            => 'required|numeric|min:0',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'timezone' => 'nullable|string|timezone', // Optional, auto-detected from coordinates
        ];
    }


    public function messages(): array
    {
        return [
            'sports_type_id.required' => 'Sports type is required.',
            'sports_type_id.exists' => 'Invalid sports type selected.',
            'camp_name.required' => 'Camp name is required.',
            'location.required' => 'Camp location is required.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'timezone.timezone' => 'Invalid timezone format.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or a future date.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be equal to or after start date.',
            'price.required' => 'Price is required.',
            'price.min' => 'Price must be a positive number.',
            'camp_logo.image' => 'Camp logo must be an image.',
            'camp_logo.max' => 'Camp logo size must not exceed 2MB.',
        ];
    }
}
