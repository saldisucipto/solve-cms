<?php

namespace App\Core;

use App\Models\Permission;

class Gate
{
    static function allows(string $premission): bool
    {
        $user = Auth::user();
        $userID = $user['id'] ?? null;

        if (!$userID) {
            return false;
        }
        return Permission::userHasPermission($userID, $premission);
    }

    static function authorize(string $premission): void
    {
        if (!self::allows($premission)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
