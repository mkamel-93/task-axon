<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('toggle-cache', fn () => new \App\Support\ToggleCacheSupport);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'REGEXP',
                static fn (string $pattern, ?string $value): bool => (bool) preg_match($pattern, (string) $value),
                2
            );
        }

        if (config('system.database_debug_mode')) {
            DB::listen(fn ($query) => Log::info("Query [{$query->time}ms]: {$query->sql}", $query->bindings));
        }
    }
}
