<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use libphonenumber\CountryCodeToRegionCodeMap;
use Propaganistas\LaravelPhone\Rules\Phone;

class VerifyRequest extends FormRequest
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
                (new Phone())->country(CountryCodeToRegionCodeMap::$countryCodeToRegionCodeMap[998]),
                Rule::exists('users', 'phone_number'),
            ],
            'code' => [
                'required',
                'integer',
                'min:100000',
                'max:999999',
            ],
        ];
    }
}
