<?php

namespace App\Models;

use App\Core\DB;

class Admin
{
    public function findByEmail(string $email): ?array
    {
        $stmt = DB::conn()->prepare(
            'SELECT * FROM admins WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}
