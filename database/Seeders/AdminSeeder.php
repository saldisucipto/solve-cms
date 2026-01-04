<?php

namespace Database\Seeders;

use App\Core\DB;

class AdminSeeder
{
    static function run(): void
    {
        // mulai koneksi 
        $pdo = DB::conn();

        // Membuat Role Admin 
        $pdo->exec("INSERT IGNORE INTO roles (name) values ('admin')");
        $roleID = $pdo->lastInsertId() ?: self::getRoleID('admin');


        // Default Permissions List 
        $permissions = [
            'admin.view',
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'post.view',
            'post.create',
            'post.edit',
            'post.delete',
            'setting.update',
        ];

        foreach ($permissions as $permission) {
            $statement = $pdo->prepare(
                "INSERT IGNORE INTO permissions (name) VALUES (:name)"
            );
            $statement->execute(['name' => $permission]);

            $permissionID = $pdo->lastInsertId() ?: self::getPermissionID($permission);

            // role permission 
            $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role, :permisson)")->execute([
                'role' => $roleID,
                'permisson' => $permissionID,
            ]);

            $pdo->prepare(
                "INSERT IGNORE INTO users (name, email, password)
             VALUES ('Administrator', 'admin@local.test', :password)"
            )->execute([
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
            ]);

            $userId = $pdo->lastInsertId() ?: self::getUserId('admin@local.test');

            // 5️⃣ USER ↔ ROLE
            $pdo->prepare(
                "INSERT IGNORE INTO user_roles (user_id, role_id)
             VALUES (:user, :role)"
            )->execute([
                'user' => $userId,
                'role' => $roleID,
            ]);

            echo "✅ Admin seeder executed successfully\n";
        }
    }

    protected static function getRoleId(string $name): int
    {
        return (int) DB::conn()
            ->query("SELECT id FROM roles WHERE name='{$name}'")
            ->fetchColumn();
    }

    protected static function getPermissionId(string $name): int
    {
        return (int) DB::conn()
            ->query("SELECT id FROM permissions WHERE name='{$name}'")
            ->fetchColumn();
    }

    protected static function getUserId(string $email): int
    {
        return (int) DB::conn()
            ->query("SELECT id FROM users WHERE email='{$email}'")
            ->fetchColumn();
    }
}
