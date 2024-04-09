<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use libphonenumber\CountryCodeToRegionCodeMap;
use Propaganistas\LaravelPhone\Rules\Phone;

class ResendRequest extends FormRequest
{
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
                (new Phone())->country(CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap[998]),
                Rule::exists('users', 'phone_number'),
            ],
        ];
    }
}
