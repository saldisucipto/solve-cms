<?php

namespace Database\Migrate;

use App\Core\Migrations;

class MediaMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS media (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(255) NOT NULL,
            mime_type VARCHAR(255) NOT NULL,
            size INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table media created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS media");
        echo "✅ Table media dropped\n";
    }
}
