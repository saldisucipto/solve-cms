<?php

/**
 * Solve CMS 
 * Ini adalah gate pertama request masuk ke aplikasi Solve CMS
 */

// Memberikan Strict Value Pengecekan Type Data 
declare(strict_types=1);

// Error Reporting Dev Module 
ini_set('display_error', 1);
error_reporting(E_ALL);

// Mendefinisikan Base Path Aplikasi 
define('BASE_PATH', __DIR__);

// Simple Autoload Tanpa Composer 
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    // hanya load class App/ 
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        /**
         * Fungsi ini berguna untuk membandingkan string dengan membuat jumlah karakter
         */
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        return $file;
    }
});

// Boot Application 
use App\Core\App;

// Mmebuat Object Baru
$app = new App();
$app->run();
