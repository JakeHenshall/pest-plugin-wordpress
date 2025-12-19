<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class PluginHelpers
{
    public static function activate(string $plugin): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $result = activate_plugin($plugin);
        
        if (is_wp_error($result)) {
            throw new \RuntimeException("Failed to activate plugin '{$plugin}': " . $result->get_error_message());
        }
    }
    
    public static function deactivate(string $plugin): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        deactivate_plugins($plugin);
    }
    
    public static function isInstalled(string $plugin): bool
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $plugins = get_plugins();
        return isset($plugins[$plugin]);
    }
    
    public static function isActive(string $plugin): bool
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        return is_plugin_active($plugin);
    }
    
    public static function withYoast(callable $callback): void
    {
        if (!self::isInstalled('wordpress-seo/wp-seo.php')) {
            test()->markTestSkipped('Yoast SEO is not installed');
        }
        
        $wasActive = self::isActive('wordpress-seo/wp-seo.php');
        
        if (!$wasActive) {
            self::activate('wordpress-seo/wp-seo.php');
        }
        
        try {
            $callback();
        } finally {
            if (!$wasActive) {
                self::deactivate('wordpress-seo/wp-seo.php');
            }
        }
    }
    
    public static function withWooCommerce(callable $callback): void
    {
        if (!self::isInstalled('woocommerce/woocommerce.php')) {
            test()->markTestSkipped('WooCommerce is not installed');
        }
        
        $wasActive = self::isActive('woocommerce/woocommerce.php');
        
        if (!$wasActive) {
            self::activate('woocommerce/woocommerce.php');
        }
        
        try {
            $callback();
        } finally {
            if (!$wasActive) {
                self::deactivate('woocommerce/woocommerce.php');
            }
        }
    }
    
    public static function withAcf(callable $callback): void
    {
        if (!self::isInstalled('advanced-custom-fields/acf.php')) {
            test()->markTestSkipped('ACF is not installed');
        }
        
        $wasActive = self::isActive('advanced-custom-fields/acf.php');
        
        if (!$wasActive) {
            self::activate('advanced-custom-fields/acf.php');
        }
        
        try {
            $callback();
        } finally {
            if (!$wasActive) {
                self::deactivate('advanced-custom-fields/acf.php');
            }
        }
    }
    
    public static function withPlugin(string $plugin, callable $callback): void
    {
        if (!self::isInstalled($plugin)) {
            test()->markTestSkipped("Plugin '{$plugin}' is not installed");
        }
        
        $wasActive = self::isActive($plugin);
        
        if (!$wasActive) {
            self::activate($plugin);
        }
        
        try {
            $callback();
        } finally {
            if (!$wasActive) {
                self::deactivate($plugin);
            }
        }
    }
}

function activatePlugin(string $plugin): void
{
    PluginHelpers::activate($plugin);
}

function deactivatePlugin(string $plugin): void
{
    PluginHelpers::deactivate($plugin);
}

function assertPluginActive(string $plugin): void
{
    test()->assertTrue(
        PluginHelpers::isActive($plugin),
        "Plugin '{$plugin}' is not active"
    );
}

function assertPluginInactive(string $plugin): void
{
    test()->assertFalse(
        PluginHelpers::isActive($plugin),
        "Plugin '{$plugin}' should not be active"
    );
}

function withYoast(callable $callback): void
{
    PluginHelpers::withYoast($callback);
}

function withWooCommerce(callable $callback): void
{
    PluginHelpers::withWooCommerce($callback);
}

function withAcf(callable $callback): void
{
    PluginHelpers::withAcf($callback);
}

function withPlugin(string $plugin, callable $callback): void
{
    PluginHelpers::withPlugin($plugin, $callback);
}

