<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ToggleCacheSupport
{
    /** @var array<int, string> */
    protected array $tags = [];

    /**
     * @param  array<int, string>  $tags
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function remember(string $key, Closure $callback, ?int $seconds = null): mixed
    {
        try {
            // 1. Check if caching is globally disabled or bypassed via request
            if (! $this->isEnabled() || request()->has('no-cache')) {
                $this->log("Bypassed for key [{$key}]");

                return $callback();
            }

            // 2. Setup tagging logic
            $ttl = $seconds ?? (int) config('system.toggle_cache.default_ttl', 60);
            $cacheInstance = $this->isTaggable() ? Cache::tags($this->tags) : Cache::getFacadeRoot();

            if ($cacheInstance->has($key)) {
                $this->log("Retrieved [ {$key} ]");

                return $cacheInstance->get($key);
            }

            /** @var T $data */
            $data = $callback();

            if ($this->isCacheable($data)) {
                $cacheInstance->put($key, $data, $ttl);
                $this->log("Store [ {$key} ]");
            } else {
                $this->log("Skip [ {$key} ] | empty results.");
            }

            return $data;
        } finally {
            $this->resetTags();
        }
    }

    public function clearTable(string $keyword): bool
    {
        try {
            $store = Cache::getStore();

            if ($store instanceof TaggableStore && ! empty($this->tags)) {
                $this->log("Flushing tags for table [{$keyword}]");

                return Cache::tags($this->tags)->flush();
            }

            return $this->clearByPattern("table:{$keyword}");
        } finally {
            $this->resetTags();
        }
    }

    public function clearByPattern(string $keyword): bool
    {
        try {
            $cacheDriver = config('cache.default');
            $this->log("Clearing cache by pattern: {$keyword}");

            // --- REDIS LOGIC ---
            if ($cacheDriver === 'redis') {
                $connection = Redis::connection('cache');
                $redisPrefix = (string) config('database.redis.options.prefix').(string) config('cache.prefix');
                $cursor = '0';
                $pattern = "*{$keyword}*";

                do {
                    /** @var array{0: string, 1: array<int, string>}|false $result */
                    $result = $connection->scan(
                        $cursor,
                        // @phpstan-ignore-next-line
                        ['match' => "{$redisPrefix}{$pattern}", 'count' => 100]
                    );
                    if (! is_array($result)) {
                        break;
                    }
                    [$cursor, $keys] = $result;

                    foreach ($keys as $redisKey) {
                        $cacheKey = Str::after($redisKey, $redisPrefix);
                        $this->log("Flushing after strip prefix from cacheKey: {$cacheKey}");
                        Cache::forget($cacheKey);
                    }
                } while ((string) $cursor !== '0');

                return true;
            }

            // --- DATABASE LOGIC ---
            if ($cacheDriver === 'database') {
                $table = config('cache.stores.database.table');

                return DB::table($table)
                    ->where('key', 'LIKE', "%{$keyword}%")
                    ->delete() > 0;
            }

            $this->log("No pattern logic for [{$cacheDriver}]. Flushing entire cache.");

            return Cache::flush();
        } finally {
            $this->resetTags();
        }
    }

    private function resetTags(): void
    {
        $this->tags = [];
    }

    private function isEnabled(): bool
    {
        return (bool) config('system.toggle_cache.enabled', true);
    }

    private function isTaggable(): bool
    {
        return Cache::getStore() instanceof TaggableStore && ! empty($this->tags);
    }

    private function isCacheable(mixed $data): bool
    {
        return match (true) {
            $data instanceof EloquentCollection => $data->isNotEmpty(),
            $data instanceof LengthAwarePaginator => $data->count() > 0,
            is_array($data) => ! empty($data),
            is_string($data) => trim($data) !== '',
            default => ! is_null($data),
        };
    }

    private function log(string $message, string $level = 'info'): void
    {
        $loggingEnabled = (bool) config('system.toggle_cache.debug_mode', true);

        if ($loggingEnabled) {
            $cacheDriver = config('cache.default');
            $cacheDriver = "ToggleCache [ {$cacheDriver} ]";
            $message .= $this->isTaggable() ? ' | With tags: [ '.implode(', ', $this->tags).' ] ' : '';
            logger()->$level("{$cacheDriver} | {$message}");
        }
    }
}
