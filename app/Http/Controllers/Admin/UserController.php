<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $search = $request->string('search')->value();

        $users = User::query()
            ->with('roles:id,name,label')
            ->when($search, fn ($query, string $term) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('label')->all(),
                'role_ids' => $user->roles->pluck('id')->all(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('label')->get(['id', 'label']),
            'filters' => ['search' => $search],
            'can' => [
                'manage' => $request->user()->hasPermission('users.manage'),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'status' => $data['status'],
        ]);

        if ($request->user()->hasPermission('roles.manage') || $request->user()->isSuperAdmin()) {
            $user->roles()->sync($data['roles'] ?? []);
        }

        $this->activity->log('user.updated', "Updated user \"{$user->name}\"", $user);

        return back()->with('success', 'User updated.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        $this->activity->log(
            'user.status_changed',
            "Set user \"{$user->name}\" to {$user->status}",
            $user,
        );

        return back()->with('success', 'User status changed.');
    }
}
