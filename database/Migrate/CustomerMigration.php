<?php

namespace Database\Migrate;

use App\Core\Migrations;

class CustomerMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            npwp VARCHAR(50) NOT NULL,
            customer_person VARCHAR(255) NOT NULL,
            coa VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo "✅ Table customers created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS customers");

        echo "🗑️ Table customers dropped\n";
    }
}
