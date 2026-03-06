<?php

use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="flex items-center justify-between bg-white/80 border border-blue-100 rounded-2xl p-4 shadow-sm">
    <div class="flex items-center gap-3">
        <label for="sidebar-toggle"
            class="lg:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg border border-blue-100 bg-white text-blue-600 hover:bg-blue-50">
            <span class="sr-only">Open menu</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </label>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
            <p class="text-sm text-slate-500">Overview of system status and recent activity.</p>
        </div>
    </div>
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium shadow hover:bg-blue-700" id="btn-open-modal">
        Refresh
    </button>
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium shadow hover:bg-blue-700" id="btn-open-modal-1">
        Refresh 1
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
<?php View::endSection(); ?>

<?php View::section('inject-js') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JS code here runs after DOM is ready
        document.getElementById('btn-open-modal').addEventListener('click', () => {
            $modal.open({
                title: "User Detail",
                content: `
                    <p>Nama: Saldi</p>
                    <p>Email: saldi@mail.com</p>
                `
            })
        });
        document.getElementById('btn-open-modal-1').addEventListener('click', () => {
            $modal.open({
                title: "User Detail",
                content: `
                    <p>Nama: Saldi</p>
                    <p>Email: saldi@mail.com</p>
                `
            })
        });
    });
</script>
<?php View::endSection() ?>