<?php

namespace Database\Migrate;

use App\Core\Migrations;

class SettingMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
           `key` VARCHAR(255) NOT NULL UNIQUE,
            value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table settings created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS settings");
        echo "✅ Table settings dropped\n";
    }
}
