<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Setting;

class SettingController extends Controller
{
    public function settings()
    {
        // Render the settings page
        $this->view('admin/setting', [
            'title' => 'Settings',
            'settings' => Setting::settings(),
        ]);
    }
}
