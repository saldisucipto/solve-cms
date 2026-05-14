<?php

namespace App\Cms\Services;

use App\Cms\Repositories\PostRepository;
use InvalidArgumentException;

class PostService
{
    public function __construct(
        protected PostRepository $repository,
        protected SlugService $slugService
    ) {
    }

    public function list(): array
    {
        return $this->repository->paginate();
    }

    public function create(array $input): int
    {
        $title = trim((string) ($input['title'] ?? ''));
        $content = trim((string) ($input['content'] ?? ''));

        if ($title === '' || $content === '') {
            throw new InvalidArgumentException('Judul dan konten wajib diisi.');
        }

        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slugBase = $slugInput !== '' ? $this->slugService->slugify($slugInput) : $this->slugService->slugify($title);
        $slug = $this->uniqueSlug($slugBase);

        return $this->repository->create([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) ($input['excerpt'] ?? '')),
            'content' => $content,
            'meta_title' => trim((string) ($input['meta_title'] ?? '')),
            'meta_description' => trim((string) ($input['meta_description'] ?? '')),
            'status' => in_array(($input['status'] ?? 'draft'), ['draft', 'published'], true)
                ? $input['status']
                : 'draft',
        ]);
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 1;

        while ($this->repository->findBySlug($slug) !== null) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
