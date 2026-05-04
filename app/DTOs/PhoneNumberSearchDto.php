<?php

declare(strict_types=1);

namespace App\DTOs;

class PhoneNumberSearchDto extends BaseDto
{
    public ?string $country = null;

    public ?string $state = null;

    public int $page = 1;

    public int $per_page = 5;
}
