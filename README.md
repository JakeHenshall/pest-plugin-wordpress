# WordPress Pest Testing Framework

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jakehenshall/pest-plugin-wordpress.svg?style=flat-square)](https://packagist.org/packages/jakehenshall/pest-plugin-wordpress)
[![Total Downloads](https://img.shields.io/packagist/dt/jakehenshall/pest-plugin-wordpress.svg?style=flat-square)](https://packagist.org/packages/jakehenshall/pest-plugin-wordpress)
[![License](https://img.shields.io/packagist/l/jakehenshall/pest-plugin-wordpress.svg?style=flat-square)](https://packagist.org/packages/jakehenshall/pest-plugin-wordpress)

**The complete WordPress testing solution.** One package includes everything: Pest PHP v4, PHPStan v2.1, SQLite, MySQL support, WordPress stubs, and 150+ helper functions. Write beautiful, Laravel-style tests with zero configuration.

> **🔋 Batteries Included**: Install once, test immediately. No setup, no configuration, no additional packages needed.

```php
test('creates posts and sends emails', function () {
    actingAsAdmin();

    $postId = factory()::post(['post_title' => 'Hello World']);

    fakeEmail();
    wp_mail('admin@example.com', 'New Post', 'Post created!');

    assertPostExists($postId);
    assertEmailSent('admin@example.com');
});
```

## Why This Package?

Testing WordPress shouldn't be complicated. This package brings the joy of testing to WordPress with:

- 🚀 **150+ Helper Functions** - Everything you need out of the box
- 🎨 **Laravel-Style Syntax** - Beautiful, expressive test code
- ⚡ **Fast** - SQLite database built-in for speed (MySQL supported too)
- 🔬 **PHPStan Built-in** - Static analysis included, no extra setup
- 🌐 **Browser Testing** - Test WordPress admin, Gutenberg, WooCommerce with real browsers
- 🔌 **Plugin Compatible** - Works with Yoast, WooCommerce, ACF
- 🌐 **WP-CLI Integration** - Native WordPress tooling
- 📦 **Complete Coverage** - REST API, AJAX, Blocks, Email, Cron, and more
- 🎯 **Pest v4 Features** - Test sharding, skip helpers, new expectations
- 🔋 **Batteries Included** - One package, zero configuration

## Requirements

- PHP >= 8.3.0
- Composer

That's it! Everything else (Pest, PHPStan, SQLite, WordPress stubs) is included automatically.

## Installation

Install via Composer in your WordPress plugin or theme:

```bash
composer require jakehenshall/pest-plugin-wordpress --dev
```

**What you get automatically:**

- ✅ Pest PHP v4 - Complete testing framework
- ✅ PHPStan v2.1 - Static analysis with WordPress rules
- ✅ SQLite - Fast in-memory database for tests
- ✅ MySQL Support - Production-like testing
- ✅ WordPress Stubs - Full IntelliSense support
- ✅ 150+ Helper Functions - HTTP, Email, AJAX, REST API, and more
- ✅ 14 Ready-to-Use Stubs - Test examples, configs, CI/CD templates
- ✅ WP-CLI Commands - Native WordPress integration

**One package. Zero configuration. Start testing immediately.**

### Using PHPStan (Built-in)

PHPStan is automatically included with WordPress-specific rules. Add to your `composer.json`:

```json
{
  "scripts": {
    "phpstan": "phpstan analyse --memory-limit=2G",
    "phpstan:baseline": "phpstan analyse --memory-limit=2G --generate-baseline"
  }
}
```

Create `phpstan.neon`:

```neon
parameters:
    level: 6
    paths:
        - your-plugin.php
        - src
    scanFiles:
        - vendor/php-stubs/wordpress-stubs/wordpress-stubs.php
```

Run analysis:

```bash
composer phpstan
```

**What's included:**

- ✅ PHPStan v2.1
- ✅ WordPress-specific rules
- ✅ WordPress function stubs
- ✅ Level 6 analysis ready

## Quick Start

### 1. Install the Package

```bash
composer require jakehenshall/pest-plugin-wordpress --dev
```

This single command installs:

- Pest PHP v4 (testing framework)
- PHPStan v2.1 (static analysis)
- SQLite database driver
- WordPress function stubs
- All 150+ helper functions

### 2. Run Setup

For a **plugin**:

```bash
vendor/bin/wp-pest setup plugin --plugin-slug=my-awesome-plugin
```

For a **theme**:

```bash
vendor/bin/wp-pest setup theme
```

Or via WP-CLI:

```bash
wp pest setup plugin --plugin-slug=my-plugin
```

This will:

- Create `tests/` directory structure
- Download WordPress core
- Set up SQLite database (automatically included)
- Create example tests (unit, integration, browser)
- Configure PHPUnit/Pest
- Generate `phpunit.xml` configuration
- Set up WordPress test config

**Optional:** You can also copy these stubs from `vendor/jakehenshall/pest-plugin-wordpress/stubs/`:
- `phpstan.neon.stub` - PHPStan configuration
- `phpstan-baseline.neon.stub` - PHPStan baseline
- `.gitignore.stub` - Ignore test artifacts
- `.github-workflows-tests.yml.stub` - CI/CD workflow
- `composer.json.stub` - Example project structure

### 3. Run Your Tests

```bash
# Run all tests
vendor/bin/pest

# Run unit tests only
vendor/bin/pest --group=unit

# Run integration tests only
vendor/bin/pest --group=integration
```

Or via WP-CLI:

```bash
wp pest test all
wp pest test unit
wp pest test integration
```

### 4. Write Your First Test

Create `tests/Integration/MyFirstTest.php`:

```php
<?php

if (isUnitTest()) {
    return;
}

test('creates a post successfully', function () {
    $postId = factory()::post([
        'post_title' => 'My First Test Post',
        'post_status' => 'publish',
    ]);

    assertPostExists($postId);
    assertPostHasStatus($postId, 'publish');

    expect(get_post($postId)->post_title)->toBe('My First Test Post');
});

test('admin can access settings', function () {
    actingAsAdmin();

    assertAuthenticated();
    assertUserCan('manage_options');
});
```

### 5. See Results

```
   PASS  Tests\Integration\MyFirstTest
  ✓ creates a post successfully
  ✓ admin can access settings

  Tests:  2 passed
  Time:   0.14s
```

### 6. (Optional) Set Up CI/CD

Copy the GitHub Actions workflow stub:

```bash
mkdir -p .github/workflows
cp vendor/jakehenshall/pest-plugin-wordpress/stubs/.github-workflows-tests.yml.stub .github/workflows/tests.yml
```

Edit the workflow and replace `{{PLUGIN_SLUG}}` with your plugin slug.

**Also available:**
- `.gitignore.stub` - Ignore test files and WordPress core
- `phpstan.neon.stub` - PHPStan configuration
- `composer.json.stub` - Example project structure

## Common Testing Patterns

### Setup and Teardown

Tests automatically clean up after themselves, but you can add custom setup:

```php
beforeEach(function () {
    $this->userId = factory()::user(['role' => 'editor']);
    actingAs($this->userId);
});

afterEach(function () {
    // Custom cleanup if needed
});
```

### Shared Data with Datasets

```php
dataset('user_roles', [
    'admin' => ['administrator'],
    'editor' => ['editor'],
    'author' => ['author'],
]);

test('user can edit posts', function ($role) {
    $userId = factory()::user(['role' => $role]);
    actingAs($userId);

    assertUserCan('edit_posts');
})->with('user_roles');
```

### Testing Custom REST Endpoints

```php
test('custom REST endpoint works', function () {
    register_rest_route('my-plugin/v1', '/data', [
        'methods' => 'GET',
        'callback' => fn() => ['data' => 'value'],
    ]);

    restGet('/my-plugin/v1/data')
        ->assertOk()
        ->assertJsonPath('data', 'value');
});
```

## Troubleshooting

### Tests Won't Run?

```bash
# Ensure WordPress is downloaded
ls -la wp/

# Re-run setup if needed
vendor/bin/wp-pest setup plugin --plugin-slug=your-plugin
```

### Autoload Issues?

```bash
# Regenerate autoload files
composer dump-autoload
```

### Permission Errors?

```bash
# Make bin executable
chmod +x vendor/bin/wp-pest
```

## Features Overview

### 🌐 Browser Testing (NEW in v4)

Test WordPress in real browsers with Playwright-powered browser testing:

```php
test('admin can create post in block editor', function () {
    browserLoginAsAdmin();

    $page = visitNewPost();

    $page->type('.editor-post-title__input', 'My New Post');

    publishPost($page);

    assertPostPublished($page);
})->group('browser');
```

**Browser Testing Features:**

- Test WordPress admin UI interactions
- Test block editor (Gutenberg)
- Test frontend themes
- Test WooCommerce checkout flows
- Test contact forms
- Multi-device testing (mobile, tablet, desktop)
- Dark/light mode testing
- Visual regression testing
- Smoke testing

**Installation:**

```bash
composer require pestphp/pest-plugin-browser --dev
npm install playwright@latest
npx playwright install
```

**Available Functions:**

```php
// Navigation
visitWordPress('/');              // Visit any WordPress page
visitAdmin('index.php');          // Visit admin page
visitBlockEditor($postId);        // Open block editor
visitNewPost('post');             // New post editor
visitLogin();                     // Login page

// Authentication
browserLoginAs('username', 'password');
browserLoginAsAdmin();
browserLoginAsUser($userId, 'password');
browserLogout();

// Block Editor
addGutenbergBlock($page, 'core/paragraph');
publishPost($page);
saveDraft($page);
updatePost($page);

// WooCommerce
visitWooCommerceProduct($productId);
visitWooCommerceCart();
visitWooCommerceCheckout();
addToCart($page);
fillCheckoutForm($page, $data);
placeOrder($page);

// Assertions
assertLoggedInAs($page, 'admin');
assertCanSeeAdminBar($page);
assertInBlockEditor($page);
assertPostPublished($page);
assertOrderComplete($page);
assertNoWordPressErrors($page);

// Device & Theme
onMobile($page);
onTablet($page);
onDesktop($page);
inDarkMode($page);
inLightMode($page);

// Screenshots & Debugging
screenshotAs($page, 'checkout-complete');
```

**Browser Testing Examples:**

```php
// Test admin dashboard
test('dashboard loads without errors', function () {
    browserLoginAsAdmin();

    visitAdmin('index.php')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
})->group('browser');

// Test Gutenberg block editor
test('can add paragraph block', function () {
    browserLoginAsAdmin();

    $page = visitNewPost();
    $page->type('.editor-post-title__input', 'Test Post');

    addGutenbergBlock($page, 'core/paragraph');

    assertInBlockEditor($page);
})->group('browser');

// Test WooCommerce checkout
test('customer can complete checkout', function () {
    skipIfWooCommerceNotActive();

    $productId = factory()::post([
        'post_type' => 'product',
        'post_title' => 'Test Product',
    ]);

    update_post_meta($productId, '_price', '29.99');

    $page = visitWooCommerceProduct($productId);
    $page = addToCart($page);

    $page = visitWooCommerceCheckout();
    $page = fillCheckoutForm($page, [
        'billing_email' => 'customer@example.com',
    ]);

    $page = placeOrder($page);

    assertOrderComplete($page);
})->group('browser', 'woocommerce');

// Test responsive design
test('homepage works on mobile', function () {
    visitWordPress('/')
        ->on()->mobile()
        ->assertSee('Welcome')
        ->assertNoJavascriptErrors();
})->group('browser');

// Smoke testing
test('critical pages have no errors', function () {
    $routes = ['/', '/about', '/contact', '/shop'];

    visit($routes)->assertNoSmoke();
})->group('browser', 'smoke');
```

### 🎯 Test Sharding (NEW in v4)

Split your test suite across multiple processes for faster CI/CD:

```bash
# Split tests into 4 shards
vendor/bin/pest --shard=1/4
vendor/bin/pest --shard=2/4
vendor/bin/pest --shard=3/4
vendor/bin/pest --shard=4/4

# Combine with parallel execution
vendor/bin/pest --shard=1/4 --parallel
```

**GitHub Actions Example:**

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: ["8.3", "8.4"]
        shard: [1, 2, 3, 4]

    name: Tests (PHP ${{ matrix.php }}, Shard ${{ matrix.shard }}/4)

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: sqlite3

      - name: Install Dependencies
        run: composer install

      - name: Setup WordPress
        run: |
          vendor/bin/wp-pest setup plugin \
            --plugin-slug=my-plugin \
            --skip-delete

      - name: Run Tests
        run: vendor/bin/pest --parallel --shard=${{ matrix.shard }}/4
```

**Performance Tips:**

1. **Optimal Shard Count**: Start with 4 shards, adjust based on test suite size
2. **Browser Tests**: Use sharding for browser tests as they're slower
3. **Parallel + Sharding**: Combine both for maximum speed
4. **CI Resources**: Match shard count to available CI workers

```bash
# Fast local development (no browser tests)
vendor/bin/pest --exclude-group=browser --parallel

# Full CI run with sharding
vendor/bin/pest --shard=1/4 --parallel
```

### ⏭️ Skip Helpers (NEW in v4)

Skip tests conditionally based on environment:

```php
// Skip locally or on CI
test('browser test', function () {
    skipBrowserTestsLocally(); // Skip slow tests in development

    browserLoginAsAdmin();
    visitAdmin('index.php');
})->group('browser');

test('external API test', function () {
    skipExternalApiTestsOnCi(); // Skip on CI if no API keys

    $response = wp_remote_get('https://api.example.com');
})->group('api');

// Skip based on environment
test('multisite test', function () {
    skipIfNotMultisite();

    $blogId = createBlog('test.example.com');
})->group('multisite');

// Skip based on plugins
test('woocommerce feature', function () {
    skipIfWooCommerceNotActive();

    // Test WooCommerce
})->group('woocommerce');

// Skip based on WordPress/PHP version
test('requires WP 6.4+', function () {
    skipIfWordPressVersion('<', '6.4');

    // Test feature
});

// Aliases for readability
test('multisite only', function () {
    onlyInMultisite();

    // Test runs only in multisite
})->group('multisite');

test('CI only', function () {
    onlyOnCi();

    // Test runs only on CI
})->group('ci');
```

**Available Skip Helpers:**

```php
// Environment
skipLocally()                    // Pest v4 built-in
skipOnCi()                       // Pest v4 built-in
skipBrowserTestsLocally()
skipBrowserTestsOnCi()
skipExternalApiTestsLocally()
skipExternalApiTestsOnCi()
skipLongRunningTestsLocally()
skipLongRunningTestsOnCi()

// WordPress Environment
skipIfMultisite()
skipIfNotMultisite()
skipIfRestApiDisabled()
skipIfGutenbergNotAvailable()

// Plugins
skipIfPluginNotActive($plugin)
skipIfPluginActive($plugin)
skipIfWooCommerceNotActive()
skipIfYoastNotActive()
skipIfAcfNotActive()

// Versions
skipIfPhpVersion($operator, $version)
skipIfWordPressVersion($operator, $version)

// Aliases
onlyInMultisite()
onlyInSingleSite()
onlyWithPlugin($plugin)
onlyOnCi()
onlyLocally()

// Platform
skipOnWindows()
skipOnMac()
skipOnLinux()
```

### ✅ New WordPress Expectations (Pest v4)

New chainable expectations for WordPress:

```php
// Validate WordPress concepts
expect('my-post-slug')->toBeSlug();
expect('publish')->toBeValidPostStatus();
expect('administrator')->toBeValidUserRole();
expect('manage_options')->toBeValidCapability();
expect('post')->toBeValidPostType();
expect('category')->toBeValidTaxonomy();

// Post assertions
expect($postId)->toBePublished();
expect($postId)->toHavePostMeta('_thumbnail_id', 123);

// User assertions
expect($userId)->toHaveUserRole('editor');
expect($userId)->toHaveCapability('edit_posts');

// WP_Error assertions
expect($result)->toBeWordPressError();
expect($error)->toHaveErrorCode('invalid_username');

// Examples
test('validates post data', function () {
    $slug = 'my-awesome-post';
    $status = 'publish';

    expect($slug)->toBeSlug();
    expect($status)->toBeValidPostStatus();

    $postId = factory()::post([
        'post_name' => $slug,
        'post_status' => $status,
    ]);

    expect($postId)->toBePublished();
});

test('validates user permissions', function () {
    $userId = factory()::user(['role' => 'editor']);

    expect($userId)->toHaveUserRole('editor');
    expect($userId)->toHaveCapability('edit_posts');
    expect($userId)->not->toHaveCapability('manage_options');
});

test('handles WordPress errors', function () {
    $result = wp_insert_post([
        'post_title' => '',  // Invalid
    ]);

    expect($result)->toBeWordPressError();
    expect($result)->toHaveErrorCode('empty_content');
});
```

### 🏭 Factory Functions

Create WordPress entities with one line:

```php
// Posts
$postId = factory()::post(['post_title' => 'Test Post']);
$postIds = factory()::posts(5);

// Users
$userId = factory()::user(['role' => 'editor']);
$adminId = factory()::user(['role' => 'administrator']);

// Terms
$categoryId = factory()::term('Technology', 'category');
$tagIds = factory()::terms(5, 'post_tag');

// Comments
$commentId = factory()::comment($postId, ['comment_content' => 'Great!']);

// Attachments
$attachmentId = factory()::attachment(['post_mime_type' => 'image/jpeg']);
```

### 🔐 Authentication

Switch between users effortlessly:

```php
// Act as different roles
actingAsAdmin();
actingAsEditor();
actingAsGuest();

// Act as specific user
$user = actingAs($userId);

// Assertions
assertAuthenticated();
assertNotAuthenticated();
assertUserCan('manage_options');
assertUserCannot('edit_posts');
```

### 🌐 HTTP Testing

Test HTTP requests with fluent assertions:

```php
get('/')
    ->assertOk()
    ->assertSee('Welcome');

post('/wp-admin/admin-ajax.php', ['action' => 'my_action'])
    ->assertStatus(200)
    ->assertSee('success');

from('https://google.com')
    ->get('/page')
    ->assertOk();
```

### 🎭 HTTP Mocking

Mock external API calls:

```php
fakeHttp('https://api.example.com/*', [
    'body' => json_encode(['data' => 'mocked']),
    'response' => ['code' => 200],
]);

$response = wp_remote_get('https://api.example.com/users');

assertHttpSent('https://api.example.com/*');
assertHttpSentCount('https://api.example.com/*', 1);

// Prevent unexpected requests
preventStrayRequests();
```

### 📧 Email Testing

Intercept and test emails:

```php
fakeEmail();

wp_mail('user@example.com', 'Welcome!', 'Thanks for signing up');

assertEmailSent('user@example.com');
assertEmailSentCount(1);
assertEmailSentTo(['user1@example.com', 'user2@example.com']);
```

### ⏰ Cron/Scheduled Events

Test scheduled tasks:

```php
wp_schedule_event(time(), 'daily', 'my_cleanup_task');

assertCronScheduled('my_cleanup_task');

runCron('my_cleanup_task');
runAllCrons();
runDueCrons();

clearAllCrons();
```

### 🗄️ Database Testing

Direct database assertions:

```php
assertDatabaseHas('posts', [
    'post_title' => 'Test Post',
    'post_status' => 'publish',
]);

assertDatabaseMissing('posts', ['post_status' => 'trash']);
assertDatabaseCount('posts', 10);

truncateTable('postmeta');
seedTable('posts', [['post_title' => 'Seeded Post']]);
```

### 🔌 REST API Testing

Fluent REST API testing:

```php
restGet('/wp/v2/posts')
    ->assertOk()
    ->assertJsonCount(10)
    ->assertJsonPath('0.title.rendered', 'Post Title');

$userId = actingAsEditor()->ID;

restPost('/wp/v2/posts', [
    'title' => 'New Post',
    'status' => 'publish',
], $userId)
    ->assertCreated()
    ->assertJsonPath('title.rendered', 'New Post');
```

### 🔌 Plugin Compatibility

Test with popular plugins:

```php
withYoast(function () {
    // Test with Yoast SEO active
    expect(function_exists('wpseo_init'))->toBeTrue();
});

withWooCommerce(function () {
    // Test WooCommerce integration
    $productId = factory()::post(['post_type' => 'product']);
    assertPostExists($productId);
});

withAcf(function () {
    // Test ACF integration
    update_field('my_field', 'value', $postId);
});

withPlugin('contact-form-7/wp-contact-form-7.php', function () {
    // Test with any plugin
});
```

### 🌍 Multisite Support

Test multisite networks:

```php
assertMultisite();

$blogId = createBlog('testsite.example.com', '/');
assertBlogExists($blogId);

switchToBlog($blogId);
// Run tests in blog context
restoreCurrentBlog();

deleteBlog($blogId);
```

### 📁 File Upload Testing

Fake file uploads:

```php
$image = fakeImage('photo.jpg', 1920, 1080);
$file = fakeUpload('document.pdf', 'content', 'application/pdf');

assertFileUploaded($image['id']);
assertImageSize($image['id'], 'thumbnail');
```

### ⏱️ Time Travel

Freeze and manipulate time:

```php
freezeTime(strtotime('2024-01-01 00:00:00'));

// Your time-dependent code

travelInTime(3600); // Move 1 hour forward
travelToTime(strtotime('2025-12-31'));

restoreTime();
```

### 💾 Cache Testing

Test caching behaviour:

```php
assertCached('my_key', 'my_group');
assertNotCached('expired_key');

assertTransient('my_transient');
assertNoTransient('deleted_transient');

flushCache();
```

### ⚡ AJAX Testing

Test AJAX handlers:

```php
callAjax('my_action', ['key' => 'value'], true)
    ->assertSuccess()
    ->assertJsonPath('data.id', 123);

callAjax('public_action', [], false)
    ->assertFailed();
```

### 🔀 Redirect Testing

Capture and test redirects:

```php
captureRedirects();

// Code that redirects
wp_redirect('/success');

assertRedirected('/success');
assertRedirectStatus(302);
assertRedirectContains('?message=saved');
```

### 🧱 Gutenberg Blocks

Test block editor:

```php
registerBlock('my-plugin/custom-block');

assertBlockRegistered('core/paragraph');
assertHasBlock('core/paragraph', $content);
assertBlockCount(5, $postContent);

$output = renderBlock('core/paragraph', [
    'content' => 'Hello World'
]);
```

### 📢 Admin Notices

Test admin UI:

```php
captureAdminNotices();

add_settings_error('general', 'settings_updated', 'Settings saved', 'success');

assertAdminNotice('Settings saved');
assertAdminNoticeType('success');
```

### 🧭 Navigation Menus

Test menus:

```php
$menuId = createMenu('Primary Menu', 'primary');
addMenuItem($menuId, [
    'menu-item-title' => 'Home',
    'menu-item-url' => home_url('/'),
]);

assertMenuExists('Primary Menu');
assertMenuHasItems($menuId, 5);
```

### 📦 Widgets

Test widgets and sidebars:

```php
registerWidget(MyCustomWidget::class);
addWidgetToSidebar('sidebar-1', 'my_widget', ['title' => 'Widget']);

assertWidgetRegistered(MyCustomWidget::class);
assertSidebarExists('sidebar-1');
assertSidebarHasWidgets('sidebar-1', 3);
```

### ✅ WordPress Assertions

40+ WordPress-specific assertions:

```php
// Posts
assertPostExists($postId);
assertPostHasStatus($postId, 'publish');
assertPostHasMeta($postId, 'key', 'value');
assertPostHasTerm($postId, $termId, 'category');

// Terms
assertTermExists($termId, 'category');

// Users
assertUserExists($userId);
assertUserHasRole($userId, 'editor');

// Options
assertOptionExists('my_setting');
assertOptionEquals('my_setting', 'value');

// Hooks
assertHookAdded('init', 'my_function');
assertFilterAdded('the_content', 'my_filter');

// Post Types & Taxonomies
assertPostTypeExists('book');
assertTaxonomyExists('genre');
assertShortcodeExists('my_shortcode');

// Queries
assertQueryHasPosts($query);
assertQueryPostCount($query, 5);

// Assets
assertEnqueued('my-script', 'script');
assertEnqueued('my-style', 'style');

// Plugins
assertPluginActive('plugin/plugin.php');
assertPluginInactive('inactive-plugin/plugin.php');
```

## Complete Example

Here's a comprehensive test showing multiple features:

```php
<?php

test('complete e-commerce flow', function () {
    // Setup admin user
    $admin = actingAsAdmin();

    // Create products
    $productId = factory()::post([
        'post_type' => 'product',
        'post_title' => 'Test Product',
    ]);

    // Fake payment gateway API
    fakeHttp('https://payment-gateway.com/api/*', [
        'body' => json_encode(['status' => 'approved']),
        'response' => ['code' => 200],
    ]);

    // Fake email notifications
    fakeEmail();

    // Test REST API
    restGet("/wp/v2/products/{$productId}", [], $admin->ID)
        ->assertOk()
        ->assertJsonPath('title.rendered', 'Test Product');

    // Process order (triggers email)
    do_action('order_completed', $orderId);

    // Verify email sent
    assertEmailSent($admin->user_email);

    // Verify API called
    assertHttpSent('https://payment-gateway.com/api/*');

    // Verify database
    assertDatabaseHas('posts', [
        'ID' => $productId,
        'post_type' => 'product',
    ]);
});
```

## Setup Options

### Command Line Options

```bash
vendor/bin/wp-pest setup [project-type] [options]
```

**Arguments:**

- `project-type` - Either `plugin` or `theme`

**Options:**

- `--wp-version[=VERSION]` - WordPress version to test against (default: `latest`)
- `--plugin-slug[=SLUG]` - Plugin slug (required for plugins)
- `--skip-delete` - Skip cleanup (useful for CI)

### Examples

```bash
# Plugin with specific WP version
vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --wp-version=6.4

# Theme setup
vendor/bin/wp-pest setup theme

# CI environment
vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --skip-delete
```

## Running in CI/CD

### GitHub Actions with Test Sharding

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      fail-fast: false
      matrix:
        php: ["8.3", "8.4"]
        wordpress: ["latest", "6.4", "6.5"]
        shard: [1, 2, 3, 4]

    name: PHP ${{ matrix.php }} - WP ${{ matrix.wordpress }} - Shard ${{ matrix.shard }}/4

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: sqlite3
          coverage: none

      - name: Install Composer Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Setup WordPress Tests
        run: |
          vendor/bin/wp-pest setup plugin \
            --plugin-slug=my-plugin \
            --wp-version=${{ matrix.wordpress }} \
            --skip-delete

      - name: Run Tests
        run: vendor/bin/pest --parallel --shard=${{ matrix.shard }}/4

  browser-tests:
    runs-on: ubuntu-latest

    strategy:
      fail-fast: false
      matrix:
        shard: [1, 2]

    name: Browser Tests - Shard ${{ matrix.shard }}/2

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
          composer install --prefer-dist --no-progress
          composer require pestphp/pest-plugin-browser --dev
          npm install playwright@latest
          npx playwright install --with-deps

      - name: Setup WordPress Tests
        run: |
          vendor/bin/wp-pest setup plugin \
            --plugin-slug=my-plugin \
            --skip-delete

      - name: Start WordPress Server
        run: |
          cd wp && php -S localhost:8080 &
          sleep 5

      - name: Run Browser Tests
        run: vendor/bin/pest --group=browser --shard=${{ matrix.shard }}/2

      - name: Upload Screenshots
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: browser-screenshots-${{ matrix.shard }}
          path: tests/screenshots/
```

### GitLab CI with Test Sharding

```yaml
variables:
  MYSQL_ROOT_PASSWORD: root
  WP_VERSION: latest

stages:
  - test

.test-template: &test-template
  image: php:8.3
  before_script:
    - apt-get update && apt-get install -y sqlite3 libsqlite3-dev
    - composer install --prefer-dist --no-progress
    - vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --skip-delete

test:shard-1:
  <<: *test-template
  stage: test
  script:
    - vendor/bin/pest --parallel --shard=1/4

test:shard-2:
  <<: *test-template
  stage: test
  script:
    - vendor/bin/pest --parallel --shard=2/4

test:shard-3:
  <<: *test-template
  stage: test
  script:
    - vendor/bin/pest --parallel --shard=3/4

test:shard-4:
  <<: *test-template
  stage: test
  script:
    - vendor/bin/pest --parallel --shard=4/4

browser-tests:
  image: mcr.microsoft.com/playwright:v1.40.0-focal
  stage: test
  before_script:
    - apt-get update && apt-get install -y php8.3 php8.3-sqlite3 composer
    - composer install
    - composer require pestphp/pest-plugin-browser --dev
    - npm install playwright@latest
  script:
    - vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --skip-delete
    - php -S localhost:8080 -t wp &
    - sleep 5
    - vendor/bin/pest --group=browser
```

### CircleCI with Test Sharding

```yaml
version: 2.1

jobs:
  test:
    parameters:
      php-version:
        type: string
      shard:
        type: integer
      total-shards:
        type: integer

    docker:
      - image: cimg/php:<< parameters.php-version >>

    steps:
      - checkout

      - run:
          name: Install Dependencies
          command: |
            composer install --no-progress

      - run:
          name: Setup WordPress
          command: |
            vendor/bin/wp-pest setup plugin \
              --plugin-slug=my-plugin \
              --skip-delete

      - run:
          name: Run Tests
          command: |
            vendor/bin/pest \
              --parallel \
              --shard=<< parameters.shard >>/<< parameters.total-shards >>

workflows:
  test:
    jobs:
      - test:
          matrix:
            parameters:
              php-version: ["8.3", "8.4"]
              shard: [1, 2, 3, 4]
              total-shards: [4]
```

### Performance Optimization Tips

**1. Optimal Sharding Strategy:**

```bash
# Small test suite (< 100 tests)
vendor/bin/pest --parallel

# Medium test suite (100-500 tests)
vendor/bin/pest --parallel --shard=1/2

# Large test suite (500+ tests)
vendor/bin/pest --parallel --shard=1/4

# Very large suite with browser tests (1000+ tests)
vendor/bin/pest --parallel --shard=1/8
```

**2. Separate Browser Tests:**

```yaml
# Run unit/integration tests with high parallelism
jobs:
  unit-tests:
    strategy:
      matrix:
        shard: [1, 2, 3, 4, 5, 6, 7, 8]
    steps:
      - run: vendor/bin/pest --exclude-group=browser --shard=${{ matrix.shard }}/8

  # Run browser tests separately with fewer shards
  browser-tests:
    strategy:
      matrix:
        shard: [1, 2]
    steps:
      - run: vendor/bin/pest --group=browser --shard=${{ matrix.shard }}/2
```

**3. Skip Slow Tests Locally:**

```php
// In tests/Pest.php
uses()->group('browser')->in('Browser');
uses()->group('slow')->in('Slow');

// Run only fast tests locally
// vendor/bin/pest --exclude-group=browser --exclude-group=slow
```

**4. Cache Dependencies:**

```yaml
# GitHub Actions
- name: Cache Composer
  uses: actions/cache@v4
  with:
    path: vendor
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}

- name: Cache WordPress
  uses: actions/cache@v4
  with:
    path: wp
    key: ${{ runner.os }}-wp-${{ matrix.wordpress }}
```

### Local Development Workflow

```bash
# Fast feedback loop (skip slow tests)
vendor/bin/pest --exclude-group=browser --exclude-group=slow

# Test specific feature
vendor/bin/pest tests/Feature/MyFeatureTest.php

# Run with coverage (slower)
vendor/bin/pest --coverage

# Full test suite before pushing
vendor/bin/pest --parallel
```

## Running in CI/CD (Legacy)

### Simple GitHub Actions (No Sharding)

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: ["8.3", "8.4"]
        wordpress: ["latest", "6.4"]

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: sqlite3

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Setup WordPress Tests
        run: |
          vendor/bin/wp-pest setup plugin \
            --plugin-slug=my-plugin \
            --wp-version=${{ matrix.wordpress }} \
            --skip-delete

      - name: Run Tests
        run: vendor/bin/pest
```

### GitLab CI

```yaml
test:
  image: php:8.3
  before_script:
    - apt-get update && apt-get install -y sqlite3 libsqlite3-dev
    - composer install
  script:
    - vendor/bin/wp-pest setup plugin --plugin-slug=my-plugin --skip-delete
    - vendor/bin/pest
```

## Project Structure

After running setup, your project will have:

```
your-plugin/
├── .github/                  # (Optional) Copy from stubs
│   └── workflows/
│       └── tests.yml         # CI/CD workflow
├── .gitignore                # (Optional) Copy from stubs
├── composer.json             # Your project dependencies
├── phpstan.neon              # (Optional) Copy from stubs
├── phpstan-baseline.neon     # (Optional) Generated baseline
├── tests/
│   ├── Pest.php              # Pest configuration
│   ├── Helpers.php           # Helper functions
│   ├── bootstrap/
│   │   ├── integration.php   # Integration test bootstrap
│   │   ├── unit.php          # Unit test bootstrap
│   │   └── wp-tests-config.php
│   ├── Unit/
│   │   └── ExampleTest.php
│   └── Integration/
│       └── ExampleTest.php
├── phpunit.xml               # PHPUnit configuration
└── wp/                       # WordPress installation
    ├── src/                  # WordPress core
    └── tests/                # WordPress test suite
```

### Available Stubs

All stubs are located in `vendor/jakehenshall/pest-plugin-wordpress/stubs/`:

**Test Files:**
- `ExampleUnitTest.php.stub`
- `ExampleIntegrationTest.php.stub`
- `ExampleBrowserTest.php.stub`
- `ExampleWooCommerceBrowserTest.php.stub`

**Configuration:**
- `Pest.php.stub` - Pest configuration
- `phpunit.xml.stub` - PHPUnit configuration
- `phpstan.neon.stub` - PHPStan configuration
- `phpstan-baseline.neon.stub` - PHPStan baseline
- `wp-tests-config.php.stub` - WordPress test config

**Bootstrap:**
- `bootstrap-unit.php.stub`
- `bootstrap-integration.php.stub`
- `bootstrap-integration-universal.php.stub`

**Project Setup:**
- `composer.json.stub` - Example composer.json
- `.gitignore.stub` - Ignore test artifacts
- `.github-workflows-tests.yml.stub` - GitHub Actions CI/CD

**Helpers:**
- `Helpers.php.stub` - Custom helper functions

Copy any stub to your project:
```bash
cp vendor/jakehenshall/pest-plugin-wordpress/stubs/phpstan.neon.stub phpstan.neon
```

## Advanced Testing Examples

### Testing WooCommerce Integration

```php
test('complete e-commerce flow', function () {
    withWooCommerce(function () {
        $admin = actingAsAdmin();

        // Create products
        $productIds = factory()::posts(5, [
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Fake payment gateway API
        fakeHttp('https://payment-gateway.com/api/*', [
            'body' => json_encode(['status' => 'approved']),
            'response' => ['code' => 200],
        ]);

        // Test REST API
        restGet('/wc/v3/products', [], $admin->ID)
            ->assertOk()
            ->assertJsonCount(5);

        // Verify API called
        assertHttpSent('https://payment-gateway.com/api/*');
    });
});
```

### Testing Custom Post Types with ACF

```php
test('custom post type with ACF fields', function () {
    withAcf(function () {
        register_post_type('book', ['public' => true]);

        $bookId = factory()::post([
            'post_type' => 'book',
            'post_title' => 'The Great Gatsby',
        ]);

        update_field('isbn', '978-0-7432-7356-5', $bookId);
        update_field('author', 'F. Scott Fitzgerald', $bookId);

        assertPostHasMeta($bookId, 'isbn', '978-0-7432-7356-5');

        restGet("/wp/v2/book/{$bookId}")
            ->assertOk()
            ->assertJsonPath('title.rendered', 'The Great Gatsby');
    });
});
```

### Testing Email Workflows

```php
test('newsletter subscription flow', function () {
    fakeEmail();

    $email = 'subscriber@example.com';

    // Schedule verification email
    scheduleCron('send_verification_email', time(), ['email' => $email]);
    runCron('send_verification_email');

    assertEmailSent($email, function ($email) {
        return str_contains($email['subject'], 'Verify');
    });

    assertDatabaseHas('subscribers', [
        'email' => $email,
        'status' => 'pending',
    ]);
});
```

### Testing External APIs with Caching

```php
test('external API with caching', function () {
    fakeHttp('https://api.weather.com/forecast/*', [
        'body' => json_encode(['temperature' => 22, 'conditions' => 'sunny']),
        'response' => ['code' => 200],
    ]);

    $response = wp_remote_get('https://api.weather.com/forecast/london');
    $data = json_decode(wp_remote_retrieve_body($response), true);

    set_transient('weather_london', $data, HOUR_IN_SECONDS);

    assertHttpSentCount('https://api.weather.com/forecast/*', 1);

    // Second request from cache - no additional API call
    $cached = get_transient('weather_london');
    expect($cached)->toBe($data);

    assertHttpSentCount('https://api.weather.com/forecast/*', 1);
});
```

### Testing Role-Based Permissions

```php
test('role-based content access', function () {
    $privatePostId = factory()::post([
        'post_status' => 'private',
        'post_title' => 'Private Content',
    ]);

    // Guest cannot access
    actingAsGuest();
    restGet("/wp/v2/posts/{$privatePostId}")
        ->assertNotFound();

    // Editor can access
    $editor = actingAsEditor();
    restGet("/wp/v2/posts/{$privatePostId}", [], $editor->ID)
        ->assertOk()
        ->assertJsonPath('title.rendered', 'Private Content');
});
```

### Testing Background Processing

```php
test('background batch processing', function () {
    $postIds = factory()::posts(100);

    foreach (array_chunk($postIds, 10) as $batch) {
        scheduleCron('process_batch', time(), ['post_ids' => $batch]);
    }

    runAllCrons();

    foreach ($postIds as $postId) {
        assertPostHasMeta($postId, '_processed', '1');
    }
});
```

## Documentation

All documentation is now contained in this README for your convenience.

## Comparison with Alternatives

| Feature             | This Package | WP PHPUnit | Brain Monkey |
| ------------------- | ------------ | ---------- | ------------ |
| Pest PHP v4         | ✅           | ❌         | ❌           |
| PHPStan Built-in    | ✅           | ❌         | ❌           |
| SQLite Built-in     | ✅           | ❌         | N/A          |
| Laravel-Style       | ✅           | ❌         | ❌           |
| Browser Testing     | ✅           | ❌         | ❌           |
| Test Sharding       | ✅           | ❌         | ❌           |
| Skip Helpers        | ✅           | ❌         | ❌           |
| Custom Expectations | ✅           | ❌         | ❌           |
| Zero Config         | ✅           | ❌         | ❌           |
| HTTP Testing        | ✅           | ⚠️         | ❌           |
| Email Testing       | ✅           | ❌         | ❌           |
| Time Travel         | ✅           | ❌         | ❌           |
| AJAX Testing        | ✅           | ❌         | ❌           |
| Block Testing       | ✅           | ❌         | ❌           |
| Plugin Tests        | ✅           | ✅         | ❌           |
| WP-CLI              | ✅           | ❌         | ❌           |
| **Functions**       | **150+**     | ~40        | ~20          |
| **Setup Required**  | **Minimal**  | Complex    | Manual       |

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- Built on [Pest PHP](https://pestphp.com/)
- Inspired by [Laravel Testing](https://laravel.com/docs/testing)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

- **Issues**: [GitHub Issues](https://github.com/jakehenshall/pest-plugin-wordpress/issues)
- **Discussions**: [GitHub Discussions](https://github.com/jakehenshall/pest-plugin-wordpress/discussions)

---

Made with ❤️ for the WordPress community
