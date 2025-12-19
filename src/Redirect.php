<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class RedirectHelpers
{
    private static ?string $redirectUrl = null;
    private static ?int $redirectStatus = null;
    private static $filterCallback = null;
    
    public static function capture(): void
    {
        // Remove existing filter if present
        self::cleanup();
        
        // Store the callback so we can remove it later
        self::$filterCallback = function($location, $status) {
            self::$redirectUrl = $location;
            self::$redirectStatus = $status;
            return false; // Prevent actual redirect
        };
        
        add_filter('wp_redirect', self::$filterCallback, 10, 2);
    }
    
    public static function cleanup(): void
    {
        if (self::$filterCallback !== null) {
            remove_filter('wp_redirect', self::$filterCallback, 10);
            self::$filterCallback = null;
        }
    }
    
    public static function reset(): void
    {
        self::$redirectUrl = null;
        self::$redirectStatus = null;
        self::cleanup();
    }
    
    public static function getUrl(): ?string
    {
        return self::$redirectUrl;
    }
    
    public static function getStatus(): ?int
    {
        return self::$redirectStatus;
    }
}

function captureRedirects(): void
{
    RedirectHelpers::capture();
}

function assertRedirected(?string $url = null): void
{
    $redirectUrl = RedirectHelpers::getUrl();
    
    test()->assertNotNull($redirectUrl, 'No redirect occurred');
    
    if ($url !== null) {
        test()->assertEquals($url, $redirectUrl, "Expected redirect to '{$url}', got '{$redirectUrl}'");
    }
}

function assertNotRedirected(): void
{
    test()->assertNull(RedirectHelpers::getUrl(), 'Unexpected redirect occurred');
}

function assertRedirectStatus(int $status): void
{
    test()->assertEquals($status, RedirectHelpers::getStatus(), "Expected redirect status {$status}");
}

function assertRedirectContains(string $fragment): void
{
    $redirectUrl = RedirectHelpers::getUrl();
    test()->assertNotNull($redirectUrl, 'No redirect occurred');
    test()->assertStringContainsString($fragment, $redirectUrl);
}

