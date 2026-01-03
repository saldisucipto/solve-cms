<?php

namespace App\Models;

use App\Core\DB;

abstract class Model
{
    protected string $table;

    public function all(): array
    {
        $stmt = DB::conn()->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = DB::conn()->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }
}
