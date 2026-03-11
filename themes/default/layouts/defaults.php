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

<body class="bg-blue-50 text-slate-900 font-sans">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-blue-100">
            <div class="container mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-button text-white grid place-items-center font-black text-xl shadow-lg transform rotate-3">A</div>
                    <div>
                        <p class="text-sm font-black text-slate-800 leading-none">Admin Panel</p>
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mt-1"><?= Config::get('app.name') ?></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right mr-2">
                        <p class="text-xs font-black text-slate-700">System Administrator</p>
                        <p class="text-[10px] text-slate-400 font-bold italic">Online</p>
                    </div>
                    <a href="/logout" class="h-10 w-10 rounded-xl bg-red-50 text-red-500 border-2 border-red-100 grid place-items-center hover:bg-red-500 hover:text-white transition-all duration-200 shadow-sm" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="container mx-auto px-6 py-8 flex-grow">
            <?php View::yield('content'); ?>
        </main>

        <footer class="bg-white border-t border-blue-100 py-6">
            <div class="container mx-auto px-6 text-center">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">&copy; <?= date('Y') ?> <?= Config::get('app.name') ?> Admin Dashboard</p>
            </div>
        </footer>
    </div>
</body>

</html>