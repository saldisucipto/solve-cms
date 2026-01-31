<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Helpers\Cache;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $data_cache = Cache::remember('test', 300, function () {
            return 'Data Cache';
        });
        $data_cache1 = Cache::remember('test_lagi', 300, function () {
            return 'Data Cache1';
        });
        // $test = Cache::put('test', 'testaja');
        // var_dump($test);
        View::render('home', ['data' => [$data_cache, $data_cache1]]);
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
