<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function reply(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }
}
