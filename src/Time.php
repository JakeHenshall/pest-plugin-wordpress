<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class TimeHelpers
{
    private static ?int $frozenTime = null;
    private static $filterCallback = null;
    
    public static function freeze(?int $timestamp = null): void
    {
        self::$frozenTime = $timestamp ?? time();
        
        // Remove existing filter if present
        if (self::$filterCallback !== null) {
            remove_filter('current_time', self::$filterCallback, 10);
        }
        
        // Store the callback so we can remove it later
        self::$filterCallback = function($time, $type) {
            if ($type === 'timestamp') {
                return self::$frozenTime;
            }
            return $time;
        };
        
        add_filter('current_time', self::$filterCallback, 10, 2);
    }
    
    public static function travel(int $seconds): void
    {
        if (self::$frozenTime !== null) {
            self::$frozenTime += $seconds;
        } else {
            self::freeze(time() + $seconds);
        }
    }
    
    public static function travelTo(int $timestamp): void
    {
        self::freeze($timestamp);
    }
    
    public static function restore(): void
    {
        if (self::$filterCallback !== null) {
            remove_filter('current_time', self::$filterCallback, 10);
            self::$filterCallback = null;
        }
        self::$frozenTime = null;
    }
    
    public static function now(): int
    {
        return self::$frozenTime ?? time();
    }
}

function freezeTime(?int $timestamp = null): void
{
    TimeHelpers::freeze($timestamp);
}

function travelInTime(int $seconds): void
{
    TimeHelpers::travel($seconds);
}

function travelToTime(int $timestamp): void
{
    TimeHelpers::travelTo($timestamp);
}

function restoreTime(): void
{
    TimeHelpers::restore();
}

function timeNow(): int
{
    return TimeHelpers::now();
}

