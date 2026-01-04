<?php

namespace App\Core;

class View
{
    protected static string $layout;
    protected static array $sections = [];
    protected static array $sectionStack = [];

    public static function extend(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function section(string $name): void
    {
        self::$sectionStack[] = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        $name = array_pop(self::$sectionStack);
        self::$sections[$name] = ob_get_clean();
    }

    public static function yield(string $name): void
    {
        echo self::$sections[$name] ?? '';
    }

    public static function render(string $view, array $data = []): void
    {
        extract($data);

        require dirname(__DIR__, 2) . "/themes/default/{$view}.php";

        if (isset(self::$layout)) {
            require dirname(__DIR__, 2) . "/themes/default/" . self::$layout . ".php";
        }
    }
}
