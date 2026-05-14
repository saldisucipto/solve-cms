<?php

namespace App\Cms\Repositories;

use App\Core\DB;

class PageRepository
{
    public function paginate(int $limit = 20): array
    {
        $sql = 'SELECT * FROM pages ORDER BY created_at DESC LIMIT :limit';
        $stmt = DB::conn()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return DB::first('SELECT * FROM pages WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO pages (title, slug, content, meta_title, meta_description, status) VALUES (:title, :slug, :content, :meta_title, :meta_description, :status)';

        return DB::insertGetId($sql, [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'status' => $data['status'],
        ]);
    }
}
