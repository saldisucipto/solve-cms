<?php

namespace App\Core;

class Controller
{
    // fungsi helper view disini
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }
}
