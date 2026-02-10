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

<body class="bg-blue-50 text-slate-900">
    <div class="min-h-screen bg-gradient-to-b from-blue-100 via-blue-50 to-white">
        <header class="bg-white/70 backdrop-blur border-b border-blue-100">
            <div class="container mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-9 w-9 rounded-lg bg-blue-600 text-white grid place-items-center font-bold">A</div>
                    <div>
                        <p class="text-sm text-blue-600 font-semibold">Admin</p>
                        <p class="text-xs text-slate-500"><?= Config::get('app.name') ?></p>
                    </div>
                </div>
                <div class="text-sm text-slate-500">System Panel</div>
            </div>
        </header>
        <main class="w-full px-4 py-6">
            <?php View::yield('content'); ?>
        </main>
    </div>
</body>

</html>