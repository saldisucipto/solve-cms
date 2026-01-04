<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Response;
use App\Helpers\FlashSession;

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

        if (!Csrf::validate(trim($_POST['_token']))) {
            http_response_code(419);
            FlashSession::set('csrf_e   rror', 'CSRF Token tidak valid');
        }
        $email = trim($_POST['email']) ?? '';
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            FlashSession::set('flash_error', 'Email & password wajib diisi');
            header('Location: /login');
            exit;
        }

        if (!Auth::attempt($email, $password)) {
            FlashSession::set('flash_error', 'Email atau password salah');
            header('Location: /login');
            exit;
        }

        FlashSession::set('success', 'Berhasil Login, Selamat Datang');
        Response::redirect('/admin');
    }

    function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
