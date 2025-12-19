<?php

declare(strict_types=1);

/**
 * PHPStan bootstrap file
 * Defines stubs for Pest and Pest Browser functions
 * WordPress core functions are handled by php-stubs/wordpress-stubs
 */

// Stub Pest functions for static analysis
if (!function_exists('test')) {
    function test(string $description, ?Closure $closure = null): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('it')) {
    function it(string $description, ?Closure $closure = null): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('expect')) {
    function expect(mixed $value): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('describe')) {
    function describe(string $description, Closure $closure): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('beforeEach')) {
    function beforeEach(Closure $closure): void
    {
    }
}

if (!function_exists('afterEach')) {
    function afterEach(Closure $closure): void
    {
    }
}

if (!function_exists('beforeAll')) {
    function beforeAll(Closure $closure): void
    {
    }
}

if (!function_exists('afterAll')) {
    function afterAll(Closure $closure): void
    {
    }
}

if (!function_exists('uses')) {
    function uses(string ...$traits): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('pest')) {
    function pest(): mixed
    {
        return new stdClass();
    }
}

if (!function_exists('dataset')) {
    function dataset(string $name, iterable|Closure $dataset): void
    {
    }
}
