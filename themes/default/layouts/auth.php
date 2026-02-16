<?php

use App\Core\Config;
use App\Core\Vite;
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? Config::get('app.name') ?></title>

    <?php if (Config::get('app.env') === 'development'): ?>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/js/app.js"></script>
    <?php else : ?>
        <?php $asset = Vite::asset('resources/js/app.js') ?>
        <?php foreach ($asset['css'] as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
        <script type="module" src="<?= $asset['js'] ?>"></script>
    <?php endif; ?>
</head>

<body class=" min-h-screen w-full flex flex-col justify-center">
    <?php View::yield('content'); ?>
</body>

</html>