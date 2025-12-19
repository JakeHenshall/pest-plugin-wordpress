# Release Checklist for v0.1.0

## Pre-Release (Do these first)

- [ ] All tests pass: `composer test`
- [ ] PHPStan analysis clean (or acceptable): `composer phpstan`
- [ ] README.md is up to date
- [ ] CHANGELOG.md has v0.1.0 section with all changes
- [ ] composer.json has correct version constraints
- [ ] All changes committed to git
- [ ] Code pushed to GitHub

## Release Steps

### 1. Create and Push Tag
```bash
# Make sure you're on main branch and up to date
git checkout main
git pull

# Run the release script
./release.sh 0.1.0

# Or manually:
git tag -a v0.1.0 -m "First beta release - v0.1.0"
git push origin v0.1.0
```

### 2. Create GitHub Release
1. Go to: https://github.com/jakehenshall/pest-plugin-wordpress/releases/new
2. Select tag: `v0.1.0`
3. Release title: `v0.1.0 - First Beta Release`
4. Description (copy from CHANGELOG.md or write):
   ```markdown
   # 🚀 First Beta Release
   
   The most comprehensive WordPress testing framework built on Pest PHP v4.
   
   ## ✨ Highlights
   
   - **Batteries Included**: PHPStan v2.1, SQLite, 150+ helpers—all bundled
   - **Zero Config**: Install and start testing immediately
   - **Laravel-Style Syntax**: Beautiful, expressive tests
   - **Fast**: SQLite for blazing-fast test execution
   - **WordPress Native**: Full WP-CLI integration
   
   ## 📦 What's Included
   
   - ✅ Pest PHP v4
   - ✅ PHPStan v2.1 with WordPress rules
   - ✅ SQLite for fast testing
   - ✅ 150+ WordPress helper functions
   - ✅ Browser testing support (optional)
   - ✅ Email, HTTP, AJAX, REST API testing
   - ✅ Multisite support
   - ✅ Plugin compatibility (Yoast, WooCommerce, ACF)
   
   ## 🚀 Quick Start
   
   ```bash
   composer require --dev jakehenshall/pest-plugin-wordpress:^0.1
   vendor/bin/wp-pest setup
   composer test
   ```
   
   See the [README](https://github.com/jakehenshall/pest-plugin-wordpress#readme) for full documentation.
   
   ## ⚠️ Beta Notice
   
   This is a beta release. While fully functional and tested, we're gathering community feedback before releasing v1.0.0. Please report any issues!
   ```
5. Check "Set as a pre-release" (since it's 0.1.0)
6. Click **"Publish release"**

### 3. Register on Packagist (First Time Only)
1. Go to: https://packagist.org/packages/submit
2. Enter: `https://github.com/jakehenshall/pest-plugin-wordpress`
3. Click "Check" then "Submit"
4. Packagist will auto-update on new tags (via webhook)

### 4. Verify Release
- [ ] GitHub release is visible
- [ ] Tag appears on GitHub
- [ ] Packagist shows v0.1.0 (may take ~1 hour)
- [ ] Test installation: `composer require --dev jakehenshall/pest-plugin-wordpress:^0.1`

## Post-Release

### Announce It! 🎉
- [ ] Twitter/X
- [ ] WordPress Slack (testing channel)
- [ ] Reddit (r/WordPress, r/PHPUnit, r/PHPHelp)
- [ ] Dev.to article
- [ ] WordPress.org forums

### Monitor
- [ ] Watch GitHub issues
- [ ] Check Packagist download stats
- [ ] Gather feedback for v1.0.0

## Version Bump Strategy

After gathering feedback and fixing any issues:
- v0.2.0 - Minor improvements
- v0.3.0 - More features
- v1.0.0 - Stable release (after community validation)

## Manual Release Commands

If you don't want to use the script:

```bash
# Ensure clean state
git status

# Create tag
git tag -a v0.1.0 -m "Release v0.1.0"

# Push tag
git push origin v0.1.0

# Delete tag if needed (mistakes happen!)
git tag -d v0.1.0
git push origin :refs/tags/v0.1.0
```

