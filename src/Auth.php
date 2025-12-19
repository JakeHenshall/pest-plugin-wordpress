<?php

declare(strict_types=1);

namespace PestPluginWordPress;

use WP_User;

function actingAs(int|string|WP_User $user): WP_User
{
    if (is_int($user)) {
        $user = get_user_by('id', $user);
    } elseif (is_string($user)) {
        $user = get_user_by('login', $user);
    }
    
    if (!$user instanceof WP_User) {
        throw new \InvalidArgumentException('Invalid user provided to actingAs()');
    }
    
    wp_set_current_user($user->ID);
    
    return $user;
}

function actingAsAdmin(): WP_User
{
    $adminId = Factory::user([
        'role' => 'administrator',
    ]);
    
    return actingAs($adminId);
}

function actingAsEditor(): WP_User
{
    $editorId = Factory::user([
        'role' => 'editor',
    ]);
    
    return actingAs($editorId);
}

function actingAsGuest(): void
{
    wp_set_current_user(0);
}

function assertAuthenticated(?WP_User $user = null): void
{
    $currentUserId = get_current_user_id();
    
    test()->assertTrue($currentUserId > 0, 'User is not authenticated');
    
    if ($user !== null) {
        test()->assertEquals($user->ID, $currentUserId, 'Authenticated user does not match expected user');
    }
}

function assertNotAuthenticated(): void
{
    test()->assertEquals(0, get_current_user_id(), 'User should not be authenticated');
}

function assertUserCan(string $capability, ?WP_User $user = null): void
{
    $user = $user ?? wp_get_current_user();
    
    test()->assertTrue(
        $user->has_cap($capability),
        "User does not have the '{$capability}' capability"
    );
}

function assertUserCannot(string $capability, ?WP_User $user = null): void
{
    $user = $user ?? wp_get_current_user();
    
    test()->assertFalse(
        $user->has_cap($capability),
        "User should not have the '{$capability}' capability"
    );
}


