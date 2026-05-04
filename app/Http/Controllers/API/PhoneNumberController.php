<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Enums\CountryEnum;
use App\Facades\ToggleCache;
use App\Enums\PhoneStateEnum;
use Illuminate\Http\JsonResponse;
use App\DTOs\PhoneNumberSearchDto;
use App\Http\Controllers\Controller;
use App\Http\Resources\PhoneNumberResource;
use App\Http\Requests\PhoneNumberIndexRequest;

class PhoneNumberController extends Controller
{
    public function index(PhoneNumberIndexRequest $request): JsonResponse
    {
        $dto = PhoneNumberSearchDto::fromRequest($request);
        $cacheKey = $dto->toCacheKey(prefix: 'layout:dashboard|table:customers');
        $data = ToggleCache::remember($cacheKey, function () use ($dto) {
            $baseQuery = Customer::query()
                ->when(
                    $dto->country,
                    fn ($query) => $query->where('phone', 'like', "%({$dto->country})%")
                )
                ->when($dto->state, function ($query) use ($dto) {
                    $state = PhoneStateEnum::tryFrom((string) $dto->state);

                    if (! $state) {
                        return;
                    }

                    $isValid = $state === PhoneStateEnum::Valid;

                    // fetch country code from phone number to get regex of country then apply this regex against phone number
                    $query->where(function ($q) use ($isValid) {
                        foreach (CountryEnum::cases() as $country) {
                            $isValid
                                ? $q->orWhereRaw('phone REGEXP ?', [$country->regex()])
                                : $q->whereRaw('phone NOT REGEXP ?', [$country->regex()]);
                        }
                    });
                });
            // logger([
            //    'query' => $baseQuery->toSql(),
            //    'binding' => $baseQuery->getBindings()
            // ]);
            $data = $baseQuery->simplePaginate($dto->per_page); // these better for small dataset and faster response

            return PhoneNumberResource::collection($data)->response()->getData(true);
        });

        return response()->json($data);
    }

    public function dropdowns(): JsonResponse
    {
        return response()->json([
            'countries' => ToggleCache::remember('layout:dashboard|enums:countries', fn () => CountryEnum::options()),
            'phone_states' => ToggleCache::remember('layout:dashboard|enums:phone_states', fn () => PhoneStateEnum::options()),
        ]);
    }
}
