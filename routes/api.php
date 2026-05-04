<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PhoneNumberController;

Route::get('/dropdowns', [PhoneNumberController::class, 'dropdowns']);
Route::get('/phone-numbers', [PhoneNumberController::class, 'index']);
