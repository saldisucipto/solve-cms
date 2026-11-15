<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('posts.view');
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->hasPermission('posts.update')) {
            return true;
        }

        return $this->updateOwn($user, $post);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.update');
    }

    public function updateOwn(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.update.own') && $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.delete');
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission('posts.publish');
    }

    /**
     * Determine whether the user may edit the given post in any capacity.
     */
    public function edit(User $user, Post $post): bool
    {
        return $this->update($user, $post) || $this->updateOwn($user, $post);
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.delete');
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.delete');
    }
}
