<?php

use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">CMS Posts Module Active</h1>
        <p class="text-sm text-slate-500 font-medium mt-2">
            Modul CMS sudah terdaftar melalui ModuleManager dan menu ini berasal dari hook admin.sidebar.menu.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Total Posts</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">0</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Draft</p>
            <p class="text-3xl font-bold text-amber-600 mt-2">0</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Published</p>
            <p class="text-3xl font-bold text-emerald-600 mt-2">0</p>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
