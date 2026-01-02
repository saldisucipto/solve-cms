<?php

return [
    'name'  => $_ENV['APP_NAME'] ?? 'Solve CMS',
    'env'   => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'theme' => $_ENV['APP_THEME'] ?? 'default',
];
