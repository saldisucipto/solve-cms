<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Helpers\FlashSession;
use App\Core\Response;

class RegisterController extends Controller
{
    public function showForm(): void
    {
        $this->view('admin/register', [
            'title' => 'Admin Register'
        ]);
    }

    public function register(): void
    {
        if (!Csrf::validate(trim($_POST['_token']))) {
            FlashSession::set('csrf_error', 'CSRF Token invalid');
            Response::redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            FlashSession::set('flash_error', 'All fields are required');
            Response::redirect('/register');
        }

        // Logic to save user to DB would go here.
        // For now, let's mock it and redirect to login.
        
        FlashSession::set('success', 'Registration successful! Please login.');
        Response::redirect('/login');
    }
}
