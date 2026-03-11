<?php


namespace App\Core;

use App\Models\Admin;
use App\Models\User;

class Auth
{
    /**
     * Waktu kadaluarsa login (dalam detik)
     * 24 jam = 24 * 3600 = 86400
     */
    protected const EXPIRE_TIME = 86400;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }
        session_regenerate_id(true);

        Session::set('user', (array) $user);

        Session::set('auth', [
            'id'         => $user->id,
            'role'       => $user->role,
            'ua'         => $_SERVER['HTTP_USER_AGENT'],
            'login_time' => time(), // Tambahkan waktu login
        ]);
        return true;
    }

    public static function check(): bool
    {
        $auth = Session::get('auth');

        if (!$auth) {
            return false;
        }

        // Cek apakah sudah kadaluarsa
        if (time() - $auth['login_time'] > self::EXPIRE_TIME) {
            static::logout();
            return false;
        }

        return true;
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function role(): ?string
    {
        $auth = Session::get('auth');
        return $auth['role'] ?? null;
    }

    public static function id(): ?int
    {
        $auth = Session::get('auth');
        return $auth['id'] ?? null;
    }

    public static function logout(): void
    {
        Session::forget('auth');
        Session::forget('user');
        session_destroy();
    }
}
