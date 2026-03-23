<?php
return [
    'name'  => $_ENV['APP_NAME'] ?? 'Solve CMS',
    'env'   => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'],
    'theme' => $_ENV['APP_THEME'] ?? 'default',
];
