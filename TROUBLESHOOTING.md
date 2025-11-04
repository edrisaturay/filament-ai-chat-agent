# Troubleshooting Guide

## Class "MalteKuhr\LaravelGPT\GPTChat" not found

### Problem
Even though `maltekuhr/laravel-gpt` is listed as a dependency in `composer.json`, you're getting a "Class not found" error.

### Solutions

#### Solution 1: Install the dependency directly in your Laravel app

```bash
cd /path/to/your/laravel/app
composer require maltekuhr/laravel-gpt:^0.1.5 --no-interaction
composer dump-autoload
php artisan optimize:clear
```

#### Solution 2: Update your package and regenerate autoloader

If you're using a local path installation:

```bash
# In your Laravel app directory
composer update edrisaturay/filament-ai-chat-agent --no-interaction
composer dump-autoload
```

#### Solution 3: Check if the package is actually installed

```bash
# In your Laravel app directory
composer show maltekuhr/laravel-gpt
```

If it shows "Package not found", install it using Solution 1.

#### Solution 4: Verify autoloader

```bash
# In your Laravel app directory
composer dump-autoload -o
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### Why this happens

When you install a package via Composer, dependencies listed in that package's `composer.json` should be automatically installed. However, sometimes:

1. **Local path installations**: If you're using `"type": "path"` in your Laravel app's `composer.json`, Composer might not automatically resolve nested dependencies properly.

2. **Version conflicts**: There might be version conflicts preventing the dependency from being installed.

3. **Cache issues**: Composer or Laravel cache might be stale.

### Verification

After installing, verify the class is available:

```php
// In tinker or a test file
php artisan tinker

// Then run:
class_exists('MalteKuhr\LaravelGPT\GPTChat') // Should return true
```

### Still having issues?

1. Check your Laravel app's `composer.json` to see how the package is installed
2. Check `composer.lock` to see if `maltekuhr/laravel-gpt` is listed
3. Check `vendor/composer/autoload_classmap.php` to see if the class is registered

