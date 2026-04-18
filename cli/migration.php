<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ambil argumen
$argv = $_SERVER['argv'] ?? [];

$className = $argv[1] ?? null;
$action = $argv[2] ?? 'up';
$migrationDir = dirname(__DIR__)
    . DIRECTORY_SEPARATOR . 'database'
    . DIRECTORY_SEPARATOR . 'Migrate';

if (!$className) {
    echo "❌ Migration class name required\n";
    exit;
}

function runMigration(string $className, string $action, string $migrationDir): void
{
    $fullClass = "Database\\Migrate\\{$className}";
    $file = $migrationDir . DIRECTORY_SEPARATOR . $className . '.php';

    if (!file_exists($file)) {
        echo "File {$file} \n";
        echo "❌ File migration tidak ditemukan: {$className}.php\n";
        exit(1);
    }

    require_once $file;

    if (!class_exists($fullClass)) {
        echo "❌ Class {$fullClass} tidak ditemukan\n";
        exit(1);
    }

    $migration = new $fullClass();

    if (!method_exists($migration, $action)) {
        echo "❌ Method {$action}() tidak ditemukan di class {$fullClass}\n";
        exit(1);
    }

    echo "🚀 Running migration: {$className} ({$action})\n";
    $migration->{$action}();
}

if ($className === 'all') {
    $files = glob($migrationDir . DIRECTORY_SEPARATOR . '*.php') ?: [];

    sort($files);

    foreach ($files as $file) {
        $currentClass = pathinfo($file, PATHINFO_FILENAME);

        if ($currentClass === 'All') {
            continue;
        }

        runMigration($currentClass, $action, $migrationDir);
    }

    echo "✅ Semua migration selesai\n";
    exit;
}

runMigration($className, $action, $migrationDir);

echo "✅ Migration selesai\n";
