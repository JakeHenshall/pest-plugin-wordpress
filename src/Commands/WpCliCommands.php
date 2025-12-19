<?php

declare(strict_types=1);

namespace PestPluginWordPress\Commands;

use WP_CLI;

if (!class_exists('WP_CLI')) {
    return;
}

class WpCliSetupCommand
{
    public function __invoke($args, $assoc_args): void
    {
        $projectType = $args[0] ?? null;
        
        if (!$projectType || !in_array($projectType, ['plugin', 'theme'])) {
            WP_CLI::error('Please specify project type: plugin or theme');
            return;
        }
        
        $wpVersion = $assoc_args['wp-version'] ?? 'latest';
        $pluginSlug = $assoc_args['plugin-slug'] ?? null;
        $skipDelete = isset($assoc_args['skip-delete']);
        
        if ($projectType === 'plugin' && !$pluginSlug) {
            WP_CLI::error('--plugin-slug is required for plugin setup');
            return;
        }
        
        // Check if wp-pest exists
        $wpPestPath = getcwd() . '/vendor/bin/wp-pest';
        if (!file_exists($wpPestPath)) {
            WP_CLI::error('vendor/bin/wp-pest not found. Please ensure pest-plugin-wordpress is installed via composer.');
            return;
        }
        
        // Build command array for safe execution
        $command = [escapeshellcmd($wpPestPath), 'setup', escapeshellarg($projectType)];
        
        if ($wpVersion !== 'latest') {
            $command[] = '--wp-version=' . escapeshellarg($wpVersion);
        }
        
        if ($pluginSlug) {
            $command[] = '--plugin-slug=' . escapeshellarg($pluginSlug);
        }
        
        if ($skipDelete) {
            $command[] = '--skip-delete';
        }
        
        $result = WP_CLI::launch(implode(' ', $command), false, true);
        
        if ($result->return_code !== 0) {
            WP_CLI::error('Setup command failed');
        }
        
        WP_CLI::success('Pest setup completed successfully!');
    }
}

class WpCliTestCommand
{
    public function unit($args, $assoc_args): void
    {
        $pestPath = getcwd() . '/vendor/bin/pest';
        if (!file_exists($pestPath)) {
            WP_CLI::error('vendor/bin/pest not found. Please ensure Pest is installed via composer.');
            return;
        }
        
        WP_CLI::line('Running unit tests...');
        $result = WP_CLI::launch(escapeshellcmd($pestPath) . ' --group=unit', false, true);
        
        if ($result->return_code !== 0) {
            WP_CLI::error('Unit tests failed');
        }
        
        WP_CLI::success('Unit tests passed!');
    }
    
    public function integration($args, $assoc_args): void
    {
        $pestPath = getcwd() . '/vendor/bin/pest';
        if (!file_exists($pestPath)) {
            WP_CLI::error('vendor/bin/pest not found. Please ensure Pest is installed via composer.');
            return;
        }
        
        WP_CLI::line('Running integration tests...');
        $result = WP_CLI::launch(escapeshellcmd($pestPath) . ' --group=integration', false, true);
        
        if ($result->return_code !== 0) {
            WP_CLI::error('Integration tests failed');
        }
        
        WP_CLI::success('Integration tests passed!');
    }
    
    public function all($args, $assoc_args): void
    {
        $pestPath = getcwd() . '/vendor/bin/pest';
        if (!file_exists($pestPath)) {
            WP_CLI::error('vendor/bin/pest not found. Please ensure Pest is installed via composer.');
            return;
        }
        
        WP_CLI::line('Running all tests...');
        $result = WP_CLI::launch(escapeshellcmd($pestPath), false, true);
        
        if ($result->return_code !== 0) {
            WP_CLI::error('Tests failed');
        }
        
        WP_CLI::success('All tests passed!');
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('pest setup', WpCliSetupCommand::class);
    WP_CLI::add_command('pest test unit', [WpCliTestCommand::class, 'unit']);
    WP_CLI::add_command('pest test integration', [WpCliTestCommand::class, 'integration']);
    WP_CLI::add_command('pest test all', [WpCliTestCommand::class, 'all']);
}

