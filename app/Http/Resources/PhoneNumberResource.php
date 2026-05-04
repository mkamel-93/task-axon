<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CountryEnum;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Enums\PhoneStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class PhoneNumberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $countryCode = Str::between($this->resource->phone, '(', ')');
        $country = CountryEnum::tryFromCode($countryCode);

        $isValid = $country && preg_match($country->regex(), $this->resource->phone);

        return [
            'number' => Str::replace([$countryCode, '(', ')'], '', (string) $this->resource->phone),
            'country_code' => $countryCode,
            'country_name' => $country?->name,
            'state' => $isValid
                ? PhoneStateEnum::Valid->label()
                : PhoneStateEnum::Invalid->label(),
        ];
    }
}
