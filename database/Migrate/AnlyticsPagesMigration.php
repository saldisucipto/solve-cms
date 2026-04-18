<?php

namespace Database\Migrate;

use App\Core\Migrations;

class AnlyticsPagesMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS analytics_pages(
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(255) NOT NULL,
            visit_date DATE NOT NULL,
            total_visits INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table analytics_pages created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS analytics_pages");
        echo "✅ Table analytics_pages dropped\n";
    }
}
