<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class AdminNoticeHelpers
{
    private static array $notices = [];
    private static $startCallback = null;
    private static $endCallback = null;
    
    public static function capture(): void
    {
        // Remove existing hooks if present
        self::cleanup();
        
        // Store callbacks so we can remove them later
        self::$startCallback = function() {
            ob_start();
        };
        
        self::$endCallback = function() {
            self::$notices[] = ob_get_clean();
        };
        
        add_action('admin_notices', self::$startCallback, -999);
        add_action('admin_notices', self::$endCallback, 999);
    }
    
    public static function cleanup(): void
    {
        if (self::$startCallback !== null) {
            remove_action('admin_notices', self::$startCallback, -999);
            self::$startCallback = null;
        }
        
        if (self::$endCallback !== null) {
            remove_action('admin_notices', self::$endCallback, 999);
            self::$endCallback = null;
        }
    }
    
    public static function getNotices(): array
    {
        return self::$notices;
    }
    
    public static function reset(): void
    {
        self::$notices = [];
        self::cleanup();
    }
}

function captureAdminNotices(): void
{
    AdminNoticeHelpers::capture();
}

function assertAdminNotice(string $text): void
{
    $notices = AdminNoticeHelpers::getNotices();
    $found = false;
    
    foreach ($notices as $notice) {
        if (str_contains($notice, $text)) {
            $found = true;
            break;
        }
    }
    
    test()->assertTrue($found, "Admin notice containing '{$text}' was not found");
}

function assertNoAdminNotice(): void
{
    $notices = AdminNoticeHelpers::getNotices();
    test()->assertEmpty($notices, 'Expected no admin notices');
}

function assertAdminNoticeType(string $type): void
{
    $notices = AdminNoticeHelpers::getNotices();
    $found = false;
    
    foreach ($notices as $notice) {
        if (str_contains($notice, "notice-{$type}")) {
            $found = true;
            break;
        }
    }
    
    test()->assertTrue($found, "Admin notice of type '{$type}' was not found");
}

