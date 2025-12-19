<?php

declare(strict_types=1);

namespace PestPluginWordPress;

/**
 * Check if tests are running in multisite mode
 */
function isMultisite(): bool
{
    return defined('MULTISITE') && MULTISITE;
}

/**
 * Switch to a specific blog in multisite
 */
function switchToBlog(int $blogId): void
{
    if (!isMultisite()) {
        throw new \RuntimeException('Not running in multisite mode');
    }
    
    switch_to_blog($blogId);
}

/**
 * Restore the current blog in multisite
 */
function restoreCurrentBlog(): void
{
    if (!isMultisite()) {
        return;
    }
    
    restore_current_blog();
}

/**
 * Create a new blog/site in multisite
 */
function createBlog(string $domain, string $path = '/', array $meta = []): int
{
    if (!isMultisite()) {
        throw new \RuntimeException('Not running in multisite mode');
    }
    
    $title = $meta['title'] ?? 'Test Site';
    $userId = $meta['user_id'] ?? 1;
    
    $blogId = wpmu_create_blog($domain, $path, $title, $userId, $meta, get_current_network_id());
    
    if (is_wp_error($blogId)) {
        throw new \RuntimeException('Failed to create blog: ' . $blogId->get_error_message());
    }
    
    return $blogId;
}

/**
 * Delete a blog/site in multisite
 */
function deleteBlog(int $blogId, bool $drop = true): void
{
    if (!isMultisite()) {
        throw new \RuntimeException('Not running in multisite mode');
    }
    
    wpmu_delete_blog($blogId, $drop);
}

/**
 * Assert that multisite is enabled
 */
function assertMultisite(): void
{
    test()->assertTrue(isMultisite(), 'Expected multisite to be enabled');
}

/**
 * Assert that multisite is disabled
 */
function assertNotMultisite(): void
{
    test()->assertFalse(isMultisite(), 'Expected multisite to be disabled');
}

/**
 * Get the current blog ID
 */
function currentBlogId(): int
{
    return get_current_blog_id();
}
