<?php

namespace App\Controllers\Admin;

use App\Cms\Repositories\PageRepository;
use App\Cms\Services\PageService;
use App\Cms\Services\SlugService;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\FlashSession;
use Throwable;

class CmsPageController extends Controller
{
    protected PageService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PageService(new PageRepository(), new SlugService());
    }

    public function index(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/pages/index', [
            'title' => 'Pages',
            'admin' => Auth::user(),
            'pages' => $this->service->list(),
            'success' => FlashSession::get('success'),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function create(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/pages/create', [
            'title' => 'Create Page',
            'admin' => Auth::user(),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function store(): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->create(Request::all());
            FlashSession::set('success', 'Page berhasil dibuat.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
            Response::redirect('/admin/cms/pages/create');
        }

        Response::redirect('/admin/cms/pages');
    }
}
