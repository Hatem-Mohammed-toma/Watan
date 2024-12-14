<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CountryRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "country_name" => "required|string|max:255",
            "city_name" => "required|string|max:255",
            "event_name" => "required|string|max:255",
            "date" => "required|date",
            "desc_event" => "required|string",
            "country_desc" => "required|string",
            "country_photo" => "nullable|array", // Validate as an array of files
            "country_photo.*" => "file|mimes:jpg,jpeg,png|max:10240", // Validate each file
            "event_photo" => "nullable|array", // Validate as an array of files
            "event_photo.*" => "file|mimes:jpg,jpeg,png|max:10240", // Validate each file
            "latitude" => "required|numeric|between:-90,90",  // Latitude validation
            "longitude" => "required|numeric|between:-180,180",  // Longitude validation
        ];
    }
    public function failedValidation(Validator $validator)

    {

        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Validation errors',
            'error'      => $validator->errors()
],400));
}
}

