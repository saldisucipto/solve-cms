<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('admin/login', [
            'title' => 'Admin Login'
        ]);
    }

    function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attemps($email, $password)) {
            header('Location: /admin');
            exit;
        }

        $this->view('admin/login', [
            'title' => 'Admin Login',
            'error' => 'Email Atau Passwordnya salah'
        ]);
    }

    function logout(): void
    {
        Auth::logout();
        header('Location: /admin/login');
        exit;
    }
}
