<?php

declare(strict_types=1);

namespace App\DTOs;

use ReflectionProperty;
use ReflectionNamedType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @phpstan-consistent-constructor
 */
abstract class BaseDto
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (! property_exists($this, $key)) {
                continue;
            }

            $rp = new ReflectionProperty($this, $key);
            $type = $rp->getType();

            // If the property has a type and the value isn't null, cast it
            if ($type instanceof ReflectionNamedType && $value !== null) {
                $typeName = $type->getName();

                // Basic type casting logic
                $value = match ($typeName) {
                    'int' => (int) $value,
                    'float' => (float) $value,
                    'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'string' => (string) $value,
                    default => $value,
                };
            }

            $this->{$key} = $value;
        }
    }

    /**
     * Create DTO from request input.
     */
    public static function fromRequest(FormRequest $request): static
    {
        return new static($request->validated());
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Generate a dynamic cache key based on non-empty property values.
     */
    public function toCacheKey(string $prefix): string
    {
        $data = $this->toArray();

        // Filter out nulls and empty strings and convert to array of key:value.
        $filtered = collect($data)
            ->filter(function ($value) {
                return ! is_null($value) && $value !== '';
            })
            ->mapWithKeys(function ($value, $key) {
                return [$key => "$key:$value"];
            })
            ->implode('|');

        return $prefix.'|'.$filtered;
    }
}
