<?php

namespace App\Helpers;

class Debug
{
    public static function dd(...$vars)
    {
        echo "<pre style='background:#111827;color:#f9fafb;padding:16px;border-radius:8px;'>";

        foreach ($vars as $var) {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();

            echo htmlspecialchars($output);
            echo "\n";
        }

        echo "</pre>";
        die();
    }
}
