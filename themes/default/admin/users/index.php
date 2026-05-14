<?php

use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-5">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Users</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola semua user di sistem dengan role dan permission.</p>
        </div>
        <a href="/admin/users/create" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-all">Add New User</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-medium"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-medium"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="text-left p-4">Name</th>
                    <th class="text-left p-4">Email</th>
                    <th class="text-left p-4">Role</th>
                    <th class="text-left p-4">Created</th>
                    <th class="text-center p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">Belum ada user.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b last:border-b-0 border-slate-100 hover:bg-slate-50/50">
                            <td class="p-4 font-semibold text-slate-800"><?= htmlspecialchars($user['name'] ?? '') ?></td>
                            <td class="p-4 text-slate-600 text-xs"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    <?= htmlspecialchars($user['role'] ?? 'No Role') ?>
                                </span>
                            </td>
                            <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars($user['created_at'] ?? '-') ?></td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="/admin/users/<?= (int) $user['id'] ?>/edit" class="px-2 py-1 text-xs bg-amber-100 text-amber-700 rounded hover:bg-amber-200 transition-all">Edit</a>
                                    <a href="/admin/users/<?= (int) $user['id'] ?>/delete" onclick="return confirm('Hapus user ini?')" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-all">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php View::endSection(); ?>
