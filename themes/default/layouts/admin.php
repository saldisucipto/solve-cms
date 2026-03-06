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
            <div class="w-full px-4 py-4 flex items-center justify-between">
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

        <main class="w-full py-6">
            <div class="relative w-full px-4 lg:grid lg:grid-cols-[260px_1fr] lg:gap-6">
                <input id="sidebar-toggle" type="checkbox" class="peer hidden" />

                <label for="sidebar-toggle"
                    class="fixed inset-0 bg-black/30 opacity-0 pointer-events-none transition lg:hidden z-30 peer-checked:opacity-100 peer-checked:pointer-events-auto"></label>

                <aside
                    class="bg-white/90 border border-blue-100 rounded-2xl shadow-sm overflow-hidden h-full fixed z-40 inset-y-0 left-0 w-64 transform -translate-x-full transition duration-200 peer-checked:translate-x-0 lg:static lg:translate-x-0 lg:z-auto">
                    <div class="p-6 bg-gradient-to-br from-blue-600 via-blue-500 to-sky-400 text-white">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-white/20 grid place-items-center text-lg font-bold">A
                            </div>
                            <div>
                                <h2 class="text-xl font-bold">Admin Panel</h2>
                                <p class="text-sm text-blue-100">System Dashboard</p>
                            </div>
                        </div>
                    </div>

                    <nav class="p-4 space-y-5">
                        <div>
                            <p class="px-4 text-xs uppercase tracking-wider text-slate-400 mb-2">Main</p>
                            <div class="space-y-1">
                                <a href="#"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-blue-50 text-blue-700 font-medium border border-blue-100">
                                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                    Dashboard
                                </a>
                            </div>
                        </div>

                        <div>
                            <p class="px-4 text-xs uppercase tracking-wider text-slate-400 mb-2">Management</p>
                            <div class="space-y-1">
                                <a href="#"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-50 text-slate-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    Users
                                </a>
                                <a href="#"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-50 text-slate-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    Roles
                                </a>
                            </div>
                        </div>

                        <div>
                            <p class="px-4 text-xs uppercase tracking-wider text-slate-400 mb-2">System</p>
                            <div class="space-y-1">
                                <a href="#"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-50 text-slate-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    Settings
                                </a>
                                <a href="#"
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-50 text-slate-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    Logs
                                </a>
                            </div>
                        </div>
                    </nav>
                </aside>

                <section class="space-y-6 w-full lg:col-start-2">
                    <?php View::yield('content'); ?>
                </section>
            </div>
        </main>
    </div>
    <!-- Modal -->
    <div id="app-modal" class="modal hidden">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title"></h3>
                <button id="modal-close">x</button>
            </div>
            <div id="modal-body"></div>
        </div>
    </div>
    <!-- End Modal -->

    <?php View::yield('inject-js') ?>

</body>

</html>