<?php

// Vercel serverless environment configuration
if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    $tmp_dir = '/tmp/laravel';
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir . '/framework/cache/data', 0777, true);
        mkdir($tmp_dir . '/framework/views', 0777, true);
        mkdir($tmp_dir . '/framework/sessions', 0777, true);
        mkdir($tmp_dir . '/logs', 0777, true);
    }

    // Force environment variables to use /tmp and serverless-friendly drivers
    $_ENV['VIEW_COMPILED_PATH'] = $tmp_dir . '/framework/views';
    putenv('VIEW_COMPILED_PATH=' . $tmp_dir . '/framework/views');

    $_ENV['SESSION_DRIVER'] = 'cookie';
    putenv('SESSION_DRIVER=cookie');

    $_ENV['LOG_CHANNEL'] = 'stderr';
    putenv('LOG_CHANNEL=stderr');

    $_ENV['APP_CONFIG_CACHE'] = '/tmp/laravel/config.php';
    putenv('APP_CONFIG_CACHE=/tmp/laravel/config.php');

    $_ENV['APP_EVENTS_CACHE'] = '/tmp/laravel/events.php';
    putenv('APP_EVENTS_CACHE=/tmp/laravel/events.php');

    $_ENV['APP_ROUTES_CACHE'] = '/tmp/laravel/routes.php';
    putenv('APP_ROUTES_CACHE=/tmp/laravel/routes.php');

    $_ENV['APP_SERVICES_CACHE'] = '/tmp/laravel/services.php';
    putenv('APP_SERVICES_CACHE=/tmp/laravel/services.php');

    $_ENV['APP_PACKAGES_CACHE'] = '/tmp/laravel/packages.php';
    putenv('APP_PACKAGES_CACHE=/tmp/laravel/packages.php');
    
    $_ENV['CACHE_STORE'] = 'array';
    putenv('CACHE_STORE=array');
}

require __DIR__ . '/../public/index.php';
