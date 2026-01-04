<?php

namespace App\Core;

class Response
{
    static function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }
}
