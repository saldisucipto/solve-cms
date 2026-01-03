<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Middleware;

class DashboardController extends Controller
{
    function index(): void
    {
        Middleware::auth();
        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'admin' => Auth::user(),
        ]);
    }
}
