<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Services\Notifications\Notifier;
use Illuminate\Auth\Events\Registered;

class NotifyAdminsOfNewUser
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->notifier->toPermission(
            'users.manage',
            new NewUserRegistered($event->user),
            $event->user->id,
        );
    }
}
