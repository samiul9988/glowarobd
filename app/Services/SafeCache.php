<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SafeCache
{
    protected array $tags = [];

    public function __construct(array $tags = [])
    {
        $this->tags = $tags;
    }

    public static function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags') || config('cache.default') === 'redis';
    }

    protected function resolveStore()
    {
        if (!empty($this->tags) && self::supportsTags()) {
            return Cache::tags($this->tags);
        }

        return Cache::getFacadeRoot(); // default cache store instance
    }

    public static function tags(array $tags): self
    {
        return new self($tags);
    }

    public function get($key, $default = null)
    {
        return $this->resolveStore()->get($key, $default);
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->resolveStore()->put($key, $value, $ttl);
    }

    public function forget($key)
    {
        return $this->resolveStore()->forget($key);
    }

    public function remember($key, $ttl, \Closure $callback)
    {
        return $this->resolveStore()->remember($key, $ttl, $callback);
    }

    public function rememberForever($key, \Closure $callback)
    {
        return $this->resolveStore()->rememberForever($key, $callback);
    }

    public function flush()
    {
        return $this->resolveStore()->flush();
    }

    public function has($key)
    {
        return $this->resolveStore()->has($key);
    }

    public function increment($key, $value = 1)
    {
        return $this->resolveStore()->increment($key, $value);
    }

    public function decrement($key, $value = 1)
    {
        return $this->resolveStore()->decrement($key, $value);
    }

    public function forever($key, $value)
    {
        return $this->resolveStore()->forever($key, $value);
    }

    public function pull($key)
    {
        return $this->resolveStore()->pull($key);
    }
}
