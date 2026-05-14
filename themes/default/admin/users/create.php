<?php

use App\Core\Csrf;
use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-5">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Add New User</h1>
            <p class="text-sm text-slate-500 mt-1">Buat akun user baru dengan role dan permission yang sesuai.</p>
        </div>
        <a href="/admin/users" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-all">Back to Users</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-medium"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/users" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <input type="hidden" name="_token" value="<?= Csrf::token() ?>">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="name">Full Name</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="name" name="name" type="text" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email Address</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="email" name="email" type="email" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="password">Password</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="password" name="password" type="password" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="password_confirm">Confirm Password</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="password_confirm" name="password_confirm" type="password" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="role_id">Role</label>
            <select class="w-full border border-slate-300 rounded-lg px-3 py-2" id="role_id" name="role_id">
                <option value="">-- Select Role --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-all">Create User</button>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
