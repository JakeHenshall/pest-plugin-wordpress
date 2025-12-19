<?php

declare(strict_types=1);

namespace PestPluginWordPress;

function assertPostExists(int $postId): void
{
    $post = get_post($postId);
    test()->assertNotNull($post, "Post with ID {$postId} does not exist");
}

function assertPostHasStatus(int $postId, string $status): void
{
    $post = get_post($postId);
    test()->assertNotNull($post, "Post with ID {$postId} does not exist");
    
    if ($post === null) {
        return;
    }
    
    test()->assertEquals($status, $post->post_status, "Post status expected to be '{$status}', got '{$post->post_status}'");
}

function assertTermExists(int $termId, string $taxonomy): void
{
    $term = get_term($termId, $taxonomy);
    test()->assertInstanceOf(\WP_Term::class, $term, "Term with ID {$termId} in taxonomy '{$taxonomy}' does not exist");
}

function assertUserExists(int $userId): void
{
    $user = get_user_by('id', $userId);
    test()->assertInstanceOf(\WP_User::class, $user, "User with ID {$userId} does not exist");
}

function assertUserHasRole(int $userId, string $role): void
{
    $user = get_user_by('id', $userId);
    test()->assertInstanceOf(\WP_User::class, $user, "User with ID {$userId} does not exist");
    
    if (!$user instanceof \WP_User) {
        return;
    }
    
    test()->assertTrue(in_array($role, $user->roles), "User does not have role '{$role}'");
}

function assertPostHasTerm(int $postId, int $termId, string $taxonomy): void
{
    $terms = wp_get_post_terms($postId, $taxonomy, ['fields' => 'ids']);
    test()->assertContains($termId, $terms, "Post {$postId} does not have term {$termId} in taxonomy '{$taxonomy}'");
}

function assertPostHasMeta(int $postId, string $key, mixed $value = null): void
{
    $exists = metadata_exists('post', $postId, $key);
    test()->assertTrue($exists, "Post {$postId} does not have meta key '{$key}'");
    
    if ($value !== null) {
        $actual = get_post_meta($postId, $key, true);
        test()->assertEquals($value, $actual, "Post meta '{$key}' expected to be '{$value}', got '{$actual}'");
    }
}

function assertOptionExists(string $option): void
{
    $value = get_option($option, '__NON_EXISTENT__');
    test()->assertNotEquals('__NON_EXISTENT__', $value, "Option '{$option}' does not exist");
}

function assertOptionEquals(string $option, mixed $expected): void
{
    $actual = get_option($option);
    test()->assertEquals($expected, $actual, "Option '{$option}' expected to be '{$expected}', got '{$actual}'");
}

function assertHookAdded(string $hook, callable|string $callback, int $priority = 10): void
{
    $actualPriority = has_action($hook, $callback);
    
    test()->assertIsNotBool(
        $actualPriority,
        "Hook '{$hook}' does not have callback attached"
    );
    
    if ($priority !== 10) {
        test()->assertEquals(
            $priority,
            $actualPriority,
            "Hook priority expected to be {$priority}"
        );
    }
}

function assertFilterAdded(string $filter, callable|string $callback, int $priority = 10): void
{
    $actualPriority = has_filter($filter, $callback);
    
    test()->assertIsNotBool(
        $actualPriority,
        "Filter '{$filter}' does not have callback attached"
    );
    
    if ($priority !== 10) {
        test()->assertEquals(
            $priority,
            $actualPriority,
            "Filter priority expected to be {$priority}"
        );
    }
}

function assertShortcodeExists(string $shortcode): void
{
    test()->assertTrue(
        shortcode_exists($shortcode),
        "Shortcode '{$shortcode}' is not registered"
    );
}

function assertPostTypeExists(string $postType): void
{
    test()->assertTrue(
        post_type_exists($postType),
        "Post type '{$postType}' is not registered"
    );
}

function assertTaxonomyExists(string $taxonomy): void
{
    test()->assertTrue(
        taxonomy_exists($taxonomy),
        "Taxonomy '{$taxonomy}' is not registered"
    );
}

function assertQueryHasPosts(\WP_Query $query): void
{
    test()->assertTrue($query->have_posts(), 'Query has no posts');
}

function assertQueryPostCount(\WP_Query $query, int $count): void
{
    test()->assertEquals($count, $query->post_count, "Expected {$count} posts in query, got {$query->post_count}");
}

function assertEnqueued(string $handle, string $type = 'script'): void
{
    if ($type === 'script') {
        test()->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' is not enqueued");
    } else {
        test()->assertTrue(wp_style_is($handle, 'enqueued'), "Style '{$handle}' is not enqueued");
    }
}

function assertNotEnqueued(string $handle, string $type = 'script'): void
{
    if ($type === 'script') {
        test()->assertFalse(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should not be enqueued");
    } else {
        test()->assertFalse(wp_style_is($handle, 'enqueued'), "Style '{$handle}' should not be enqueued");
    }
}

// WordPress-specific Pest v4 Expectations
if (!function_exists('expect')) {
    return;
}

expect()->extend('toBeSlug', function () {
    $value = $this->value;
    
    test()->assertIsString($value, 'Value must be a string to check if it is a slug');
    
    $isSlug = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
    
    test()->assertTrue(
        $isSlug,
        "Expected '{$value}' to be a valid slug (lowercase letters, numbers, and hyphens only)"
    );
    
    return $this;
});

expect()->extend('toBeValidPostStatus', function () {
    $value = $this->value;
    
    $validStatuses = ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'];
    
    test()->assertContains(
        $value,
        $validStatuses,
        "Expected '{$value}' to be a valid WordPress post status. Valid statuses: " . implode(', ', $validStatuses)
    );
    
    return $this;
});

expect()->extend('toBeValidUserRole', function () {
    $value = $this->value;
    
    $validRoles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
    
    // Get custom roles from WordPress
    if (function_exists('wp_roles')) {
        $wpRoles = wp_roles();
        $allRoles = array_keys($wpRoles->roles);
    } else {
        $allRoles = $validRoles;
    }
    
    test()->assertContains(
        $value,
        $allRoles,
        "Expected '{$value}' to be a valid WordPress user role"
    );
    
    return $this;
});

expect()->extend('toBeValidCapability', function () {
    $value = $this->value;
    
    $commonCapabilities = [
        'manage_options',
        'edit_posts',
        'edit_others_posts',
        'publish_posts',
        'read',
        'upload_files',
        'edit_pages',
        'edit_published_posts',
        'delete_posts',
        'delete_published_posts',
        'manage_categories',
        'moderate_comments',
        'edit_theme_options',
        'install_plugins',
        'activate_plugins',
        'edit_users',
        'delete_users',
        'create_users',
    ];
    
    // Check if it's a known capability or follows WordPress naming convention
    $isValid = in_array($value, $commonCapabilities) || 
               preg_match('/^[a-z_]+$/', $value) === 1;
    
    test()->assertTrue(
        $isValid,
        "Expected '{$value}' to be a valid WordPress capability"
    );
    
    return $this;
});

expect()->extend('toBeValidPostType', function () {
    $value = $this->value;
    
    if (function_exists('post_type_exists')) {
        $exists = post_type_exists($value);
        test()->assertTrue(
            $exists,
            "Expected '{$value}' to be a registered WordPress post type"
        );
    } else {
        $builtInTypes = ['post', 'page', 'attachment', 'revision', 'nav_menu_item'];
        test()->assertContains(
            $value,
            $builtInTypes,
            "Expected '{$value}' to be a valid WordPress post type"
        );
    }
    
    return $this;
});

expect()->extend('toBeValidTaxonomy', function () {
    $value = $this->value;
    
    if (function_exists('taxonomy_exists')) {
        $exists = taxonomy_exists($value);
        test()->assertTrue(
            $exists,
            "Expected '{$value}' to be a registered WordPress taxonomy"
        );
    } else {
        $builtInTaxonomies = ['category', 'post_tag', 'nav_menu', 'link_category', 'post_format'];
        test()->assertContains(
            $value,
            $builtInTaxonomies,
            "Expected '{$value}' to be a valid WordPress taxonomy"
        );
    }
    
    return $this;
});

expect()->extend('toBeWordPressError', function () {
    test()->assertInstanceOf(
        \WP_Error::class,
        $this->value,
        'Expected value to be a WP_Error instance'
    );
    
    return $this;
});

expect()->extend('toHaveErrorCode', function (string $code) {
    test()->assertInstanceOf(
        \WP_Error::class,
        $this->value,
        'Expected value to be a WP_Error instance'
    );
    
    if ($this->value instanceof \WP_Error) {
        test()->assertEquals(
            $code,
            $this->value->get_error_code(),
            "Expected WP_Error to have code '{$code}', got '{$this->value->get_error_code()}'"
        );
    }
    
    return $this;
});

expect()->extend('toBePublished', function () {
    $postId = $this->value;
    
    if (is_object($postId) && isset($postId->ID)) {
        $postId = $postId->ID;
    }
    
    $post = get_post($postId);
    test()->assertNotNull($post, "Post with ID {$postId} does not exist");
    
    if ($post) {
        test()->assertEquals(
            'publish',
            $post->post_status,
            "Expected post to be published, got status '{$post->post_status}'"
        );
    }
    
    return $this;
});

expect()->extend('toHavePostMeta', function (string $key, mixed $value = null) {
    $postId = $this->value;
    
    if (is_object($postId) && isset($postId->ID)) {
        $postId = $postId->ID;
    }
    
    $exists = metadata_exists('post', $postId, $key);
    test()->assertTrue($exists, "Post {$postId} does not have meta key '{$key}'");
    
    if ($value !== null && $exists) {
        $actual = get_post_meta($postId, $key, true);
        test()->assertEquals(
            $value,
            $actual,
            "Expected post meta '{$key}' to be '{$value}', got '{$actual}'"
        );
    }
    
    return $this;
});

expect()->extend('toHaveUserRole', function (string $role) {
    $userId = $this->value;
    
    if (is_object($userId) && isset($userId->ID)) {
        $userId = $userId->ID;
    }
    
    $user = get_user_by('id', $userId);
    test()->assertInstanceOf(\WP_User::class, $user, "User with ID {$userId} does not exist");
    
    if ($user instanceof \WP_User) {
        test()->assertTrue(
            in_array($role, $user->roles),
            "Expected user to have role '{$role}'"
        );
    }
    
    return $this;
});

expect()->extend('toHaveCapability', function (string $capability) {
    $userId = $this->value;
    
    if (is_object($userId) && isset($userId->ID)) {
        $user = $userId;
    } else {
        $user = get_user_by('id', $userId);
    }
    
    test()->assertInstanceOf(\WP_User::class, $user, "Invalid user");
    
    if ($user instanceof \WP_User) {
        test()->assertTrue(
            $user->has_cap($capability),
            "Expected user to have capability '{$capability}'"
        );
    }
    
    return $this;
});

