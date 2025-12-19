<?php

declare(strict_types=1);

namespace PestPluginWordPress;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cleanupTestData();
        
        HttpMock::reset();
        EmailTester::reset();
        RedirectHelpers::reset();
        AdminNoticeHelpers::reset();
        TimeHelpers::restore();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->cleanupTestData();
        
        HttpMock::reset();
        EmailTester::reset();
        RedirectHelpers::reset();
        AdminNoticeHelpers::reset();
        TimeHelpers::restore();
        wp_set_current_user(0);
    }
    
    private function cleanupTestData(): void
    {
        global $wpdb;
        
        if (function_exists('\_delete_all_posts')) {
            \_delete_all_posts();
        }
        
        $users = get_users(['fields' => 'ID']);
        foreach ($users as $userId) {
            if ($userId > 1) {
                wp_delete_user($userId);
            }
        }
        
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'fields' => 'ids',
            ]);
            
            if (!is_wp_error($terms)) {
                foreach ($terms as $termId) {
                    wp_delete_term($termId, $taxonomy);
                }
            }
        }
        
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
        
        \PestPluginWordPress\clearAllCrons();
    }
    
    protected function assertQuerySuccessful(\WP_REST_Response $response): void
    {
        $this->assertInstanceOf(\WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
    }
    
    protected function assertHasKey(string $key, array $array, string $message = ''): void
    {
        $this->assertArrayHasKey($key, $array, $message);
    }
    
    protected function createPost(array $args = []): int
    {
        $defaults = [
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post',
        ];
        
        $args = array_merge($defaults, $args);
        
        return wp_insert_post($args);
    }
    
    protected function createTerm(string $name, string $taxonomy = 'category', array $args = []): int
    {
        $result = wp_insert_term($name, $taxonomy, $args);
        
        if (is_wp_error($result)) {
            $this->fail('Failed to create term: ' . $result->get_error_message());
        }
        
        return $result['term_id'];
    }
    
    protected function createUser(array $args = []): int
    {
        $defaults = [
            'user_login' => 'testuser_' . uniqid(),
            'user_pass' => wp_generate_password(),
            'user_email' => 'test_' . uniqid() . '@example.com',
            'role' => 'subscriber',
        ];
        
        $args = array_merge($defaults, $args);
        
        $user_id = wp_insert_user($args);
        
        if (is_wp_error($user_id)) {
            $this->fail('Failed to create user: ' . $user_id->get_error_message());
        }
        
        return $user_id;
    }
}

