<?php

namespace App\Cms\Repositories;

use App\Core\DB;

class PostRepository
{
    public function paginate(int $limit = 20): array
    {
        $sql = 'SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit';
        $stmt = DB::conn()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return DB::first('SELECT * FROM posts WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO posts (title, slug, excerpt, content, meta_title, meta_description, status) VALUES (:title, :slug, :excerpt, :content, :meta_title, :meta_description, :status)';

        return DB::insertGetId($sql, [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'status' => $data['status'],
        ]);
    }
}
