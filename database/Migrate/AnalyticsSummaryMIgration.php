<?php

namespace Database\Migrate;

use App\Core\Migrations;

class AnalyticsSummaryMIgration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS analytics_summary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visit_date DATE NOT NULL,
            total_visits INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table analytics_summary created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS analytics_summary");
        echo "✅ Table analytics_summary dropped\n";
    }
}
