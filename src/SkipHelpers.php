<?php

declare(strict_types=1);

namespace PestPluginWordPress;

/**
 * Skip helper functions for conditional test execution
 */

/**
 * Skip test if not in a multisite environment
 */
function skipIfNotMultisite(string $message = 'Test requires WordPress multisite'): void
{
    if (!is_multisite()) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if in a multisite environment
 */
function skipIfMultisite(string $message = 'Test requires single site WordPress'): void
{
    if (is_multisite()) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if a plugin is not active
 */
function skipIfPluginNotActive(string $plugin, ?string $message = null): void
{
    if (!is_plugin_active($plugin)) {
        $message = $message ?? "Test requires plugin: {$plugin}";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if a plugin is active
 */
function skipIfPluginActive(string $plugin, ?string $message = null): void
{
    if (is_plugin_active($plugin)) {
        $message = $message ?? "Test requires plugin to be inactive: {$plugin}";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if WordPress version is below required version
 */
function skipIfWordPressVersionBelow(string $version, ?string $message = null): void
{
    global $wp_version;
    
    if (version_compare($wp_version, $version, '<')) {
        $message = $message ?? "Test requires WordPress {$version} or higher (current: {$wp_version})";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if PHP version is below required version
 */
function skipIfPhpVersionBelow(string $version, ?string $message = null): void
{
    if (version_compare(PHP_VERSION, $version, '<')) {
        $message = $message ?? "Test requires PHP {$version} or higher (current: " . PHP_VERSION . ")";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if a PHP extension is not loaded
 */
function skipIfExtensionNotLoaded(string $extension, ?string $message = null): void
{
    if (!extension_loaded($extension)) {
        $message = $message ?? "Test requires PHP extension: {$extension}";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if not running in CI environment
 */
function skipIfNotCI(string $message = 'Test only runs in CI environment'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if (!$isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if running in CI environment
 */
function skipIfCI(string $message = 'Test only runs in local environment'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if ($isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if database is not MySQL
 */
function skipIfNotMySQL(string $message = 'Test requires MySQL database'): void
{
    global $wpdb;
    
    if (!$wpdb->use_mysqli) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test on specific operating system
 */
function skipOnOS(string $os, ?string $message = null): void
{
    $currentOS = strtolower(PHP_OS_FAMILY);
    $targetOS = strtolower($os);
    
    if ($currentOS === $targetOS || strpos($currentOS, $targetOS) !== false) {
        $message = $message ?? "Test skipped on {$os}";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test unless running on specific operating system
 */
function skipUnlessOS(string $os, ?string $message = null): void
{
    $currentOS = strtolower(PHP_OS_FAMILY);
    $targetOS = strtolower($os);
    
    if ($currentOS !== $targetOS && strpos($currentOS, $targetOS) === false) {
        $message = $message ?? "Test requires {$os}";
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if browser testing is not available
 */
function skipIfBrowserTestingNotAvailable(string $message = 'Test requires pestphp/pest-plugin-browser. Install with: composer require --dev pestphp/pest-plugin-browser'): void
{
    if (!function_exists('Pest\browser')) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip test if WooCommerce is not active
 */
function skipIfWooCommerceNotActive(string $message = 'Test requires WooCommerce to be active'): void
{
    if (!function_exists('WC')) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip browser tests locally (for faster development)
 */
function skipBrowserTestsLocally(string $message = 'Browser tests skipped locally for faster development'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if (!$isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip browser tests on CI (if needed)
 */
function skipBrowserTestsOnCi(string $message = 'Browser tests skipped on CI'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if ($isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip external API tests locally
 */
function skipExternalApiTestsLocally(string $message = 'External API tests skipped locally'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if (!$isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip external API tests on CI
 */
function skipExternalApiTestsOnCi(string $message = 'External API tests skipped on CI'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if ($isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip long running tests locally
 */
function skipLongRunningTestsLocally(string $message = 'Long running tests skipped locally'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if (!$isCI) {
        test()->markTestSkipped($message);
    }
}

/**
 * Skip long running tests on CI
 */
function skipLongRunningTestsOnCi(string $message = 'Long running tests skipped on CI'): void
{
    $isCI = getenv('CI') === 'true' || 
            getenv('GITHUB_ACTIONS') === 'true' || 
            getenv('GITLAB_CI') === 'true' ||
            getenv('CIRCLECI') === 'true';
    
    if ($isCI) {
        test()->markTestSkipped($message);
    }
}
