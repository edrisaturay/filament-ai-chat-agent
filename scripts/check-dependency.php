#!/usr/bin/env php
<?php

/**
 * Script to check if maltekuhr/laravel-gpt dependency is available
 * Run this from your Laravel application root:
 * php vendor/edrisaturay/filament-ai-chat-agent/scripts/check-dependency.php
 */

$autoloadPaths = [
    __DIR__ . '/../../../../autoload.php', // Laravel app vendor/autoload.php
    __DIR__ . '/../../../autoload.php',    // Alternative path
    __DIR__ . '/../../vendor/autoload.php', // Package vendor/autoload.php
];

$autoload = null;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        $autoload = $path;
        break;
    }
}

if (!$autoload) {
    echo "❌ Could not find autoload.php\n";
    echo "Please run this script from your Laravel application root directory.\n";
    exit(1);
}

require $autoload;

echo "🔍 Checking maltekuhr/laravel-gpt dependency...\n\n";

$checks = [
    'GPTChat' => 'MalteKuhr\LaravelGPT\GPTChat',
    'ChatRole' => 'MalteKuhr\LaravelGPT\Enums\ChatRole',
    'ChatMessage' => 'MalteKuhr\LaravelGPT\Models\ChatMessage',
];

$allPassed = true;

foreach ($checks as $name => $class) {
    if (class_exists($class) || (str_contains($class, '\\Enums\\') && enum_exists($class))) {
        echo "✅ {$name} class/enum found\n";
    } else {
        echo "❌ {$name} class/enum NOT found\n";
        $allPassed = false;
    }
}

echo "\n";

if (!$allPassed) {
    echo "❌ Dependency check FAILED\n\n";
    echo "To fix this, run in your Laravel application:\n";
    echo "  composer require maltekuhr/laravel-gpt:^0.1.5\n";
    echo "  composer dump-autoload\n";
    echo "  php artisan optimize:clear\n";
    exit(1);
}

echo "✅ All dependencies are available!\n";
exit(0);

