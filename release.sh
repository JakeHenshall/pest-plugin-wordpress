#!/bin/bash
#
# Release Script for pest-plugin-wordpress
# Usage: ./release.sh 0.1.0
#

set -e

VERSION=$1

if [ -z "$VERSION" ]; then
    echo "❌ Error: Version number required"
    echo "Usage: ./release.sh 0.1.0"
    exit 1
fi

echo "🚀 Releasing pest-plugin-wordpress v$VERSION"
echo ""

# Ensure we're on main branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ] && [ "$CURRENT_BRANCH" != "master" ]; then
    echo "⚠️  Warning: You're not on main/master branch (current: $CURRENT_BRANCH)"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check for uncommitted changes
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ Error: You have uncommitted changes"
    echo "Please commit or stash them first"
    exit 1
fi

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin $(git branch --show-current)

# Run tests
echo "🧪 Running tests..."
if ! composer test; then
    echo "❌ Tests failed! Fix them before releasing."
    exit 1
fi

# Run PHPStan
echo "🔍 Running PHPStan..."
if ! composer phpstan; then
    echo "⚠️  PHPStan found issues"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Create and push tag
echo "🏷️  Creating tag v$VERSION..."
git tag -a "v$VERSION" -m "Release v$VERSION"

echo "📤 Pushing tag to GitHub..."
git push origin "v$VERSION"

echo ""
echo "✅ Release v$VERSION completed!"
echo ""
echo "Next steps:"
echo "1. Go to: https://github.com/jakehenshall/pest-plugin-wordpress/releases/new"
echo "2. Select tag: v$VERSION"
echo "3. Set title: v$VERSION"
echo "4. Add release notes (see CHANGELOG.md)"
echo "5. Click 'Publish release'"
echo ""
echo "📦 Packagist will automatically detect the new tag within ~1 hour"
echo "   Or trigger manually: https://packagist.org/packages/jakehenshall/pest-plugin-wordpress"

