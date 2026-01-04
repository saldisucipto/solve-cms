<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\FlashSession;

class DashboardController extends Controller
{
    function index(): void
    {
        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'success' => FlashSession::get('success'),
            'admin' => Auth::user(),
        ]);
    }
}
