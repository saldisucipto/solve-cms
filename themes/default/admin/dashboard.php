<?php

use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 bg-blue-600 text-white rounded-xl grid place-items-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 012-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">System Dashboard</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Manage your lightweight CMS & framework</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 flex items-center gap-2 text-sm"
                id="btn-refresh">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-300 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 grid place-items-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Users</span>
            </div>
            <p class="text-3xl font-bold text-slate-800 mb-2">--</p>
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full w-1/3 bg-blue-600 rounded-full"></div>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-300 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-lg bg-sky-50 text-sky-600 grid place-items-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Sessions</span>
            </div>
            <p class="text-3xl font-bold text-slate-800 mb-2">--</p>
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full w-1/2 bg-sky-500 rounded-full"></div>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-blue-300 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 grid place-items-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">System Status</span>
            </div>
            <p class="text-3xl font-bold text-emerald-600 mb-2">ONLINE</p>
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full w-4/5 bg-emerald-500 rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Welcome Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div
                class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 font-bold text-[11px] uppercase tracking-wide mb-4">
                Welcome Back
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-3">Ready to build something great?</h3>
            <p class="text-slate-600 font-medium leading-relaxed mb-6">
                You are currently using the Solve CMS engine. All core systems are optimized and ready for development.
            </p>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                <div
                    class="h-8 w-8 rounded-lg bg-white shadow-sm grid place-items-center text-blue-500 border border-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 italic">"Simplicity is the ultimate sophistication."</p>
            </div>
        </div>

        <!-- Cache Debug Card -->
        <div class="bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="h-8 w-8 rounded-lg bg-blue-500/10 text-blue-400 grid place-items-center border border-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Cache Inspector</h3>
                </div>
                <span
                    class="text-[10px] font-bold text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded-full uppercase tracking-wider border border-blue-400/20">DEBUG</span>
            </div>

            <div class="bg-black/30 rounded-xl p-4 border border-white/5 font-mono text-[13px] leading-relaxed">
                <div class="flex items-center gap-2 mb-3 text-slate-500 text-xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>System log output...</span>
                </div>
                <div class="text-blue-300/90 overflow-auto max-h-[100px] scrollbar-hide">
                    <pre><?= htmlspecialchars(print_r($data, true)) ?></pre>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>

<?php View::section('inject-js') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btn-refresh')?.addEventListener('click', () => {
            location.reload();
        });
    });
</script>
<?php View::endSection() ?>