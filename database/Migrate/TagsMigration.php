<?php

namespace Database\Migrate;

use App\Core\Migrations;

class TagsMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table tags created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS tags");
        echo "✅ Table tags dropped\n";
    }
}
