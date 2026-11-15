<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user->hasPermission('notifications.view'), 403);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $user->notifications()
                ->latest()
                ->paginate(25)
                ->through(fn (DatabaseNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'body' => $notification->data['body'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'type' => $notification->data['type'] ?? 'general',
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->hasPermission('notifications.view'), 403);

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (DatabaseNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'body' => $notification->data['body'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
        ]);
    }

    public function read(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $target = $notification->data['url'] ?? null;

        return $target !== null ? redirect($target) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
