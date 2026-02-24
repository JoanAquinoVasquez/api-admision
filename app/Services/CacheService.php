<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CacheService
{
    /**
     * Default cache duration in minutes (e.g., 60 minutes).
     */
    protected const DEFAULT_DURATION = 60;

    /**
     * Retrieve an item from the cache, or store the default value.
     *
     * @param string $key
     * @param \Closure $callback
     * @param int|null $minutes
     * @return mixed
     */
    public function remember(string $key, \Closure $callback, ?int $minutes = null): mixed
    {
        $minutes = $minutes ?? self::DEFAULT_DURATION;
        // In Laravel 11/recent versions, cache stores use seconds or DateInterval, 
        // but remember() usually takes seconds or TTL. 
        // Let's assume minutes for consistency with older apps or convert to seconds.
        // Laravel's Cache::remember takes $ttl which can be seconds or Carbon instance.
        
        return Cache::remember($key, $minutes * 60, $callback);
    }

    /**
     * Retrieve an item from the cache, or store the default value forever.
     *
     * @param string $key
     * @param \Closure $callback
     * @return mixed
     */
    public function rememberForever(string $key, \Closure $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Generate a cache key.
     *
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        return $prefix . '_' . md5(json_encode($params));
    }
}
