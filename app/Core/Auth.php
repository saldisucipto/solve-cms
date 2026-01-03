<?php


namespace App\Core;

use App\Models\Admin;
use App\Models\User;

class Auth
{
    public static function attemps(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }
        session_regenerate_id(true);

        Session::set('auth', [
            'id'   => $user->id,
            'role' => $user->role,
            'ua'   => $_SERVER['HTTP_USER_AGENT']
        ]);
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin']);
    }
}
