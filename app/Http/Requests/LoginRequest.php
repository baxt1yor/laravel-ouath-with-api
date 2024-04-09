<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use libphonenumber\CountryCodeToRegionCodeMap;
use Propaganistas\LaravelPhone\Rules\Phone;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                app(Phone::class)->country(CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap[998]),
                Rule::exists('users', 'phone_number'),
            ],
            'password' => ['required'],
        ];
    }
}
