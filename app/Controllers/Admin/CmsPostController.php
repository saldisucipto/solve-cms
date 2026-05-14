<?php

namespace App\Controllers\Admin;

use App\Cms\Repositories\PostRepository;
use App\Cms\Services\PostService;
use App\Cms\Services\SlugService;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\FlashSession;
use Throwable;

class CmsPostController extends Controller
{
    protected PostService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PostService(new PostRepository(), new SlugService());
    }

    public function index(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/posts/index', [
            'title' => 'Posts',
            'admin' => Auth::user(),
            'posts' => $this->service->list(),
            'success' => FlashSession::get('success'),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function create(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/posts/create', [
            'title' => 'Create Post',
            'admin' => Auth::user(),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function store(): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->create(Request::all());
            FlashSession::set('success', 'Post berhasil dibuat.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
            Response::redirect('/admin/cms/posts/create');
        }

        Response::redirect('/admin/cms/posts');
    }
}
