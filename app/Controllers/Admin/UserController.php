<?php

namespace App\Controllers\Admin;

use App\Users\Repositories\UserRepository;
use App\Users\Services\UserService;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\FlashSession;
use Throwable;

class UserController extends Controller
{
    protected UserService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UserService(new UserRepository());
    }

    public function index(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/users/index', [
            'title' => 'Users',
            'admin' => Auth::user(),
            'users' => $this->service->list(),
            'success' => FlashSession::get('success'),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function create(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/users/create', [
            'title' => 'Add New User',
            'admin' => Auth::user(),
            'roles' => $this->service->getRoles(),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function store(): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->create(Request::all());
            FlashSession::set('success', 'User berhasil ditambahkan.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
            Response::redirect('/admin/users/create');
        }

        Response::redirect('/admin/users');
    }

    public function edit(int $id): void
    {
        Gate::authorize('admin.view');

        $user = $this->service->find($id);

        if ($user === null) {
            Response::redirect('/admin/users');
        }

        $this->view('admin/users/edit', [
            'title' => 'Edit User',
            'admin' => Auth::user(),
            'user' => $user,
            'roles' => $this->service->getRoles(),
            'error' => FlashSession::get('error'),
            'success' => FlashSession::get('success'),
        ]);
    }

    public function update(int $id): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->update($id, Request::all());
            FlashSession::set('success', 'User berhasil diperbarui.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
            Response::redirect("/admin/users/{$id}/edit");
        }

        Response::redirect('/admin/users');
    }

    public function delete(int $id): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->delete($id);
            FlashSession::set('success', 'User berhasil dihapus.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
        }

        Response::redirect('/admin/users');
    }
}
