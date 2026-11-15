<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Sends notifications only to users who are authorised to see them.
 */
class Notifier
{
    public function toUser(?User $user, Notification $notification): void
    {
        if ($user === null || ! $user->isActive()) {
            return;
        }

        $this->send($user, $notification);
    }

    /**
     * Notify every active user holding the given permission (super admins always qualify).
     */
    public function toPermission(string $permission, Notification $notification, ?int $exceptUserId = null): void
    {
        $this->recipients($permission)
            ->reject(fn (User $user): bool => $exceptUserId !== null && $user->id === $exceptUserId)
            ->each(fn (User $user) => $this->send($user, $notification));
    }

    private function send(User $user, Notification $notification): void
    {
        try {
            $user->notify($notification);
        } catch (BroadcastException $exception) {
            // The database notification is written first; only live delivery failed.
            Log::warning('Notification broadcast failed; stored in database only.', [
                'notification' => $notification::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function recipients(string $permission): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->where(function ($query) use ($permission): void {
                $query->whereHas('roles', fn ($role) => $role->where('name', 'super-admin'))
                    ->orWhereHas('roles.permissions', fn ($perm) => $perm->where('name', $permission));
            })
            ->get();
    }
}
