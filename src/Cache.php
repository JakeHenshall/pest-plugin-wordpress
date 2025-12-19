<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class CacheHelpers
{
    public static function flush(): void
    {
        wp_cache_flush();
    }
    
    public static function forget(string $key, string $group = ''): void
    {
        wp_cache_delete($key, $group);
    }
    
    public static function remember(string $key, callable $callback, int $expiration = 0, string $group = ''): mixed
    {
        $value = wp_cache_get($key, $group, false, $found);
        
        if ($found) {
            return $value;
        }
        
        $value = $callback();
        wp_cache_set($key, $value, $group, $expiration);
        
        return $value;
    }
}

function flushCache(): void
{
    CacheHelpers::flush();
}

function forgetCache(string $key, string $group = ''): void
{
    CacheHelpers::forget($key, $group);
}

function assertCached(string $key, string $group = ''): void
{
    $found = false;
    wp_cache_get($key, $group, false, $found);
    test()->assertTrue($found, "Cache key '{$key}' in group '{$group}' does not exist");
}

function assertNotCached(string $key, string $group = ''): void
{
    $found = false;
    wp_cache_get($key, $group, false, $found);
    test()->assertFalse($found, "Cache key '{$key}' in group '{$group}' should not exist");
}

function assertTransient(string $key): void
{
    $value = get_transient($key);
    test()->assertNotFalse($value, "Transient '{$key}' does not exist");
}

function assertNoTransient(string $key): void
{
    $value = get_transient($key);
    test()->assertFalse($value, "Transient '{$key}' should not exist");
}

