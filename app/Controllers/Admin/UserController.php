<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->view('admin/dashboard', [
            'title' => 'User Controller',
            'admin' => Auth::user(),
        ]);
    }
}
