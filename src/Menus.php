<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class MenuHelpers
{
    public static function create(string $name, string $location = 'primary'): int
    {
        $menuId = wp_create_nav_menu($name);
        
        if (is_wp_error($menuId)) {
            throw new \RuntimeException('Failed to create menu: ' . $menuId->get_error_message());
        }
        
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations[$location] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);
        
        return $menuId;
    }
    
    public static function addItem(int $menuId, array $args = []): int
    {
        $defaults = [
            'menu-item-title' => 'Menu Item',
            'menu-item-url' => home_url('/'),
            'menu-item-status' => 'publish',
        ];
        
        $args = array_merge($defaults, $args);
        
        return wp_update_nav_menu_item($menuId, 0, $args);
    }
}

function createMenu(string $name, string $location = 'primary'): int
{
    return MenuHelpers::create($name, $location);
}

function addMenuItem(int $menuId, array $args = []): int
{
    return MenuHelpers::addItem($menuId, $args);
}

function assertMenuExists(string $name): void
{
    $menu = wp_get_nav_menu_object($name);
    test()->assertNotFalse($menu, "Menu '{$name}' does not exist");
}

function assertMenuLocation(string $location): void
{
    $locations = get_nav_menu_locations();
    test()->assertArrayHasKey($location, $locations, "Menu location '{$location}' is not registered");
}

function assertMenuHasItems(int $menuId, int $count): void
{
    $items = wp_get_nav_menu_items($menuId);
    
    if ($items === false || $items === null) {
        test()->fail("Menu with ID {$menuId} does not exist");
        return;
    }
    
    test()->assertCount($count, $items, "Expected {$count} menu items");
}

