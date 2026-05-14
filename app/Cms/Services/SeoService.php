<?php

namespace App\Cms\Services;

use App\Cms\Repositories\SeoSettingRepository;

class SeoService
{
    public function __construct(protected SeoSettingRepository $repository)
    {
    }

    public function getSettings(): array
    {
        return [
            'seo_site_title' => $this->repository->getByKey('seo_site_title', ''),
            'seo_meta_description' => $this->repository->getByKey('seo_meta_description', ''),
            'seo_meta_keywords' => $this->repository->getByKey('seo_meta_keywords', ''),
            'seo_robots' => $this->repository->getByKey('seo_robots', 'index,follow'),
        ];
    }

    public function save(array $input): void
    {
        $this->repository->set('seo_site_title', trim((string) ($input['seo_site_title'] ?? '')));
        $this->repository->set('seo_meta_description', trim((string) ($input['seo_meta_description'] ?? '')));
        $this->repository->set('seo_meta_keywords', trim((string) ($input['seo_meta_keywords'] ?? '')));
        $this->repository->set('seo_robots', trim((string) ($input['seo_robots'] ?? 'index,follow')));
    }
}
