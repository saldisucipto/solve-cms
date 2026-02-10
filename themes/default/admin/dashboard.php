<?php

use App\Core\View;

View::extend('layouts/defaults');
?>

<?php View::section('content'); ?>
<div class="w-full">
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 w-full">
        <aside class="bg-white/90 border border-blue-100 rounded-2xl shadow-sm overflow-hidden h-full">
            <div class="p-6 bg-gradient-to-br from-blue-600 via-blue-500 to-sky-400 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-white/20 grid place-items-center text-lg font-bold">A</div>
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

        <main class="space-y-6 w-full">
            <div class="flex items-center justify-between bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-sm text-slate-500">Overview of system status and recent activity.</p>
                </div>
                <button
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium shadow hover:bg-blue-700">
                    Refresh
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Total Users</p>
                    <p class="text-2xl font-bold text-slate-800">--</p>
                    <div class="mt-3 h-1.5 w-full bg-blue-100 rounded-full">
                        <div class="h-1.5 w-1/3 bg-blue-500 rounded-full"></div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Active Sessions</p>
                    <p class="text-2xl font-bold text-slate-800">--</p>
                    <div class="mt-3 h-1.5 w-full bg-blue-100 rounded-full">
                        <div class="h-1.5 w-1/2 bg-blue-500 rounded-full"></div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-sm text-slate-500">System Status</p>
                    <p class="text-2xl font-bold text-green-600">OK</p>
                    <div class="mt-3 h-1.5 w-full bg-blue-100 rounded-full">
                        <div class="h-1.5 w-4/5 bg-green-500 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-800">Welcome</h3>
                    <span class="text-xs text-blue-700 bg-blue-50 px-2 py-1 rounded-full">System</span>
                </div>
                <p class="text-slate-600">Selamat datang pakai Cache.</p>
            </div>

            <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Cache Data</h3>
                <pre
                    class="text-sm text-slate-600 bg-blue-50/60 border border-blue-100 rounded-xl p-4 overflow-auto"><?= var_dump($data) ?></pre>
            </div>
        </main>
    </div>
</div>
<?php View::endSection(); ?>