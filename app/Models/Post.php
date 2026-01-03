<?php

namespace App\Models;

use App\Core\DB;

class Post extends Model
{
    protected string $table = 'posts';

    public function findBySlug(string $slug): ?array
    {
        $stmt = DB::conn()->prepare(
            "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
