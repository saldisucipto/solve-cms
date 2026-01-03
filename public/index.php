<?php
// Memberikan Strict Value Pengecekan Type Data 
declare(strict_types=1);

/**
 * Solve CMS 
 * Ini adalah gate pertama request masuk ke aplikasi Solve CMS
 */

// start Session 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting Dev Module 
ini_set('display_error', 1);
error_reporting(E_ALL);

// Mendefinisikan Base Path Aplikasi 
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Autoload 
require BASE_PATH . '/vendor/autoload.php';

// Boot Application 
use App\Core\App;
// Mmebuat Object Baru
$app = new App();
$app->run();
