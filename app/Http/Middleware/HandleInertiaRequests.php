<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user instanceof User ? $this->authenticatedUser($user) : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'toast' => function () use ($request): ?array {
                    $session = $request->session();

                    return match (true) {
                        $session->has('success') => ['type' => 'success', 'message' => (string) $session->get('success')],
                        $session->has('error') => ['type' => 'error', 'message' => (string) $session->get('error')],
                        default => null,
                    };
                },
            ],
            'siteUrl' => fn (): string => app(SiteSettings::class)->siteUrl(),
            'branding' => fn (): array => [
                'name' => (string) app(SiteSettings::class)->get('site_name', config('app.name')),
                'logo' => app(SiteSettings::class)->get('dashboard_logo_path'),
                'favicon' => app(SiteSettings::class)->get('favicon_path'),
            ],
            'cspNonce' => fn (): ?string => $request->attributes->get('csp_nonce'),
            'notificationFeed' => function () use ($user): ?array {
                if (! $user instanceof User || ! $user->hasPermission('notifications.view')) {
                    return null;
                }

                return [
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
                        ])->all(),
                ];
            },
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function authenticatedUser(User $user): array
    {
        $user->loadMissing('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_path,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
            'status' => $user->status,
            'roles' => $user->roles->pluck('label')->all(),
            'is_super_admin' => $user->isSuperAdmin(),
            'permissions' => $user->permissionNames()->all(),
        ];
    }
}
