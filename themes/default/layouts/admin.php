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

<body class="bg-slate-50 text-slate-900 font-sans">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar Toggle for Mobile -->
        <input id="sidebar-toggle" type="checkbox" class="peer hidden" />
        <label for="sidebar-toggle"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity lg:hidden z-40 peer-checked:opacity-100 peer-checked:pointer-events-auto"></label>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 w-64 bg-white border-r border-slate-200 z-50 transform -translate-x-full transition-transform duration-300 peer-checked:translate-x-0 lg:translate-x-0 lg:static flex flex-col shadow-sm">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class=" h-10 w-full ">
                        <img class=" object-cover " src="/asset/img/erp-logo.webp" alt="">
                    </div>
                    <!-- <div
                        class="h-10 w-10 rounded-lg bg-blue-600 text-white grid place-items-center font-bold text-xl shadow-sm">
                        S</div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight"><?= Config::get('app.name') ?></h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Administration</p>
                    </div> -->
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <?php
            $currentRoute = $_SERVER['REQUEST_URI'] ?? '';
            $isActive = function (string $route) use ($currentRoute): string {
                // Untuk /admin, kita cek exact match agar tidak bentrok dengan sub-route seperti /admin/components
                $active = ($route === '/admin') ? ($currentRoute === '/admin') : str_starts_with($currentRoute, $route);
                return $active ? 'bg-blue-50 text-blue-600 font-semibold border border-blue-100' : 'text-slate-600 font-medium hover:bg-slate-50';
            };
            ?>
            <nav class="flex-grow p-4 space-y-6 overflow-y-auto">
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Main Menu</p>
                    <div class="space-y-1">
                        <a href="/admin"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all <?= $isActive('/admin') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="/admin/components"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all <?= $isActive('/admin/components') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            UI Components
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Management</p>
                    <div class="space-y-1">
                        <a href="/admin/users"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all <?= $isActive('/admin/users') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Users
                        </a>
                        <a href="#"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 font-medium hover:bg-slate-50 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Content
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-100">
                <a href="/logout"
                    class="flex items-center justify-center gap-2 w-full py-2.5 rounded-lg bg-red-50 text-red-600 font-bold text-xs hover:bg-red-600 hover:text-white transition-all shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-grow flex flex-col min-w-0">
            <!-- Top Header -->
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center px-6 justify-between sticky top-0 z-[100] lg:z-[100]">
                <div class="flex items-center gap-4">
                    <label for="sidebar-toggle"
                        class="lg:hidden h-9 w-9 rounded-lg bg-slate-100 text-slate-600 grid place-items-center cursor-pointer hover:bg-slate-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </label>
                    <div class="hidden sm:block">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Status: <span
                                class="text-emerald-500">System Live</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-8 w-8 rounded-lg bg-blue-600 border-2 border-white shadow-sm"></div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6 flex-grow">
                <?php View::yield('content'); ?>
            </main>

            <!-- Footer -->
            <footer class="p-6 pt-0 border-t border-slate-100 bg-white">
                <div class="text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">&copy; <?= date('Y') ?>
                        <?= Config::get('app.name') ?> Admin Ecosystem</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modals & Injections -->
    <?php View::yield('inject-js'); ?>
</body>

</html>