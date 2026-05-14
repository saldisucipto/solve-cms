<?php

namespace App\Users\Repositories;

use App\Core\DB;

class UserRepository
{
    public function all(): array
    {
        return DB::select(
            'SELECT u.id, u.name, u.email, u.created_at, r.name as role FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             ORDER BY u.created_at DESC'
        );
    }

    public function findById(int $id): ?array
    {
        return DB::first(
            'SELECT u.*, r.name as role FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return DB::first('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email]);
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)';

        return DB::insertGetId($sql, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $updates = [];
        $bindings = ['id' => $id];

        if (isset($data['name'])) {
            $updates[] = 'name = :name';
            $bindings['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updates[] = 'email = :email';
            $bindings['email'] = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = 'password = :password';
            $bindings['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($updates)) {
            return true;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';

        return DB::statement($sql, $bindings);
    }

    public function delete(int $id): bool
    {
        DB::statement('DELETE FROM user_roles WHERE user_id = :id', ['id' => $id]);

        return DB::statement('DELETE FROM users WHERE id = :id', ['id' => $id]);
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        DB::statement('DELETE FROM user_roles WHERE user_id = :user_id', ['user_id' => $userId]);

        return DB::statement(
            'INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)',
            ['user_id' => $userId, 'role_id' => $roleId]
        );
    }

    public function getRoles(): array
    {
        return DB::select('SELECT id, name FROM roles ORDER BY name ASC');
    }
}
