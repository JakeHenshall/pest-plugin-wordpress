<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class Factory
{
    public static function post(array $args = []): int
    {
        $defaults = [
            'post_title' => 'Test Post ' . uniqid(),
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post',
        ];
        
        $args = array_merge($defaults, $args);
        $postId = wp_insert_post($args);
        
        if (is_wp_error($postId)) {
            throw new \RuntimeException('Failed to create post: ' . $postId->get_error_message());
        }
        
        return $postId;
    }
    
    public static function posts(int $count, array $args = []): array
    {
        $posts = [];
        for ($i = 0; $i < $count; $i++) {
            $posts[] = self::post($args);
        }
        return $posts;
    }
    
    public static function user(array $args = []): int
    {
        $defaults = [
            'user_login' => 'testuser_' . uniqid(),
            'user_pass' => wp_generate_password(),
            'user_email' => 'test_' . uniqid() . '@example.com',
            'role' => 'subscriber',
        ];
        
        $args = array_merge($defaults, $args);
        $userId = wp_insert_user($args);
        
        if (is_wp_error($userId)) {
            throw new \RuntimeException('Failed to create user: ' . $userId->get_error_message());
        }
        
        return $userId;
    }
    
    public static function users(int $count, array $args = []): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::user($args);
        }
        return $users;
    }
    
    public static function term(string $name, string $taxonomy = 'category', array $args = []): int
    {
        $result = wp_insert_term($name, $taxonomy, $args);
        
        if (is_wp_error($result)) {
            throw new \RuntimeException('Failed to create term: ' . $result->get_error_message());
        }
        
        return $result['term_id'];
    }
    
    public static function terms(int $count, string $taxonomy = 'category', array $args = []): array
    {
        $terms = [];
        for ($i = 0; $i < $count; $i++) {
            $terms[] = self::term('Term ' . uniqid(), $taxonomy, $args);
        }
        return $terms;
    }
    
    public static function attachment(array $args = []): int
    {
        $defaults = [
            'post_title' => 'Test Attachment ' . uniqid(),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
        ];
        
        $args = array_merge($defaults, $args);
        $attachmentId = wp_insert_post($args);
        
        if (is_wp_error($attachmentId)) {
            throw new \RuntimeException('Failed to create attachment: ' . $attachmentId->get_error_message());
        }
        
        return $attachmentId;
    }
    
    public static function comment(int $postId, array $args = []): int
    {
        $defaults = [
            'comment_post_ID' => $postId,
            'comment_author' => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_content' => 'Test comment content',
            'comment_approved' => 1,
        ];
        
        $args = array_merge($defaults, $args);
        $commentId = wp_insert_comment($args);
        
        if (!$commentId) {
            throw new \RuntimeException('Failed to create comment');
        }
        
        return $commentId;
    }
}

function factory(): Factory
{
    return new Factory();
}


