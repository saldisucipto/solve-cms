<?php

namespace App\Core;

use App\Helpers\Debug;

class Controller
{
    public function __construct()
    {
        $this->csrf();
    }

    // fungsi helper view disini
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    // fungsi handle CSRF 
    protected function csrf(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $token = $_POST['_token'] ?? null;
            if (!$token || !Csrf::validate($token)) {
                http_response_code(419);
                die('Invalid CSRF token');
            }
        }
    }
}
