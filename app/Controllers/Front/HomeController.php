<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $this->view('home', []);
    }

    public function show(string $slug)
    {
        $post = (new Post())->findBySlug($slug);
        if (!$post) {
            http_response_code(404);
            echo 'Post Not Found';
            return;
        }
        $this->view('post', [
            'title' => $post['title'],
            'slug'  => $post['slug'],
            'body'  => $post['body'],
        ]);
    }
}
