<?php

use App\Core\Csrf;
use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-5">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Add New Page</h1>
            <p class="text-sm text-slate-500 mt-1">Buat static page baru untuk website.</p>
        </div>
        <a href="/admin/cms/pages" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-all">Back to Pages</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-medium"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/cms/pages" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <input type="hidden" name="_token" value="<?= Csrf::token() ?>">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="title">Title</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="title" name="title" type="text" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="slug">Slug (optional)</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="slug" name="slug" type="text">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="content">Content</label>
            <textarea class="w-full border border-slate-300 rounded-lg px-3 py-2" id="content" name="content" rows="8" required></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="meta_title">SEO Meta Title</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="meta_title" name="meta_title" type="text">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="status">Status</label>
                <select class="w-full border border-slate-300 rounded-lg px-3 py-2" id="status" name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="meta_description">SEO Meta Description</label>
            <textarea class="w-full border border-slate-300 rounded-lg px-3 py-2" id="meta_description" name="meta_description" rows="3"></textarea>
        </div>

        <div class="pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-all">Save Page</button>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
