<?php

namespace App\Controllers\Admin;

use App\Cms\Repositories\SeoSettingRepository;
use App\Cms\Services\SeoService;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\FlashSession;
use Throwable;

class CmsSeoController extends Controller
{
    protected SeoService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SeoService(new SeoSettingRepository());
    }

    public function index(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/seo/index', [
            'title' => 'SEO Settings',
            'admin' => Auth::user(),
            'seo' => $this->service->getSettings(),
            'success' => FlashSession::get('success'),
            'error' => FlashSession::get('error'),
        ]);
    }

    public function update(): void
    {
        Gate::authorize('admin.view');

        try {
            $this->service->save(Request::all());
            FlashSession::set('success', 'SEO settings berhasil disimpan.');
        } catch (Throwable $e) {
            FlashSession::set('error', $e->getMessage());
        }

        Response::redirect('/admin/cms/seo');
    }
}
