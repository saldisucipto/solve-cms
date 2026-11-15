<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Notification;

class NewCommentSubmitted extends Notification
{
    public function __construct(public Comment $comment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment',
            'title' => 'New comment awaiting review',
            'body' => $this->comment->author_name.': '.str($this->comment->content)->limit(80)->value(),
            'url' => '/admin/comments',
        ];
    }
}
