<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class EmailTester
{
    private static array $emails = [];
    private static bool $enabled = false;
    
    public static function fake(): void
    {
        if (!self::$enabled) {
            add_filter('pre_wp_mail', [self::class, 'interceptEmail'], 10, 2);
            self::$enabled = true;
        }
        
        self::$emails = [];
    }
    
    public static function interceptEmail($null, $atts): bool
    {
        self::$emails[] = [
            'to' => $atts['to'],
            'subject' => $atts['subject'],
            'message' => $atts['message'],
            'headers' => $atts['headers'] ?? [],
            'attachments' => $atts['attachments'] ?? [],
            'time' => microtime(true),
        ];
        
        return true;
    }
    
    public static function assertSent(?string $to = null, ?callable $callback = null): void
    {
        $matching = self::findEmails($to, $callback);
        
        test()->assertNotEmpty($matching, "No email was sent" . ($to ? " to {$to}" : ""));
    }
    
    public static function assertNotSent(?string $to = null, ?callable $callback = null): void
    {
        $matching = self::findEmails($to, $callback);
        
        test()->assertEmpty($matching, "Email was unexpectedly sent" . ($to ? " to {$to}" : ""));
    }
    
    public static function assertSentCount(int $count, ?string $to = null): void
    {
        $matching = self::findEmails($to);
        
        test()->assertCount($count, $matching, "Expected {$count} emails to be sent");
    }
    
    public static function assertSentTo(array $recipients): void
    {
        foreach ($recipients as $recipient) {
            self::assertSent($recipient);
        }
    }
    
    public static function assertNothingSent(): void
    {
        test()->assertEmpty(self::$emails, "Expected no emails to be sent, but " . count(self::$emails) . " were sent");
    }
    
    private static function findEmails(?string $to = null, ?callable $callback = null): array
    {
        $emails = self::$emails;
        
        if ($to !== null) {
            $emails = array_filter($emails, function($email) use ($to) {
                if (is_array($email['to'])) {
                    return in_array($to, $email['to']);
                }
                return $email['to'] === $to;
            });
        }
        
        if ($callback !== null) {
            $emails = array_filter($emails, $callback);
        }
        
        return $emails;
    }
    
    public static function sent(): array
    {
        return self::$emails;
    }
    
    public static function reset(): void
    {
        self::$emails = [];
        
        if (self::$enabled) {
            remove_filter('pre_wp_mail', [self::class, 'interceptEmail']);
            self::$enabled = false;
        }
    }
}

function fakeEmail(): void
{
    EmailTester::fake();
}

function assertEmailSent(?string $to = null, ?callable $callback = null): void
{
    EmailTester::assertSent($to, $callback);
}

function assertEmailNotSent(?string $to = null, ?callable $callback = null): void
{
    EmailTester::assertNotSent($to, $callback);
}

function assertEmailSentCount(int $count, ?string $to = null): void
{
    EmailTester::assertSentCount($count, $to);
}

function assertEmailSentTo(array $recipients): void
{
    EmailTester::assertSentTo($recipients);
}

function assertNoEmailSent(): void
{
    EmailTester::assertNothingSent();
}

