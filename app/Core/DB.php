<?php

namespace App\Core;

use PDO;
use PDOException;

class DB
{
    protected static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (static::$pdo !== null) {
            return static::$pdo;
        }
        $config = Config::get('database');
        $conn = $config['connections'][$config['default']];

        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            $conn['host'],
            $conn['port'],
            $conn['database'],
            $conn['charset']
        );

        try {
            static::$pdo = new PDO(
                $dsn,
                $conn['username'],
                $conn['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            if (Config::get('app.debug')) {
                die('Database Error: ' . $e->getMessage());
            }
            die('Koneksi Database Gagal!');
        }
        return static::$pdo;
    }
}
