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
        <script type="module" src="http://localhost:5173/js/app.js"></script>
    <?php else : ?>
        <!-- Menambahkan Asste Vite -->
        <?php $asset = Vite::asset('resources/js/app.js') ?>
        <?php foreach ($asset['css'] as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
        <script type="module" src="<?= $asset['js'] ?>"></script>
    <?php endif; ?>
</head>

<body class="bg-gray-100 text-gray-900">

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main class="container mx-auto p-4">
        <?php View::yield('content'); ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>