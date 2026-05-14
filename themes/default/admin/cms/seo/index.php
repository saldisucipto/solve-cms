<?php

use App\Core\Csrf;
use App\Core\View;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-5">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800">SEO Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Atur default SEO global website seperti title, description, keywords, dan robots.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-medium"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-medium"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/cms/seo" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <input type="hidden" name="_token" value="<?= Csrf::token() ?>">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="seo_site_title">Default Site Title</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="seo_site_title" name="seo_site_title" type="text" value="<?= htmlspecialchars($seo['seo_site_title'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="seo_meta_description">Default Meta Description</label>
            <textarea class="w-full border border-slate-300 rounded-lg px-3 py-2" id="seo_meta_description" name="seo_meta_description" rows="4"><?= htmlspecialchars($seo['seo_meta_description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="seo_meta_keywords">Default Meta Keywords</label>
            <input class="w-full border border-slate-300 rounded-lg px-3 py-2" id="seo_meta_keywords" name="seo_meta_keywords" type="text" value="<?= htmlspecialchars($seo['seo_meta_keywords'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="seo_robots">Robots</label>
            <select class="w-full border border-slate-300 rounded-lg px-3 py-2" id="seo_robots" name="seo_robots">
                <option value="index,follow" <?= (($seo['seo_robots'] ?? '') === 'index,follow') ? 'selected' : '' ?>>index,follow</option>
                <option value="noindex,follow" <?= (($seo['seo_robots'] ?? '') === 'noindex,follow') ? 'selected' : '' ?>>noindex,follow</option>
                <option value="noindex,nofollow" <?= (($seo['seo_robots'] ?? '') === 'noindex,nofollow') ? 'selected' : '' ?>>noindex,nofollow</option>
            </select>
        </div>

        <div class="pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-all">Save SEO Settings</button>
        </div>
    </form>
</div>
<?php View::endSection(); ?>
