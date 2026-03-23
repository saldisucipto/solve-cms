<?php

use App\Core\Config;
use App\Core\Vite;
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? Config::get('app.name') ?></title>

    <?php if (Config::get('app.env') === 'development'): ?>
        <script type="module" src="http://localhost:5173/js/app.js"></script>
    <?php else : ?>
        <?php $asset = Vite::asset('resources/js/app.js') ?>
        <?php foreach ($asset['css'] as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
        <script type="module" src="<?= $asset['js'] ?>"></script>
    <?php endif; ?>
</head>

<body class="bg-white text-slate-900 font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
            <div class="container mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-lg bg-blue-600 text-white grid place-items-center font-bold text-xl shadow-sm">
                        S</div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none"><?= Config::get('app.name') ?></p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Lightweight CMS &
                            Framework</p>
                    </div>
                </div>

                <?php if (!isset($_SESSION['user'])): ?>
                    <div class="flex items-center gap-4">
                        <a href="/login"
                            class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all duration-200 text-sm">
                            Login
                        </a>
                        <a href="/register"
                            class="px-5 py-2 bg-white text-slate-600 font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-all duration-200 text-sm">
                            Register
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-4">
                        <a href="/admin"
                            class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all duration-200 text-sm">
                            Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Content -->
        <main class="flex-grow">
            <?php View::yield('content'); ?>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-50 border-t border-slate-100 py-12">
            <div class="container mx-auto px-6 text-center">
                <div class="flex justify-center mb-6">
                    <div
                        class="h-8 w-8 rounded-lg bg-blue-600 text-white grid place-items-center font-bold text-lg shadow-sm">
                        S</div>
                </div>
                <p class="text-slate-500 text-sm font-medium">Built for the PHP Ecosystem</p>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-3">&copy; <?= date('Y') ?>
                    <?= Config::get('app.name') ?>. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>

</html>