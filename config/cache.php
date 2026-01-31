<?php

return [
    'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
    'storage' => BASE_PATH . '/storage/cache',
];
