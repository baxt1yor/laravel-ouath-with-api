<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use libphonenumber\CountryCodeToRegionCodeMap;
use Propaganistas\LaravelPhone\Rules\Phone;

class RegisterRequest extends FormRequest
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
            'first_name' => [
                'string',
                'required',
                'max:255',
            ],
            'last_name' => [
                'string',
                'required',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                app(Phone::class)->country(CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap[998]),
                Rule::unique('users', 'phone_number'),
            ],
            'password' => [
                'required',
                'string',
                Password::defaults(),
                'confirmed',
            ],
            'password_confirmation' => [
                'required',
                'string',
                Password::defaults(),
            ],
        ];
    }
}
