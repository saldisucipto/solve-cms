<?php

declare(strict_types=1);

// Base Path
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Autoload
require BASE_PATH . '/vendor/autoload.php';

// Load ENV (kalau kamu pakai dotenv di App)
use Dotenv\Dotenv;

if (file_exists(BASE_PATH . '/.env')) {
    Dotenv::createImmutable(BASE_PATH)->load();
}

// Start session (aman walau CLI)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
