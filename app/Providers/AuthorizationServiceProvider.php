<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Post::class => PostPolicy::class,
        Page::class => PagePolicy::class,
        Comment::class => CommentPolicy::class,
        Media::class => MediaPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        foreach (config('solve.permissions', []) as $permissions) {
            foreach (array_keys($permissions) as $permission) {
                $name = (string) $permission;
                Gate::define($name, fn (User $user): bool => $user->hasPermission($name));
            }
        }
    }
}
