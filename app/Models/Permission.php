<?php

namespace App\Models;

use App\Core\DB;

class Permission
{
    static function userHasPermission(int $userID, string $permission): bool
    {
        $sql = "
        SELECT 1 FROM permissions p 
        JOIN role_permissions rp ON rp.permission_id = p.id
        JOIN user_roles ur ON ur.role_id = rp.role_id
        WHERE ur.user_id = :user_id AND p.name = :permissions LIMIT 1
        ";

        $statement = DB::conn()->prepare($sql);
        $statement->execute([
            'user_id' => $userID,
            'permissions' => $permission,
        ]);

        return (bool) $statement->fetchColumn();
    }
}
