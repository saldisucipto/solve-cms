<?php

namespace Database\Migrate;

use App\Core\Migrations;

class PostsMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT NULL,
            content TEXT NOT NULL,
            featured_image VARCHAR(255) NULL,
            meta_title VARCHAR(255) NULL,
            meta_description TEXT NULL,
            status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        echo "✅ Table posts created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS posts");
        echo "✅ Table posts dropped\n";
    }
}
