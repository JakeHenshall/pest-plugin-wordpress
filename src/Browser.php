<?php

declare(strict_types=1);

namespace PestPluginWordPress;

use function Pest\browser;

/**
 * Browser testing helpers for WordPress
 * Integrates with Pest Browser plugin for testing WordPress admin and frontend
 * 
 * Features:
 * - WordPress navigation (visitAdmin, visitLogin, visitWordPress, visit)
 * - Authentication (browserLoginAsAdmin, browserLoginAs, browserLoginAsUser, browserLogout)
 * - Block Editor (Gutenberg) helpers (visitBlockEditor, insertBlock, publishPost, etc.)
 * - Admin area helpers (visitPluginsPage, visitSettingsPage, createNewUser, etc.)
 * - WooCommerce helpers (visitWooCommerceProduct, addToCart, placeOrder, etc.)
 * - Responsive testing (onMobile, onTablet, onDesktop)
 * - Dark mode testing (inDarkMode, inLightMode)
 * - Assertions (assertLoggedInAs, assertInBlockEditor, assertPostPublished, etc.)
 * - Screenshots (takeScreenshot, screenshotAs)
 * - Smoke testing (assertNoSmoke, assertNoJavascriptErrors)
 * - Form interactions (fillField, selectOption, checkCheckbox, etc.)
 * - Navigation controls (goBack, goForward, refreshPage)
 * - Element interactions (clickElement, hoverElement, waitForElement, etc.)
 * 
 * @requires pestphp/pest-plugin-browser
 * @requires Playwright (npm install playwright@latest && npx playwright install)
 */

/**
 * Visit a WordPress page (frontend or admin)
 */
function visitWordPress(string $path = ''): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    return browser()->visit('/' . ltrim($path, '/'));
}

/**
 * Visit one or multiple WordPress pages
 */
function visit(string|array $path): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    if (is_array($path)) {
        $results = [];
        foreach ($path as $p) {
            $results[] = browser()->visit('/' . ltrim($p, '/'));
        }
        return $results;
    }
    
    return browser()->visit('/' . ltrim($path, '/'));
}

/**
 * Visit a WordPress admin page
 */
function visitAdmin(string $path = ''): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    $url = '/wp-admin/' . ltrim($path, '/');
    return browser()->visit($url);
}

/**
 * Visit the WordPress login page
 */
function visitLogin(): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    return browser()->visit('/wp-login.php');
}

/**
 * Login to WordPress admin via browser
 */
function browserLoginAsAdmin(string $username = 'admin', string $password = 'password'): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    $page = browser()->visit('/wp-login.php');
    $page->fill('log', $username)
        ->fill('pwd', $password)
        ->click('wp-submit');
    
    return $page;
}

/**
 * Login to WordPress via browser (alias for browserLoginAsAdmin)
 */
function browserLoginAs(string $username, string $password): mixed
{
    return browserLoginAsAdmin($username, $password);
}

/**
 * Login to WordPress as a specific user via browser
 */
function browserLoginAsUser(int $userId, string $password): mixed
{
    $user = get_userdata($userId);
    
    if (!$user) {
        throw new \RuntimeException("User with ID {$userId} does not exist");
    }
    
    return browserLoginAsAdmin($user->user_login, $password);
}

/**
 * Logout from WordPress via browser
 */
function browserLogout(): mixed
{
    if (!function_exists('Pest\browser')) {
        throw new \RuntimeException('Browser testing requires pestphp/pest-plugin-browser. Install it with: composer require --dev pestphp/pest-plugin-browser');
    }
    
    $page = browser()->visit('/wp-login.php?action=logout');
    $page->click('a');
    
    return $page;
}

/**
 * Assert login was successful
 */
function assertLoginSuccessful(mixed $page): void
{
    $page->assertSee('Dashboard');
}

/**
 * Assert user is logged in as specific user
 */
function assertLoggedInAs(mixed $page, string $username): void
{
    $page->assertSee('Howdy, ' . $username);
}

/**
 * Assert admin bar is visible
 */
function assertCanSeeAdminBar(mixed $page): void
{
    $page->assertSee('wp-admin-bar');
}

/**
 * Visit the block editor for a new post
 */
function visitNewPost(): mixed
{
    return visitAdmin('post-new.php');
}

/**
 * Visit the block editor for an existing post
 */
function visitBlockEditor(int $postId): mixed
{
    return visitAdmin("post.php?post={$postId}&action=edit");
}

/**
 * Assert the block editor (Gutenberg) is loaded
 */
function assertInBlockEditor(mixed $page): void
{
    $page->assertSee('editor-styles-wrapper');
}

/**
 * Assert Gutenberg block editor is loaded
 */
function assertGutenbergLoaded(mixed $page): void
{
    assertInBlockEditor($page);
}

/**
 * Publish a post in the block editor
 */
function publishPost(mixed $page): mixed
{
    $page->click('.editor-post-publish-panel__toggle');
    $page->waitFor('.editor-post-publish-button');
    $page->click('.editor-post-publish-button');
    
    return $page;
}

/**
 * Assert a post was published successfully
 */
function assertPostPublished(mixed $page): void
{
    $page->assertSee('Post published');
}

/**
 * Insert a Gutenberg block
 */
function insertBlock(mixed $page, string $blockName): mixed
{
    $page->click('.block-editor-inserter__toggle')
        ->type('.block-editor-inserter__search-input', $blockName)
        ->click(".block-editor-block-types-list__item-title:contains('{$blockName}')");
    
    return $page;
}

/**
 * Visit the plugins page
 */
function visitPluginsPage(): mixed
{
    return visitAdmin('plugins.php');
}

/**
 * Visit the settings page
 */
function visitSettingsPage(string $page = 'general'): mixed
{
    return visitAdmin("options-{$page}.php");
}

/**
 * Visit the themes page
 */
function visitThemesPage(): mixed
{
    return visitAdmin('themes.php');
}

/**
 * Visit the users page
 */
function visitUsersPage(): mixed
{
    return visitAdmin('users.php');
}

/**
 * Visit the media library
 */
function visitMediaLibrary(): mixed
{
    return visitAdmin('upload.php');
}

/**
 * Set viewport to mobile size
 */
function onMobile(mixed $page): mixed
{
    $page->resize(375, 667);
    return $page;
}

/**
 * Set viewport to tablet size
 */
function onTablet(mixed $page): mixed
{
    $page->resize(768, 1024);
    return $page;
}

/**
 * Set viewport to desktop size
 */
function onDesktop(mixed $page): mixed
{
    $page->resize(1920, 1080);
    return $page;
}

/**
 * Get viewport helper for chaining (e.g. on()->mobile())
 */
function on(): object
{
    return new class {
        public function mobile(): object
        {
            return $this;
        }
        
        public function tablet(): object
        {
            return $this;
        }
        
        public function desktop(): object
        {
            return $this;
        }
    };
}

/**
 * Enable dark mode for testing
 */
function inDarkMode(mixed $page): mixed
{
    $page->emulateMedia('prefers-color-scheme', 'dark');
    return $page;
}

/**
 * Enable light mode for testing
 */
function inLightMode(mixed $page): mixed
{
    $page->emulateMedia('prefers-color-scheme', 'light');
    return $page;
}

/**
 * Assert no JavaScript errors on page
 */
function assertNoJavascriptErrors(mixed $page): void
{
    $page->assertNoJavascriptErrors();
}

/**
 * Assert no console logs on page
 */
function assertNoConsoleLogs(mixed $page): void
{
    $page->assertNoConsoleLogs();
}

/**
 * WooCommerce: Visit a product page
 */
function visitWooCommerceProduct(int $productId): mixed
{
    $permalink = get_permalink($productId);
    return browser()->visit($permalink);
}

/**
 * WooCommerce: Visit the cart page
 */
function visitWooCommerceCart(): mixed
{
    return visitWordPress('cart');
}

/**
 * WooCommerce: Visit the checkout page
 */
function visitWooCommerceCheckout(): mixed
{
    return visitWordPress('checkout');
}

/**
 * WooCommerce: Visit my account page
 */
function visitWooCommerceMyAccount(): mixed
{
    return visitWordPress('my-account');
}

/**
 * WooCommerce: Visit the shop page
 */
function visitWooCommerceShop(): mixed
{
    return visitWordPress('shop');
}

/**
 * WooCommerce: Add product to cart
 */
function addToCart(mixed $page): mixed
{
    $page->click('.single_add_to_cart_button');
    return $page;
}

/**
 * WooCommerce: Assert product is in cart
 */
function assertInCart(mixed $page, string $productName): void
{
    $page->assertSee($productName);
}

/**
 * WooCommerce: Fill checkout form
 */
function fillCheckoutForm(mixed $page, array $data): mixed
{
    foreach ($data as $field => $value) {
        $page->fill("#{$field}", $value);
    }
    
    return $page;
}

/**
 * WooCommerce: Place order
 */
function placeOrder(mixed $page): mixed
{
    $page->click('#place_order');
    return $page;
}

/**
 * WooCommerce: Assert order was completed
 */
function assertOrderComplete(mixed $page): void
{
    $page->assertSee('Thank you. Your order has been received.');
}

/**
 * Take a screenshot with a descriptive name
 */
function takeScreenshot(mixed $page, string $name): void
{
    $page->screenshot($name);
}

/**
 * Take a screenshot with a descriptive name (alias)
 */
function screenshotAs(mixed $page, string $name): void
{
    takeScreenshot($page, $name);
}

/**
 * Wait for a specific element to be visible
 */
function waitForElement(mixed $page, string $selector, int $timeout = 5000): mixed
{
    $page->waitFor($selector, $timeout);
    return $page;
}

/**
 * Wait for text to appear
 */
function waitForText(mixed $page, string $text, int $timeout = 5000): mixed
{
    $page->waitForText($text, $timeout);
    return $page;
}

/**
 * Assert element exists on page
 */
function assertElementExists(mixed $page, string $selector): void
{
    $page->assertSee($selector);
}

/**
 * Assert element does not exist on page
 */
function assertElementMissing(mixed $page, string $selector): void
{
    $page->assertDontSee($selector);
}

/**
 * Click an element by selector
 */
function clickElement(mixed $page, string $selector): mixed
{
    $page->click($selector);
    return $page;
}

/**
 * Type text into an element
 */
function typeInto(mixed $page, string $selector, string $text): mixed
{
    $page->type($selector, $text);
    return $page;
}

/**
 * Fill a form field
 */
function fillField(mixed $page, string $selector, string $value): mixed
{
    $page->fill($selector, $value);
    return $page;
}

/**
 * Select an option from a dropdown
 */
function selectOption(mixed $page, string $selector, string $value): mixed
{
    $page->select($selector, $value);
    return $page;
}

/**
 * Check a checkbox
 */
function checkCheckbox(mixed $page, string $selector): mixed
{
    $page->check($selector);
    return $page;
}

/**
 * Uncheck a checkbox
 */
function uncheckCheckbox(mixed $page, string $selector): mixed
{
    $page->uncheck($selector);
    return $page;
}

/**
 * Assert a checkbox is checked
 */
function assertCheckboxChecked(mixed $page, string $selector): void
{
    $page->assertChecked($selector);
}

/**
 * Assert a checkbox is not checked
 */
function assertCheckboxNotChecked(mixed $page, string $selector): void
{
    $page->assertNotChecked($selector);
}

/**
 * Press a key on the keyboard
 */
function pressKey(mixed $page, string $key): mixed
{
    $page->press($key);
    return $page;
}

/**
 * Hover over an element
 */
function hoverElement(mixed $page, string $selector): mixed
{
    $page->hover($selector);
    return $page;
}

/**
 * Execute JavaScript on the page
 */
function executeScript(mixed $page, string $script): mixed
{
    return $page->script($script);
}

/**
 * Navigate back in browser history
 */
function goBack(mixed $page): mixed
{
    $page->back();
    return $page;
}

/**
 * Navigate forward in browser history
 */
function goForward(mixed $page): mixed
{
    $page->forward();
    return $page;
}

/**
 * Refresh the current page
 */
function refreshPage(mixed $page): mixed
{
    $page->refresh();
    return $page;
}

/**
 * Get the current URL
 */
function getCurrentUrl(mixed $page): string
{
    return $page->url();
}

/**
 * Assert current URL matches
 */
function assertUrl(mixed $page, string $url): void
{
    $page->assertUrl($url);
}

/**
 * Assert current URL contains text
 */
function assertUrlContains(mixed $page, string $text): void
{
    $page->assertUrlContains($text);
}

/**
 * WordPress Admin: Assert admin notice is present
 */
function assertAdminNoticeInBrowser(mixed $page, string $message, string $type = 'success'): void
{
    $page->assertSee($message);
    $page->assertSee('notice-' . $type);
}

/**
 * WordPress Admin: Create a new page
 */
function createNewPage(mixed $page, string $title, string $content = ''): mixed
{
    $page = visitAdmin('post-new.php?post_type=page');
    
    $page->type('.editor-post-title__input', $title);
    
    if ($content) {
        $page->type('.block-editor-default-block-appender__content', $content);
    }
    
    return publishPost($page);
}

/**
 * WordPress Admin: Activate a plugin via browser
 */
function activatePluginInBrowser(mixed $page, string $pluginSlug): mixed
{
    $page = visitPluginsPage();
    $page->click("tr[data-slug='{$pluginSlug}'] .activate a");
    return $page;
}

/**
 * WordPress Admin: Deactivate a plugin via browser
 */
function deactivatePluginInBrowser(mixed $page, string $pluginSlug): mixed
{
    $page = visitPluginsPage();
    $page->click("tr[data-slug='{$pluginSlug}'] .deactivate a");
    return $page;
}

/**
 * WordPress Admin: Switch theme via browser
 */
function switchThemeInBrowser(mixed $page, string $themeSlug): mixed
{
    $page = visitThemesPage();
    $page->click(".theme[data-slug='{$themeSlug}'] .activate");
    return $page;
}

/**
 * WordPress Admin: Upload media file
 */
function uploadMedia(mixed $page, string $filePath): mixed
{
    $page = visitMediaLibrary();
    $page->click('.page-title-action');
    $page->attach('async-upload', $filePath);
    return $page;
}

/**
 * WordPress Admin: Create a new user
 */
function createNewUser(mixed $page, array $userData): mixed
{
    $page = visitAdmin('user-new.php');
    
    $page->fill('#user_login', $userData['username'] ?? 'testuser');
    $page->fill('#email', $userData['email'] ?? 'test@example.com');
    $page->fill('#first_name', $userData['first_name'] ?? '');
    $page->fill('#last_name', $userData['last_name'] ?? '');
    
    if (isset($userData['role'])) {
        $page->select('#role', $userData['role']);
    }
    
    $page->click('#createusersub');
    
    return $page;
}

/**
 * WordPress Admin: Update site settings
 */
function updateSiteSettings(mixed $page, array $settings): mixed
{
    $page = visitSettingsPage();
    
    foreach ($settings as $field => $value) {
        $page->fill("#{$field}", $value);
    }
    
    $page->click('#submit');
    
    return $page;
}

/**
 * Gutenberg: Add paragraph block with text
 */
function addParagraphBlock(mixed $page, string $text): mixed
{
    insertBlock($page, 'Paragraph');
    $page->type('.block-editor-rich-text__editable', $text);
    return $page;
}

/**
 * Gutenberg: Add block by name (alias for insertBlock)
 */
function addGutenbergBlock(mixed $page, string $blockName): mixed
{
    return insertBlock($page, $blockName);
}

/**
 * Gutenberg: Add heading block with text
 */
function addHeadingBlock(mixed $page, string $text, int $level = 2): mixed
{
    insertBlock($page, 'Heading');
    $page->type('.block-editor-rich-text__editable', $text);
    return $page;
}

/**
 * Gutenberg: Add image block
 */
function addImageBlock(mixed $page, string $imageUrl): mixed
{
    insertBlock($page, 'Image');
    $page->fill('.block-editor-url-input__input', $imageUrl);
    return $page;
}

/**
 * Gutenberg: Save draft
 */
function saveDraft(mixed $page): mixed
{
    $page->click('.editor-post-save-draft');
    return $page;
}

/**
 * Gutenberg: Switch to code editor
 */
function switchToCodeEditor(mixed $page): mixed
{
    $page->click('.edit-post-more-menu__button');
    $page->click('.edit-post-more-menu .components-menu-item__button:contains("Code editor")');
    return $page;
}

/**
 * Gutenberg: Switch to visual editor
 */
function switchToVisualEditor(mixed $page): mixed
{
    $page->click('.edit-post-more-menu__button');
    $page->click('.edit-post-more-menu .components-menu-item__button:contains("Visual editor")');
    return $page;
}

/**
 * Assert no JavaScript errors or console logs (smoke test)
 */
function assertNoSmoke(mixed $page): void
{
    if (is_array($page)) {
        foreach ($page as $p) {
            $p->assertNoJavascriptErrors();
            $p->assertNoConsoleLogs();
        }
    } else {
        $page->assertNoJavascriptErrors();
        $page->assertNoConsoleLogs();
    }
}
