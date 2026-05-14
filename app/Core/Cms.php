<?php

namespace App\Core;

use App\Core\Support\EventDispatcher;
use App\Core\Support\HookManager;
use RuntimeException;

class Cms
{
    protected static ?CmsKernel $kernel = null;

    public static function isBooted(): bool
    {
        return self::$kernel !== null;
    }

    public static function setKernel(CmsKernel $kernel): void
    {
        self::$kernel = $kernel;
    }

    public static function kernel(): CmsKernel
    {
        if (self::$kernel === null) {
            throw new RuntimeException('CMS kernel is not initialized.');
        }

        return self::$kernel;
    }

    public static function container(): Container
    {
        return self::kernel()->container();
    }

    public static function hooks(): HookManager
    {
        return self::kernel()->hooks();
    }

    public static function events(): EventDispatcher
    {
        return self::kernel()->events();
    }
}
