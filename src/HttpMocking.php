<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class HttpMock
{
    private static array $mocks = [];
    private static bool $preventStray = false;
    private static array $requests = [];
    private static bool $filterRegistered = false;
    
    public static function fake(string|array $urlPattern, array|callable $response = []): void
    {
        if (is_array($urlPattern)) {
            foreach ($urlPattern as $pattern => $resp) {
                self::$mocks[$pattern] = $resp;
            }
        } else {
            self::$mocks[$urlPattern] = $response;
        }
        
        if (!self::$filterRegistered) {
            add_filter('pre_http_request', [self::class, 'interceptRequest'], 10, 3);
            self::$filterRegistered = true;
        }
    }
    
    public static function preventStrayRequests(): void
    {
        self::$preventStray = true;
        
        if (!self::$filterRegistered) {
            add_filter('pre_http_request', [self::class, 'interceptRequest'], 10, 3);
            self::$filterRegistered = true;
        }
    }
    
    public static function allowStrayRequests(): void
    {
        self::$preventStray = false;
        remove_filter('pre_http_request', [self::class, 'interceptRequest']);
    }
    
    public static function interceptRequest($preempt, array $args, string $url)
    {
        self::$requests[] = [
            'url' => $url,
            'args' => $args,
            'time' => microtime(true),
        ];
        
        foreach (self::$mocks as $pattern => $response) {
            if (self::matchesPattern($url, $pattern)) {
                if (is_callable($response)) {
                    $response = $response($url, $args);
                }
                
                return self::buildResponse($response);
            }
        }
        
        if (self::$preventStray) {
            throw new \RuntimeException("Unexpected HTTP request to: {$url}");
        }
        
        return $preempt;
    }
    
    private static function matchesPattern(string $url, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }
        
        if (strpos($pattern, '*') !== false) {
            // Replace * with a placeholder that won't be affected by preg_quote
            $pattern = str_replace('*', '___WILDCARD___', $pattern);
            $pattern = preg_quote($pattern, '/');
            $pattern = str_replace('___WILDCARD___', '.*', $pattern);
            
            return (bool) preg_match('/^' . $pattern . '$/', $url);
        }
        
        return str_contains($url, $pattern);
    }
    
    private static function buildResponse(array $response): array
    {
        $defaults = [
            'response' => [
                'code' => 200,
                'message' => 'OK',
            ],
            'body' => json_encode([]),
            'headers' => [],
            'cookies' => [],
        ];
        
        return array_merge($defaults, $response);
    }
    
    public static function assertSent(string $urlPattern, ?callable $callback = null): void
    {
        $matchingRequests = array_filter(self::$requests, function ($request) use ($urlPattern) {
            return self::matchesPattern($request['url'], $urlPattern);
        });
        
        test()->assertNotEmpty($matchingRequests, "No HTTP request was sent to: {$urlPattern}");
        
        if ($callback !== null) {
            foreach ($matchingRequests as $request) {
                test()->assertTrue(
                    $callback($request['url'], $request['args']),
                    "HTTP request to {$request['url']} did not pass assertion"
                );
            }
        }
    }
    
    public static function assertNotSent(string $urlPattern): void
    {
        $matchingRequests = array_filter(self::$requests, function ($request) use ($urlPattern) {
            return self::matchesPattern($request['url'], $urlPattern);
        });
        
        test()->assertEmpty($matchingRequests, "HTTP request was unexpectedly sent to: {$urlPattern}");
    }
    
    public static function assertSentCount(string $urlPattern, int $count): void
    {
        $matchingRequests = array_filter(self::$requests, function ($request) use ($urlPattern) {
            return self::matchesPattern($request['url'], $urlPattern);
        });
        
        test()->assertCount($count, $matchingRequests, "Expected {$count} requests to {$urlPattern}");
    }
    
    public static function recorded(): array
    {
        return self::$requests;
    }
    
    public static function reset(): void
    {
        self::$mocks = [];
        self::$requests = [];
        self::$preventStray = false;
        
        if (self::$filterRegistered) {
            remove_filter('pre_http_request', [self::class, 'interceptRequest']);
            self::$filterRegistered = false;
        }
    }
}

function fakeHttp(string|array $urlPattern, array|callable $response = []): void
{
    HttpMock::fake($urlPattern, $response);
}

function preventStrayRequests(): void
{
    HttpMock::preventStrayRequests();
}

function allowStrayRequests(): void
{
    HttpMock::allowStrayRequests();
}

function assertHttpSent(string $urlPattern, ?callable $callback = null): void
{
    HttpMock::assertSent($urlPattern, $callback);
}

function assertHttpNotSent(string $urlPattern): void
{
    HttpMock::assertNotSent($urlPattern);
}

function assertHttpSentCount(string $urlPattern, int $count): void
{
    HttpMock::assertSentCount($urlPattern, $count);
}

