<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;

class ContentPublished extends Notification
{
    public function __construct(public Post $post) {}

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
            'type' => 'content',
            'title' => 'Content published',
            'body' => $this->post->title,
            'url' => '/admin/posts/'.$this->post->id.'/edit',
        ];
    }
}
