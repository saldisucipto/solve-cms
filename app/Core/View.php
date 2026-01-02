<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $theme = 'default';

        $viewFile = BASE_PATH . "/themes/{$theme}/{$view}.php";
        $layout = BASE_PATH . "/themes/{$theme}/layouts/main.php";

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View $view Not Found!";
            return;
        }

        extract($data, EXTR_SKIP);

        // buffer view
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // load layut 
        require $layout;
    }
}
