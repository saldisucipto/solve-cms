<?php

namespace App\Controllers\Front;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->view('home', []);
    }

    public function show(string $slug)
    {
        $this->view('post', [
            'title' => 'Post :' . $slug,
            'slug' => $slug,
        ]);
    }
}
