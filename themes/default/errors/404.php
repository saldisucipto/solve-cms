<?php

use App\Core\Config;
use App\Core\Vite;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
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
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-black text-blue-600 opacity-20">404</h1>
            <div class="relative -mt-20">
                <h2 class="text-4xl font-black text-slate-800 mb-4">Page Not Found</h2>
                <p class="text-slate-500 font-medium leading-relaxed">Oops! The page you're looking for seems to have wandered off into the digital void.</p>
            </div>
        </div>
        
        <div class="space-y-4">
            <a href="/" class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
