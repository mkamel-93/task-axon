<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryEnum;
use App\Enums\PhoneStateEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberIndexRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country' => 'nullable|string|max:255|'.Rule::in(CountryEnum::cases()),
            'state' => 'nullable|'.Rule::in(PhoneStateEnum::cases()),
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ];
    }
}
