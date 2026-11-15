<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('activity.view');

        $search = $request->string('search')->value();
        $event = $request->string('event')->value();
        $userId = $request->integer('user');

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($search !== '', fn ($query) => $query->where('description', 'like', "%{$search}%"))
            ->when($event !== '', fn ($query) => $query->where('event', 'like', $event.'%'))
            ->when($userId > 0, fn ($query) => $query->where('user_id', $userId))
            ->latest()
            ->paginate(30)
            ->withQueryString()
            ->through(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $log->description,
                'user' => $log->user?->name,
                'subject_type' => $log->subject_type === null ? null : class_basename($log->subject_type),
                'subject_id' => $log->subject_id,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toIso8601String(),
                'created_human' => $log->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Admin/Activity/Index', [
            'logs' => $logs,
            'events' => ActivityLog::query()
                ->select('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event')
                ->all(),
            'users' => User::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
                ->all(),
            'filters' => [
                'search' => $search,
                'event' => $event,
                'user' => $userId > 0 ? $userId : null,
            ],
        ]);
    }
}
