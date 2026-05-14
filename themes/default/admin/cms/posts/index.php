<?php

use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-5">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Posts</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola postingan blog dan status publish seperti WordPress.</p>
        </div>
        <a href="/admin/cms/posts/create" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-all">Add New Post</a>
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
                    <th class="text-left p-4">Title</th>
                    <th class="text-left p-4">Slug</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-left p-4">Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="4" class="p-6 text-center text-slate-500">Belum ada post.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr class="border-b last:border-b-0 border-slate-100 hover:bg-slate-50/50">
                            <td class="p-4 font-semibold text-slate-800"><?= htmlspecialchars($post['title'] ?? '') ?></td>
                            <td class="p-4 text-slate-600">/<?= htmlspecialchars($post['slug'] ?? '') ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= (($post['status'] ?? '') === 'published') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= htmlspecialchars($post['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td class="p-4 text-slate-500"><?= htmlspecialchars($post['created_at'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php View::endSection(); ?>
