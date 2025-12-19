# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2025-12-19

### Added

#### Core Framework

- WordPress integration test setup with Pest PHP v4
- PHP 8.3+ support
- File-based SQLite database for fast testing
- Base TestCase class with automatic cleanup
- WP-CLI integration (`wp pest` commands)
- Automatic WordPress develop repository download
- CI/CD pipeline support with `--skip-delete` flag

#### Browser Testing (Pest v4 Integration)

- 🌐 **WordPress Browser Testing** - Full Playwright-powered browser testing support
- `visitWordPress()`, `visitAdmin()`, `visitBlockEditor()` - Navigate WordPress pages
- `visitNewPost()`, `visitLogin()` - Navigate to post editor and login
- `browserLoginAs()`, `browserLoginAsAdmin()`, `browserLoginAsUser()` - Browser authentication
- `browserLogout()` - Log out via browser
- `addGutenbergBlock()`, `publishPost()`, `saveDraft()`, `updatePost()` - Block editor interactions
- `assertLoggedInAs()`, `assertCanSeeAdminBar()`, `assertInBlockEditor()` - Browser assertions
- `assertPostPublished()`, `assertPostUpdated()`, `assertNoWordPressErrors()` - Post assertions
- `assertOnLoginPage()`, `assertLoginSuccessful()`, `assertLoginFailed()` - Login assertions
- `onMobile()`, `onTablet()`, `onDesktop()` - Device viewport testing
- `inDarkMode()`, `inLightMode()` - Colour scheme testing
- `screenshotAs()` - Screenshot helpers

#### WooCommerce Browser Testing

- `visitWooCommerceProduct()`, `visitWooCommerceCart()`, `visitWooCommerceCheckout()` - WooCommerce navigation
- `addToCart()`, `fillCheckoutForm()`, `placeOrder()` - Checkout workflow
- `assertInCart()`, `assertOrderComplete()` - WooCommerce assertions

#### WordPress Admin Browser Testing

- `visitPluginsPage()`, `visitSettingsPage()`, `visitUsersPage()`, `visitThemesPage()` - Admin navigation
- `activatePlugin()`, `deactivatePlugin()` - Plugin management via browser
- `switchTheme()`, `customizeTheme()` - Theme management via browser
- `assertPluginActiveBrowser()`, `assertPluginInactiveBrowser()` - Plugin state assertions
- `assertThemeActive()` - Theme assertions

#### Media & Content Browser Testing

- `openMediaLibrary()`, `selectMediaItem()`, `insertMedia()` - Media library interactions
- `submitContactForm()`, `assertFormSubmitted()`, `assertFormError()` - Form testing

#### Skip Helpers (Pest v4 Features)

- 🎯 **Environment-based Skipping**
- `skipBrowserTestsLocally()`, `skipBrowserTestsOnCi()` - Skip browser tests based on environment
- `skipExternalApiTestsLocally()`, `skipExternalApiTestsOnCi()` - Skip API tests
- `skipLongRunningTestsLocally()`, `skipLongRunningTestsOnCi()` - Skip slow tests
- `skipIfMultisite()`, `skipIfNotMultisite()` - Multisite conditionals
- `skipIfPluginNotActive()`, `skipIfPluginActive()` - Plugin conditionals
- `skipIfWooCommerceNotActive()`, `skipIfYoastNotActive()`, `skipIfAcfNotActive()` - Popular plugins
- `skipIfPhpVersion()`, `skipIfWordPressVersion()` - Version requirements
- `skipIfRestApiDisabled()`, `skipIfGutenbergNotAvailable()` - Feature availability
- `skipIfDatabaseNotAvailable()` - Database availability
- `skipOnWindows()`, `skipOnMac()`, `skipOnLinux()` - Platform conditionals
- `onlyInMultisite()`, `onlyInSingleSite()`, `onlyWithPlugin()` - Readable aliases
- `onlyOnCi()`, `onlyLocally()` - Environment aliases

#### WordPress Expectations (Pest v4 Custom Expectations)

- ✅ **Chainable WordPress Expectations**
- `toBeSlug()` - Validate WordPress slugs
- `toBeValidPostStatus()` - Validate post status values
- `toBeValidUserRole()` - Validate user role names
- `toBeValidCapability()` - Validate capability strings
- `toBeValidPostType()` - Validate post type registration
- `toBeValidTaxonomy()` - Validate taxonomy registration
- `toBeWordPressError()` - Assert WP_Error instance
- `toHaveErrorCode()` - Assert specific error code
- `toBePublished()` - Assert post is published
- `toHavePostMeta()` - Assert post meta exists with value
- `toHaveUserRole()` - Assert user has role
- `toHaveCapability()` - Assert user has capability

#### Factory Functions

- `factory()::post()` and `factory()::posts()` - Create posts
- `factory()::user()` and `factory()::users()` - Create users
- `factory()::term()` and `factory()::terms()` - Create terms
- `factory()::comment()` - Create comments
- `factory()::attachment()` - Create attachments

#### Authentication & Users

- `actingAs()`, `actingAsAdmin()`, `actingAsEditor()`, `actingAsGuest()` - User authentication
- `assertAuthenticated()`, `assertNotAuthenticated()` - Auth assertions
- `assertUserCan()`, `assertUserCannot()` - Capability assertions
- `assertUserExists()`, `assertUserHasRole()` - User assertions

#### HTTP Testing

- `get()`, `post()`, `put()`, `patch()`, `delete()` - HTTP request helpers
- `from()` - Set referer header
- Fluent response assertions (`assertOk()`, `assertStatus()`, `assertSee()`)
- `assertJson()`, `assertJsonPath()` - JSON assertions

#### HTTP Mocking

- `fakeHttp()` - Mock external API calls
- `preventStrayRequests()`, `allowStrayRequests()` - Control unexpected requests
- `assertHttpSent()`, `assertHttpNotSent()`, `assertHttpSentCount()` - Request assertions

#### Email Testing

- `fakeEmail()` - Intercept WordPress emails
- `assertEmailSent()`, `assertEmailNotSent()` - Email assertions
- `assertEmailSentCount()`, `assertEmailSentTo()` - Email counting
- `assertNoEmailSent()` - Verify no emails sent

#### Cron/Scheduled Events

- `runCron()`, `runAllCrons()`, `runDueCrons()` - Execute cron jobs
- `assertCronScheduled()`, `assertCronNotScheduled()` - Cron assertions
- `assertCronScheduledAt()` - Time-specific assertions
- `scheduleCron()`, `clearAllCrons()` - Cron management

#### Database Testing

- `assertDatabaseHas()`, `assertDatabaseMissing()` - Database assertions
- `assertDatabaseCount()` - Count rows
- `truncateTable()`, `seedTable()` - Database operations

#### REST API Testing

- `restGet()`, `restPost()`, `restPut()`, `restPatch()`, `restDelete()` - REST helpers
- Fluent REST assertions (`assertCreated()`, `assertNoContent()`)
- `assertJsonStructure()`, `assertJsonCount()` - JSON structure tests
- Per-user authentication support

#### Plugin Compatibility

- `withYoast()` - Test with Yoast SEO
- `withWooCommerce()` - Test with WooCommerce
- `withAcf()` - Test with Advanced Custom Fields
- `withPlugin()` - Test with any plugin
- `activatePlugin()`, `deactivatePlugin()` - Plugin management
- `assertPluginActive()`, `assertPluginInactive()` - Plugin assertions

#### Multisite Support

- `createBlog()`, `deleteBlog()` - Blog management
- `switchToBlog()`, `restoreCurrentBlog()` - Blog switching
- `assertMultisite()`, `assertNotMultisite()` - Multisite assertions
- `assertBlogExists()` - Blog existence checks

#### File Upload Testing

- `fakeImage()` - Create fake images with dimensions
- `fakeUpload()` - Create fake file uploads
- `assertFileUploaded()` - Verify file uploads
- `assertImageSize()` - Check image size generation

#### Time Travel

- `freezeTime()` - Freeze time for testing
- `travelInTime()` - Move forward/backward in time
- `travelToTime()` - Travel to specific timestamp
- `restoreTime()` - Reset to current time
- `timeNow()` - Get current frozen time

#### Cache Testing

- `flushCache()` - Clear WordPress cache
- `forgetCache()` - Delete specific cache keys
- `assertCached()`, `assertNotCached()` - Cache assertions
- `assertTransient()`, `assertNoTransient()` - Transient assertions

#### AJAX Testing

- `callAjax()` - Call AJAX handlers
- `assertSuccess()`, `assertFailed()` - AJAX response assertions
- Support for authenticated and public AJAX actions
- JSON response validation

#### Redirect Testing

- `captureRedirects()` - Capture redirect calls
- `assertRedirected()`, `assertNotRedirected()` - Redirect assertions
- `assertRedirectStatus()` - Check redirect status codes
- `assertRedirectContains()` - Verify redirect URL fragments

#### Gutenberg Blocks

- `registerBlock()` - Register test blocks
- `parseBlocks()`, `renderBlock()` - Block parsing and rendering
- `assertBlockRegistered()`, `assertBlockNotRegistered()` - Block assertions
- `assertHasBlock()`, `assertBlockCount()` - Block content assertions

#### Admin Notices

- `captureAdminNotices()` - Capture admin UI notices
- `assertAdminNotice()` - Assert notice content
- `assertNoAdminNotice()` - Verify no notices
- `assertAdminNoticeType()` - Check notice type (success, error, etc.)

#### Navigation Menus

- `createMenu()`, `addMenuItem()` - Menu creation
- `assertMenuExists()` - Menu existence checks
- `assertMenuLocation()` - Verify menu locations
- `assertMenuHasItems()` - Count menu items

#### Widgets & Sidebars

- `registerWidget()`, `addWidgetToSidebar()` - Widget management
- `assertWidgetRegistered()` - Widget registration checks
- `assertSidebarExists()` - Sidebar existence
- `assertSidebarHasWidgets()` - Widget counting

#### WordPress Assertions (40+)

- Post assertions: `assertPostExists()`, `assertPostHasStatus()`, `assertPostHasMeta()`, `assertPostHasTerm()`
- Term assertions: `assertTermExists()`
- Option assertions: `assertOptionExists()`, `assertOptionEquals()`
- Hook assertions: `assertHookAdded()`, `assertFilterAdded()`
- Registration assertions: `assertPostTypeExists()`, `assertTaxonomyExists()`, `assertShortcodeExists()`
- Query assertions: `assertQueryHasPosts()`, `assertQueryPostCount()`
- Asset assertions: `assertEnqueued()`, `assertNotEnqueued()`

#### Setup Commands

- `vendor/bin/wp-pest setup` - CLI setup command
- Support for theme and plugin setup
- WordPress version selection (`--wp-version`)
- Plugin slug configuration (`--plugin-slug`)
- CI-friendly options (`--skip-delete`)

### Features

- **150+ Helper Functions** - Comprehensive testing toolkit
- **Browser Testing** - Test WordPress in real browsers with Playwright
- **Test Sharding** - Split tests across multiple CI workers for faster builds
- **Skip Helpers** - Conditional test execution based on environment
- **WordPress Expectations** - Chainable, WordPress-specific expectations
- **Laravel-Style Syntax** - Beautiful, expressive tests
- **Modern PHP** - PHP 8.3+ with strict types
- **Fast Testing** - File-based SQLite database
- **Complete Coverage** - Test every aspect of WordPress
- **WP-CLI Native** - Full WordPress CLI integration
- **Plugin Compatible** - Works with popular WordPress plugins
- **Pest PHP v4** - Latest testing framework features
- **Automatic Cleanup** - Tests clean up after themselves

### Documentation

- 📄 **Comprehensive Browser Testing Guide** - Complete examples for WordPress, Gutenberg, and WooCommerce
- **Test Sharding Documentation** - How to split tests across multiple CI workers
- **CI/CD Pipeline Examples** - GitHub Actions, GitLab CI, CircleCI with sharding
- **Performance Optimisation Tips** - Best practices for fast test execution
- **Skip Helper Examples** - Conditional test execution patterns
- **New Expectations Guide** - WordPress-specific chainable expectations
- Comprehensive README with examples
- Installation and quick start guide
- Advanced testing examples
- Troubleshooting guide
- CI/CD integration examples (GitHub Actions, GitLab CI, CircleCI)
- Comparison with alternatives
- Added stub files: `ExampleBrowserTest.php.stub`, `ExampleWooCommerceBrowserTest.php.stub`, `BROWSER_TESTING.md`

### Suggested Dependencies

- `pestphp/pest-plugin-browser` - Required for browser testing features (optional, suggested)

[unreleased]: https://github.com/jakehenshall/pest-plugin-wordpress/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/jakehenshall/pest-plugin-wordpress/releases/tag/v0.1.0
