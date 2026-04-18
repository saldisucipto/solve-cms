<?php

namespace Database\Migrate;

use App\Core\Migrations;


class AnalyticsCountryMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS analytics_countries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            country_code VARCHAR(10) NOT NULL,
            visit_date DATE NOT NULL,
            total_visits INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table analytics_countries created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS analytics_countries");
        echo "✅ Table analytics_countries dropped\n";
    }
}
