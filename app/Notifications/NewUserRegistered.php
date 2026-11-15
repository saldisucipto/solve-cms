<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    public function __construct(public User $user) {}

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
            'type' => 'user',
            'title' => 'New user registered',
            'body' => $this->user->name.' ('.$this->user->email.') joined.',
            'url' => '/admin/users',
        ];
    }
}
