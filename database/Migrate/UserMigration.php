<?php 

namespace Database\Migrate;

use App\Core\Migrations;

class UserMigration extends Migrations
{
    public function up(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            PRIMARY KEY (user_id, role_id),
            CONSTRAINT fk_user_roles_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_user_roles_role
                FOREIGN KEY (role_id) REFERENCES roles(id)
                ON DELETE CASCADE
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_role_permissions_role
                FOREIGN KEY (role_id) REFERENCES roles(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_role_permissions_permission
                FOREIGN KEY (permission_id) REFERENCES permissions(id)
                ON DELETE CASCADE
        )");

        echo "✅ Tables users, roles, permissions, user_roles, and role_permissions created\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS role_permissions");
        $this->pdo->exec("DROP TABLE IF EXISTS user_roles");
        $this->pdo->exec("DROP TABLE IF EXISTS permissions");
        $this->pdo->exec("DROP TABLE IF EXISTS roles");
        $this->pdo->exec("DROP TABLE IF EXISTS users");

        echo "🗑️ Tables role_permissions, user_roles, permissions, roles, and users dropped\n";
    }
}
