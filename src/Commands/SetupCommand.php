<?php

declare(strict_types=1);

namespace PestPluginWordPress\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SetupCommand extends Command
{
    private Filesystem $filesystem;
    private string $workingDir;
    
    public function __construct()
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->workingDir = getcwd();
    }
    
    protected function configure(): void
    {
        $this
            ->setName('setup')
            ->setDescription('Sets up the test suites.')
            ->setHelp('This command helps you set up WordPress integration and unit test suites.')
            ->addArgument(
                'project-type',
                InputArgument::REQUIRED,
                'Select whether you want to setup tests for theme or a plugin. Can be "theme" or "plugin"'
            )
            ->addOption(
                'wp-version',
                null,
                InputOption::VALUE_OPTIONAL,
                'Pass the version of the WordPress you want to test on.',
                'latest'
            )
            ->addOption(
                'plugin-slug',
                null,
                InputOption::VALUE_OPTIONAL,
                'If you are setting the plugin tests provide the plugin slug.'
            )
            ->addOption(
                'skip-delete',
                null,
                InputOption::VALUE_NONE,
                'If you are running the setup tests in a CI pipeline, provide this option to skip the deletion step.'
            )
            ->addOption(
                'skip-sqlite',
                null,
                InputOption::VALUE_NONE,
                'Skip SQLite installation (use this if you want MySQL only).'
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $projectType = $input->getArgument('project-type');
        $wpVersion = $input->getOption('wp-version');
        $pluginSlug = $input->getOption('plugin-slug');
        $skipDelete = $input->getOption('skip-delete');
        
        if (!in_array($projectType, ['theme', 'plugin'])) {
            $io->error('Project type must be either "theme" or "plugin"');
            return Command::FAILURE;
        }
        
        if ($projectType === 'plugin' && !$pluginSlug) {
            $io->error('Plugin slug is required when setting up plugin tests.');
            return Command::FAILURE;
        }
        
        $io->title('WordPress Pest Integration Test Setup');
        
        // Create tests directory structure
        $io->section('Creating test directories');
        $this->createDirectories($io, $projectType);
        
        // Download WordPress
        $io->section('Downloading WordPress');
        $this->downloadWordPress($io, $wpVersion, $skipDelete);
        
        // Setup SQLite DB by default
        if (!$input->getOption('skip-sqlite')) {
            $io->section('Setting up SQLite database');
            $this->setupSQLite($io);
        }
        
        // Create configuration files
        $io->section('Creating configuration files');
        $this->createConfigFiles($io, $projectType, $pluginSlug);
        
        // Create PHPStan configuration
        $io->section('Creating PHPStan configuration');
        $this->createPhpStanConfig($io);
        
        // Create example tests
        $io->section('Creating example tests');
        $this->createExampleTests($io);
        
        $io->success([
            'WordPress Pest integration tests have been set up successfully!',
            '',
            'Database Configuration:',
            '  • SQLite (default): Tests will use SQLite automatically',
            '  • MySQL: Create tests/test-config.php from tests/test-config.php.example',
            '',
            'Run Tests:',
            '  vendor/bin/pest                     # Run all tests',
            '  vendor/bin/pest --group=unit        # Unit tests only',
            '  vendor/bin/pest --group=integration # Integration tests only',
            '',
            'Static Analysis:',
            '  vendor/bin/phpstan analyse          # Run PHPStan (if installed)',
        ]);
        
        return Command::SUCCESS;
    }
    
    private function createDirectories(SymfonyStyle $io, string $projectType): void
    {
        $directories = [
            'tests',
            'tests/Unit',
            'tests/Integration',
            'tests/bootstrap',
        ];
        
        foreach ($directories as $dir) {
            $path = $this->workingDir . '/' . $dir;
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
                $io->writeln("Created: {$dir}");
            } else {
                $io->writeln("Exists: {$dir}");
            }
        }
    }
    
    private function downloadWordPress(SymfonyStyle $io, string $version, bool $skipDelete): void
    {
        $wpDir = $this->workingDir . '/wp';
        
        if ($this->filesystem->exists($wpDir)) {
            if (!$skipDelete) {
                $io->writeln('Removing existing WordPress installation...');
                $this->filesystem->remove($wpDir);
            } else {
                $io->writeln('WordPress directory exists, skipping download...');
                return;
            }
        }
        
        $io->writeln("Downloading WordPress {$version}...");
        
        // Validate version format
        if ($version !== 'latest' && $version !== 'trunk') {
            // Check if version matches expected formats: 6.4, 6.4.2, etc.
            if (!preg_match('/^\d+\.\d+(?:\.\d+)?$/', $version)) {
                $io->error([
                    "Invalid WordPress version: {$version}",
                    'Valid formats:',
                    '  - "latest" or "trunk" for development version',
                    '  - Version number like "6.4" or "6.4.2"'
                ]);
                throw new \InvalidArgumentException("Invalid WordPress version format: {$version}");
            }
        }
        
        $branch = $version === 'latest' ? 'trunk' : $version;
        
        $process = new Process([
            'git',
            'clone',
            '--depth=1',
            '--branch=' . $branch,
            'https://github.com/WordPress/wordpress-develop.git',
            'wp'
        ], $this->workingDir, null, null, 300);
        
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer);
        });
        
        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            
            // Provide helpful error message if branch doesn't exist
            if (strpos($errorOutput, 'Remote branch') !== false && strpos($errorOutput, 'not found') !== false) {
                $io->error([
                    "WordPress version '{$branch}' not found in the repository.",
                    'Please check https://github.com/WordPress/wordpress-develop/branches for available versions.',
                    'Common versions: trunk, 6.4, 6.3, 6.2, etc.'
                ]);
            }
            
            throw new \RuntimeException('Failed to download WordPress: ' . $errorOutput);
        }
        
        if (!$skipDelete) {
            $wpContentDir = $wpDir . '/src/wp-content';
            if ($this->filesystem->exists($wpContentDir)) {
                $this->filesystem->remove($wpContentDir);
                $io->writeln('Removed wp-content directory');
            }
        }
        
        $io->writeln('WordPress downloaded successfully');
    }
    
    private function setupSQLite(SymfonyStyle $io): void
    {
        $io->writeln('Installing SQLite DB drop-in...');
        
        $composerFile = $this->workingDir . '/composer.json';
        if (!$this->filesystem->exists($composerFile)) {
            $io->error('composer.json not found. Cannot install SQLite DB package.');
            throw new \RuntimeException('composer.json not found in the project directory.');
        }
        
        // First, allow the composer/installers plugin
        $allowPluginProcess = new Process([
            'composer',
            'config',
            'allow-plugins.composer/installers',
            'true'
        ], $this->workingDir, null, null, 60);
        
        $allowPluginProcess->run();
        
        if (!$allowPluginProcess->isSuccessful()) {
            $io->warning('Could not configure allow-plugins, continuing anyway...');
        }
        
        // Now install the SQLite package
        $process = new Process([
            'composer',
            'require',
            '--dev',
            'aaemnnosttv/wp-sqlite-db'
        ], $this->workingDir, null, null, 300);
        
        $process->run();
        
        if (!$process->isSuccessful()) {
            $io->error([
                'Failed to install SQLite DB package.',
                'Error output:',
                $process->getErrorOutput(),
                '',
                'Your composer.json may be in an inconsistent state.',
                'Please run "composer install" to restore it or manually add:',
                '"aaemnnosttv/wp-sqlite-db" to your require-dev section.'
            ]);
            throw new \RuntimeException('Failed to install SQLite DB: ' . $process->getErrorOutput());
        }
        
        $io->writeln('SQLite DB installed successfully');
    }
    
    private function createConfigFiles(SymfonyStyle $io, string $projectType, ?string $pluginSlug): void
    {
        // Create Pest.php
        $this->createPestConfig($io, $projectType, $pluginSlug);
        
        // Create phpunit.xml
        $this->createPhpUnitConfig($io);
        
        // Create bootstrap files
        $this->createBootstrapFiles($io, $projectType, $pluginSlug);
        
        // Create Helpers.php
        $this->createHelpers($io);
        
        // Create test-config.php example for MySQL users
        $this->createTestConfigExample($io);
    }
    
    private function createPestConfig(SymfonyStyle $io, string $projectType, ?string $pluginSlug): void
    {
        $pestFile = $this->workingDir . '/tests/Pest.php';
        
        if ($this->filesystem->exists($pestFile)) {
            $io->writeln('Pest.php already exists, skipping...');
            return;
        }
        
        $content = file_get_contents(__DIR__ . '/../../stubs/Pest.php.stub');
        
        $this->filesystem->dumpFile($pestFile, $content);
        $io->writeln('Created: tests/Pest.php');
    }
    
    private function createPhpUnitConfig(SymfonyStyle $io): void
    {
        $phpunitFile = $this->workingDir . '/phpunit.xml';
        
        if ($this->filesystem->exists($phpunitFile)) {
            $io->writeln('phpunit.xml already exists, skipping...');
            return;
        }
        
        $content = file_get_contents(__DIR__ . '/../../stubs/phpunit.xml.stub');
        
        $this->filesystem->dumpFile($phpunitFile, $content);
        $io->writeln('Created: phpunit.xml');
    }
    
    private function createBootstrapFiles(SymfonyStyle $io, string $projectType, ?string $pluginSlug): void
    {
        $bootstrapDir = $this->workingDir . '/tests/bootstrap';
        
        // Integration bootstrap
        $integrationBootstrap = $bootstrapDir . '/integration.php';
        $content = file_get_contents(__DIR__ . '/../../stubs/bootstrap-integration-universal.php.stub');
        
        if ($projectType === 'plugin') {
            $projectPath = "plugins/{$pluginSlug}/{$pluginSlug}.php";
        } else {
            // For themes, point to functions.php
            $themeName = basename($this->workingDir);
            $projectPath = "themes/{$themeName}/functions.php";
        }
        
        $content = str_replace('{{PROJECT_PATH}}', $projectPath, $content);
        
        $this->filesystem->dumpFile($integrationBootstrap, $content);
        $io->writeln('Created: tests/bootstrap/integration.php');
        
        // Unit bootstrap
        $unitBootstrap = $bootstrapDir . '/unit.php';
        $unitContent = file_get_contents(__DIR__ . '/../../stubs/bootstrap-unit.php.stub');
        
        $this->filesystem->dumpFile($unitBootstrap, $unitContent);
        $io->writeln('Created: tests/bootstrap/unit.php');
        
        // wp-tests-config.php
        $wpTestsConfig = $bootstrapDir . '/wp-tests-config.php';
        $configContent = file_get_contents(__DIR__ . '/../../stubs/wp-tests-config.php.stub');
        
        $this->filesystem->dumpFile($wpTestsConfig, $configContent);
        $io->writeln('Created: tests/bootstrap/wp-tests-config.php');
    }
    
    private function createHelpers(SymfonyStyle $io): void
    {
        $helpersFile = $this->workingDir . '/tests/Helpers.php';
        
        if ($this->filesystem->exists($helpersFile)) {
            $io->writeln('Helpers.php already exists, skipping...');
            return;
        }
        
        $content = file_get_contents(__DIR__ . '/../../stubs/Helpers.php.stub');
        
        $this->filesystem->dumpFile($helpersFile, $content);
        $io->writeln('Created: tests/Helpers.php');
    }
    
    private function createTestConfigExample(SymfonyStyle $io): void
    {
        $exampleFile = $this->workingDir . '/tests/test-config.php.example';
        
        if ($this->filesystem->exists($exampleFile)) {
            return;
        }
        
        $content = file_get_contents(__DIR__ . '/../../stubs/test-config.php.example');
        
        $this->filesystem->dumpFile($exampleFile, $content);
        $io->writeln('Created: tests/test-config.php.example (for MySQL users)');
    }
    
    private function createExampleTests(SymfonyStyle $io): void
    {
        // Unit test example
        $unitTestFile = $this->workingDir . '/tests/Unit/ExampleTest.php';
        if (!$this->filesystem->exists($unitTestFile)) {
            $content = file_get_contents(__DIR__ . '/../../stubs/ExampleUnitTest.php.stub');
            $this->filesystem->dumpFile($unitTestFile, $content);
            $io->writeln('Created: tests/Unit/ExampleTest.php');
        }
        
        // Integration test example
        $integrationTestFile = $this->workingDir . '/tests/Integration/ExampleTest.php';
        if (!$this->filesystem->exists($integrationTestFile)) {
            $content = file_get_contents(__DIR__ . '/../../stubs/ExampleIntegrationTest.php.stub');
            $this->filesystem->dumpFile($integrationTestFile, $content);
            $io->writeln('Created: tests/Integration/ExampleTest.php');
        }
    }
    
    private function createPhpStanConfig(SymfonyStyle $io): void
    {
        $phpstanFile = $this->workingDir . '/phpstan.neon';
        
        if ($this->filesystem->exists($phpstanFile)) {
            $io->writeln('phpstan.neon already exists, skipping...');
            return;
        }
        
        $content = file_get_contents(__DIR__ . '/../../stubs/phpstan.neon.stub');
        
        if ($content === false) {
            throw new \RuntimeException('Failed to read phpstan.neon.stub');
        }
        
        $this->filesystem->dumpFile($phpstanFile, $content);
        $io->writeln('Created: phpstan.neon');
        
        // Check if PHPStan is installed
        $composerJsonPath = $this->workingDir . '/composer.json';
        if ($this->filesystem->exists($composerJsonPath)) {
            $composerJsonContent = file_get_contents($composerJsonPath);
            
            if ($composerJsonContent === false) {
                return;
            }
            
            $composerData = json_decode($composerJsonContent, true);
            $hasPhpStan = isset($composerData['require-dev']['phpstan/phpstan']) 
                || isset($composerData['require']['phpstan/phpstan']);
            
            if (!$hasPhpStan) {
                $io->note([
                    'PHPStan configuration created!',
                    '',
                    'To enable static analysis, install PHPStan:',
                    '  composer require --dev phpstan/phpstan',
                    '  composer require --dev phpstan/extension-installer',
                    '  composer require --dev szepeviktor/phpstan-wordpress',
                    '  composer require --dev php-stubs/wordpress-stubs',
                    '',
                    'Then run: vendor/bin/phpstan analyse'
                ]);
            }
        }
    }
}

