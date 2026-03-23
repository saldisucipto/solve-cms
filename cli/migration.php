<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ambil argumen
$argv = $_SERVER['argv'] ?? [];

$className = $argv[1] ?? null;

if (!$className) {
    echo "❌ Migration class name required\n";
    exit;
}

// full namespace
$fullClass = "Database\\Migrate\\{$className}";

// cek file
$file = __DIR__ . "/../Database/Migrate/{$className}.php";

if (!file_exists($file)) {
    echo "❌ File migration tidak ditemukan: {$className}.php\n";
    exit;
}

// load file
require_once $file;

// cek class
if (!class_exists($fullClass)) {
    echo "❌ Class {$fullClass} tidak ditemukan\n";
    exit;
}

// jalankan
$migration = new $fullClass();

if (!method_exists($migration, 'up')) {
    echo "❌ Method up() tidak ditemukan\n";
    exit;
}

echo "🚀 Running migration: {$className}\n";

$action = $argv[2] ?? 'up';

if ($action === 'down') {
    $migration->down();
} else {
    $migration->up();
}

$migration->up();

echo "✅ Migration selesai\n";
