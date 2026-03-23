<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;
use App\Core\Middleware;
use App\Helpers\FlashSession;

class DashboardController extends Controller
{
    function index(): void
    {
        Gate::authorize('admin.view');
        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'success' => FlashSession::get('success'),
            'admin' => Auth::user(),
        ]);
    }

    function master_produk(): void
    {
        Gate::authorize('admin.view');
        $this->view('admin/master/produk', [
            'title' => 'Master Produk',
            'success' => FlashSession::get('success'),
            'admin' => Auth::user(),
        ]);
    }

    public function master_customer(): void
    {
        Gate::authorize('admin.view');
        $this->view('admin/master/customer', [
            'title' => 'Master Customer',
            'success' => FlashSession::get('success'),
            'admin' => Auth::user(),
        ]);
    }
}
