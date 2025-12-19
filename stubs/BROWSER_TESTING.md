# Browser Testing Installation Guide

This guide will help you set up browser testing for WordPress with Pest v4.

## Prerequisites

- PHP >= 8.3
- Node.js >= 18
- Composer
- npm or yarn

## Installation Steps

### 1. Install Browser Testing Plugin

```bash
composer require pestphp/pest-plugin-browser --dev
```

### 2. Install Playwright

```bash
npm install playwright@latest
npx playwright install
```

This will install Playwright and download the necessary browser binaries (Chrome, Firefox, Safari).

### 3. Configure WordPress Server

For browser tests to work, you need a running WordPress instance. You have several options:

#### Option A: PHP Built-in Server (Simplest)

```bash
# Start server in wp directory
cd wp && php -S localhost:8080 &
```

#### Option B: Local Development Environment

Use your existing local development setup (MAMP, XAMPP, Local, etc.).

#### Option C: Docker

```bash
docker-compose up -d
```

### 4. Set WordPress URL

In your `tests/bootstrap/integration.php` file:

```php
define('WP_SITEURL', 'http://localhost:8080');
define('WP_HOME', 'http://localhost:8080');
```

Or use environment variables:

```bash
export WP_SITEURL=http://localhost:8080
export WP_HOME=http://localhost:8080
```

### 5. Create Your First Browser Test

Create `tests/Browser/ExampleBrowserTest.php`:

```php
<?php

use function PestPluginWordPress\{
    visitAdmin,
    browserLoginAsAdmin,
    assertLoggedInAs
};

if (isUnitTest()) {
    return;
}

test('admin can log in', function () {
    $page = browserLoginAsAdmin();
    
    assertLoggedInAs($page, 'admin');
})->group('browser');
```

### 6. Run Browser Tests

```bash
# Run all browser tests
vendor/bin/pest --group=browser

# Run with visible browser (for debugging)
vendor/bin/pest --group=browser -- --headed

# Run in specific browser
vendor/bin/pest --group=browser -- --browser=firefox
```

## GitHub Actions Setup

Add to `.github/workflows/tests.yml`:

```yaml
name: Browser Tests

on: [push, pull_request]

jobs:
  browser-tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
          extensions: sqlite3

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: "20"

      - name: Install Dependencies
        run: |
          composer install
          composer require pestphp/pest-plugin-browser --dev
          npm install playwright@latest
          npx playwright install --with-deps

      - name: Setup WordPress
        run: |
          vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --skip-delete

      - name: Start WordPress Server
        run: |
          cd wp && php -S localhost:8080 &
          sleep 5

      - name: Run Browser Tests
        run: vendor/bin/pest --group=browser

      - name: Upload Screenshots
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: browser-screenshots
          path: tests/screenshots/
```

## Troubleshooting

### Browser Not Found

If you see "Browser not found" errors:

```bash
# Reinstall browsers
npx playwright install --force
```

### Connection Refused

If tests can't connect to WordPress:

```bash
# Check if server is running
curl http://localhost:8080

# Check WordPress is installed
ls -la wp/
```

### Slow Tests

Browser tests are slower than unit tests. Use skip helpers:

```php
test('browser test', function () {
    skipBrowserTestsLocally(); // Skip in development
    
    // Your test
})->group('browser');
```

### Screenshots for Debugging

Take screenshots when tests fail:

```php
test('complex interaction', function () {
    $page = visitAdmin('index.php');
    
    screenshotAs($page, 'before-action');
    
    // Your action
    
    screenshotAs($page, 'after-action');
})->group('browser');
```

## Best Practices

1. **Group Browser Tests**: Always add `->group('browser')` to browser tests
2. **Skip Locally**: Use `skipBrowserTestsLocally()` for faster development
3. **Use Sharding**: Split browser tests in CI with `--shard=1/2`
4. **Cache Browsers**: Cache Playwright browsers in CI to speed up builds
5. **Separate Jobs**: Run browser tests in a separate CI job from unit tests
6. **Screenshots**: Capture screenshots on failures for debugging
7. **Parallel Execution**: Browser tests can run in parallel

## Example Test Suite Structure

```
tests/
├── Unit/
│   └── ExampleTest.php
├── Integration/
│   └── ExampleIntegrationTest.php
└── Browser/
    ├── AdminTest.php
    ├── GutenbergTest.php
    └── WooCommerceTest.php
```

## Next Steps

- Read the full browser testing documentation in README.md
- Check example tests in `stubs/ExampleBrowserTest.php.stub`
- Explore WooCommerce examples in `stubs/ExampleWooCommerceBrowserTest.php.stub`
- Learn about test sharding for faster CI/CD

## Resources

- [Pest Browser Plugin Documentation](https://pestphp.com/docs/browser-testing)
- [Playwright Documentation](https://playwright.dev/)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)


