<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class CronTester
{
    public static function run(string $hook): void
    {
        do_action($hook);
    }
    
    public static function runAll(): void
    {
        $crons = _get_cron_array();
        
        if ($crons === false || !is_array($crons) || empty($crons)) {
            return;
        }
        
        foreach ($crons as $timestamp => $cronEvents) {
            foreach ($cronEvents as $hook => $events) {
                foreach ($events as $event) {
                    do_action_ref_array($hook, $event['args'] ?? []);
                }
            }
        }
    }
    
    public static function runDue(): void
    {
        $crons = _get_cron_array();
        
        if ($crons === false || !is_array($crons) || empty($crons)) {
            return;
        }
        
        $currentTime = time();
        
        foreach ($crons as $timestamp => $cronEvents) {
            if ($timestamp > $currentTime) {
                continue;
            }
            
            foreach ($cronEvents as $hook => $events) {
                foreach ($events as $event) {
                    do_action_ref_array($hook, $event['args'] ?? []);
                }
            }
        }
    }
}

function runCron(string $hook): void
{
    CronTester::run($hook);
}

function runAllCrons(): void
{
    CronTester::runAll();
}

function runDueCrons(): void
{
    CronTester::runDue();
}

function assertCronScheduled(string $hook, ?array $args = null): void
{
    $scheduled = wp_next_scheduled($hook, $args ?? []);
    
    test()->assertNotFalse($scheduled, "Cron event '{$hook}' is not scheduled");
}

function assertCronNotScheduled(string $hook, ?array $args = null): void
{
    $scheduled = wp_next_scheduled($hook, $args ?? []);
    
    test()->assertFalse($scheduled, "Cron event '{$hook}' should not be scheduled");
}

function assertCronScheduledAt(string $hook, int $timestamp, ?array $args = null): void
{
    $scheduled = wp_next_scheduled($hook, $args ?? []);
    
    test()->assertEquals($timestamp, $scheduled, "Cron event '{$hook}' is not scheduled at the expected time");
}

function scheduleCron(string $hook, ?int $timestamp = null, array $args = []): void
{
    wp_schedule_single_event($timestamp ?? time(), $hook, $args);
}

function clearAllCrons(): void
{
    $crons = _get_cron_array();
    
    if ($crons === false || !is_array($crons) || empty($crons)) {
        return;
    }
    
    foreach ($crons as $timestamp => $cronEvents) {
        foreach ($cronEvents as $hook => $events) {
            foreach ($events as $key => $event) {
                wp_unschedule_event($timestamp, $hook, $event['args'] ?? []);
            }
        }
    }
}

