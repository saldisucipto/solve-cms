<?php

namespace Database\Seeders;

use App\Core\DB;

class AdminSeeder
{
    public static function run(): void
    {
        // mulai koneksi
        $pdo = DB::conn();

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

        $pdo->beginTransaction();

        try {
            $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (:name)")
                ->execute(['name' => 'admin']);

            $roleId = (int) ($pdo->lastInsertId() ?: self::getRoleId('admin'));

            $pdo->prepare(
                "INSERT IGNORE INTO users (name, email, password)
                VALUES (:name, :email, :password)"
            )->execute([
                'name' => 'Administrator',
                'email' => 'admin@local.test',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
            ]);

            $userId = (int) ($pdo->lastInsertId() ?: self::getUserId('admin@local.test'));

            $permissionStatement = $pdo->prepare(
                "INSERT IGNORE INTO permissions (name) VALUES (:name)"
            );

            $rolePermissionStatement = $pdo->prepare(
                "INSERT IGNORE INTO role_permissions (role_id, permission_id)
                VALUES (:role_id, :permission_id)"
            );

            foreach ($permissions as $permission) {
                $permissionStatement->execute(['name' => $permission]);
                $permissionId = (int) ($pdo->lastInsertId() ?: self::getPermissionId($permission));

                $rolePermissionStatement->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }

            $pdo->prepare(
                "INSERT IGNORE INTO user_roles (user_id, role_id)
                VALUES (:user_id, :role_id)"
            )->execute([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            $pdo->commit();
            echo "✅ Admin seeder executed successfully\n";
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    protected static function getRoleId(string $name): int
    {
        $statement = DB::conn()->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
        $statement->execute(['name' => $name]);

        return (int) $statement->fetchColumn();
    }

    protected static function getPermissionId(string $name): int
    {
        $statement = DB::conn()->prepare("SELECT id FROM permissions WHERE name = :name LIMIT 1");
        $statement->execute(['name' => $name]);

        return (int) $statement->fetchColumn();
    }

    protected static function getUserId(string $email): int
    {
        $statement = DB::conn()->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $statement->execute(['email' => $email]);

        return (int) $statement->fetchColumn();
    }
}
