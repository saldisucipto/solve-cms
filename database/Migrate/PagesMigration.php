<?php

namespace Database\Migrate;

use App\Core\Migrations;

class PagesMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            meta_title VARCHAR(255) NULL,
            meta_description TEXT NULL,
            status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        echo "✅ Table pages created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS pages");
        echo "✅ Table pages dropped\n";
    }
}
