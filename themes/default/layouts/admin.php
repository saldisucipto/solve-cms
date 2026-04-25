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
            class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 z-50 transform -translate-x-full transition-transform duration-300 peer-checked:translate-x-0 lg:translate-x-0 lg:static flex flex-col shadow-sm">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class=" h-10 w-full ">
                        <img class=" object-cover " src="/asset/img/cms-logo.webp" alt="">
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <?php
            $currentRoute = $_SERVER['REQUEST_URI'] ?? '';
            $isActive = function (string $route) use ($currentRoute): string {
                $active = ($route === '/admin') ? ($currentRoute === '/admin') : str_starts_with($currentRoute, $route);
                return $active ? 'bg-blue-50 text-blue-600 font-semibold border border-blue-100' : 'text-slate-600 font-medium hover:bg-slate-50';
            };
            $isMasterActive = str_starts_with($currentRoute, '/admin/master');
            $isSettingActive = str_starts_with($currentRoute, '/admin/settings');
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

                        <!-- Master Menu with Submenu -->
                        <div>
                            <button
                                class="master-toggle flex items-center justify-between w-full gap-3 px-3 py-2.5 rounded-lg transition-all <?= $isMasterActive ? 'bg-blue-50 text-blue-600 font-semibold border border-blue-100' : 'text-slate-600 font-medium hover:bg-slate-50' ?>">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Master
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 transform transition-transform master-icon" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </button>

                            <!-- Submenu Items -->
                            <div
                                class="master-submenu max-h-0 overflow-hidden transition-all duration-300 <?= $isMasterActive ? 'max-h-96' : '' ?>">
                                <div class="pl-3 space-y-1 mt-1">
                                    <a href="/admin/master/customer"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all text-sm <?= str_starts_with($currentRoute, '/admin/master/customer') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>">
                                        <span
                                            class="inline-block w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                        Master Customer
                                    </a>
                                    <a href="/admin/master/supplier"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all text-sm <?= str_starts_with($currentRoute, '/admin/master/supplier') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>">
                                        <span
                                            class="inline-block w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                        Master Supplier
                                    </a>
                                    <a href="/admin/master/produk"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all text-sm <?= str_starts_with($currentRoute, '/admin/master/produk') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?>">
                                        <span
                                            class="inline-block w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                        Master Produk
                                    </a>

                                </div>
                            </div>
                        </div>

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
                        <a href="/admin/settings"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $isSettingActive ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' ?> font-medium transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            General Settings
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const masterToggle = document.querySelector('.master-toggle');
                const masterSubmenu = document.querySelector('.master-submenu');
                const masterIcon = document.querySelector('.master-icon');

                masterToggle.addEventListener('click', function() {
                    const isOpen = masterSubmenu.classList.contains('max-h-96');

                    if (isOpen) {
                        masterSubmenu.classList.remove('max-h-96');
                        masterSubmenu.classList.add('max-h-0');
                        masterIcon.style.transform = 'rotate(0deg)';
                    } else {
                        masterSubmenu.classList.add('max-h-96');
                        masterSubmenu.classList.remove('max-h-0');
                        masterIcon.style.transform = 'rotate(180deg)';
                    }
                });
            });
        </script>

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

    <!-- Modal Components Injects -->
    <?php View::yield('modal'); ?>

    <!-- Modals & Injections -->
    <?php View::yield('inject-js'); ?>
</body>

</html>