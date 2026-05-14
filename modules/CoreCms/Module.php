<?php

namespace Modules\CoreCms;

use App\Core\ServiceProvider;

class Module extends ServiceProvider
{
    public function register(): void
    {
        $this->hooks->addFilter('admin.sidebar.menu', function (array $menus): array {
            $menus[] = [
                'href' => '/admin/cms',
                'label' => 'Content Manager',
                'children' => [
                    [
                        'href' => '/admin/cms/posts',
                        'label' => 'Posts',
                    ],
                    [
                        'href' => '/admin/cms/pages',
                        'label' => 'Pages',
                    ],
                    [
                        'href' => '/admin/cms/seo',
                        'label' => 'SEO Settings',
                    ],
                ],
            ];

            return $menus;
        });

        $this->hooks->addFilter('cms.supported_post_statuses', function (array $statuses): array {
            $statuses[] = 'draft';
            $statuses[] = 'published';
            $statuses[] = 'trash';

            return array_values(array_unique($statuses));
        });

        $this->hooks->addFilter('cms.reserved_slugs', function (array $slugs): array {
            $slugs = array_merge($slugs, ['admin', 'login', 'register', 'api']);

            return array_values(array_unique($slugs));
        });
    }

    public function boot(): void
    {
        $this->events->listen('router.route_matched', function (array $route): void {
            if (($route['uri'] ?? '') === '/admin') {
                // Placeholder hook untuk analytics dashboard request.
            }
        });
    }
}
