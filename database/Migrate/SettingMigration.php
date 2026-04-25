<?php

namespace Database\Migrate;

use App\Core\Migrations;

class SettingMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
           `key` VARCHAR(150) NOT NULL UNIQUE,
            `value` TEXT NOT NULL,
            `group` VARCHAR(100) NULL, 
            `type` VARCHAR(50) NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL
        )");

        echo "✅ Table settings created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS settings");
        echo "✅ Table settings dropped\n";
    }
}
