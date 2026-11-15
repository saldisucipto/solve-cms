<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $event, string $description, ?Model $subject = null, ?User $user = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user !== null ? $user->id : auth()->id(),
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
