<?php

/**
 * Router for PHP's built-in server.
 *
 * We deliberately do not use `php artisan serve` in the container. That
 * command starts the built-in server as a child process and forwards only
 * a fixed whitelist of environment variables, which does not include
 * DB_CONNECTION or DB_URL. The child therefore fell back to the .env baked
 * into the image and talked to an empty SQLite file, while `artisan migrate`
 * — running in the parent, where the variables exist — happily migrated
 * Postgres. Serving in-process keeps one environment for both.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// let the built-in server serve real files in public/ directly
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
