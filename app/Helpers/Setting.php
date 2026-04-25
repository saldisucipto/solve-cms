<?php

namespace App\Helpers;

use App\Services\SettingService;

class Setting
{
    public static function settings()
    {
        return SettingService::getAllSettings();
    }

    public static function setting(string $key, $default = null)
    {
        return SettingService::get($key, $default);
    }
}
