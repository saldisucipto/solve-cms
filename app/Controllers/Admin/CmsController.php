<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Gate;

class CmsController extends Controller
{
    public function posts(): void
    {
        Gate::authorize('admin.view');

        $this->view('admin/cms/posts', [
            'title' => 'CMS Posts',
            'admin' => Auth::user(),
        ]);
    }
}
