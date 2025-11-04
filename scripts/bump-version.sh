#!/bin/bash

# Version bump script for local development
# Usage: ./scripts/bump-version.sh [major|minor|patch]

set -e

BUMP_TYPE=${1:-patch}
COMPOSER_FILE="composer.json"

# Extract current version
if [[ "$OSTYPE" == "darwin"* ]]; then
  # macOS - use grep with Perl regex
  CURRENT_VERSION=$(grep -oE '"version": "[^"]*"' "$COMPOSER_FILE" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')
else
  # Linux - use grep with Perl regex
  CURRENT_VERSION=$(grep -oP '(?<="version": ")[^"]*' "$COMPOSER_FILE")
fi
echo "Current version: $CURRENT_VERSION"

# Parse version
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

# Bump version
case $BUMP_TYPE in
  major)
    MAJOR=$((MAJOR + 1))
    MINOR=0
    PATCH=0
    ;;
  minor)
    MINOR=$((MINOR + 1))
    PATCH=0
    ;;
  patch)
    PATCH=$((PATCH + 1))
    ;;
  *)
    echo "Error: Invalid bump type. Use major, minor, or patch"
    exit 1
    ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo "New version: $NEW_VERSION"

# Update composer.json
if [[ "$OSTYPE" == "darwin"* ]]; then
  # macOS
  sed -i '' "s/\"version\": \".*\"/\"version\": \"$NEW_VERSION\"/" "$COMPOSER_FILE"
else
  # Linux
  sed -i "s/\"version\": \".*\"/\"version\": \"$NEW_VERSION\"/" "$COMPOSER_FILE"
fi

echo "Updated $COMPOSER_FILE to version $NEW_VERSION"

# Create git tag
TAG="v$NEW_VERSION"
if git rev-parse "$TAG" >/dev/null 2>&1; then
  echo "Warning: Tag $TAG already exists"
else
  git add "$COMPOSER_FILE"
  git commit -m "chore: bump version to $NEW_VERSION"
  git tag -a "$TAG" -m "Release version $NEW_VERSION"
  echo "Created tag: $TAG"
  echo "Don't forget to push: git push && git push --tags"
fi

