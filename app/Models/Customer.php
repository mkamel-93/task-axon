<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('customer')]
#[Fillable(['id', 'email', 'phone'])]
class Customer extends Model
{
    public $timestamps = false;
}
