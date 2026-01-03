<?php

namespace App\Core;

use PDO;
use App\Core\DB;

abstract class Model
{
    protected static string $table;

    protected static function db(): PDO
    {
        return DB::conn();
    }

    static function find($id)
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . "WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(static::class);
    }

    static function where($column, $value)
    {
        return new QueryBuilder(static::$table, static::class, $column, $value);
    }
}
