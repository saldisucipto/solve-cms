<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CustomizerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
        Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

        Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
        Route::patch('comments/{comment}/status', [CommentController::class, 'updateStatus'])->name('comments.status');
        Route::post('comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::get('media/library', [MediaController::class, 'library'])->name('media.library');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::patch('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');

        Route::get('settings', [SiteSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SiteSettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/branding', [SiteSettingsController::class, 'uploadBranding'])->name('settings.branding.upload');
        Route::delete('settings/branding', [SiteSettingsController::class, 'destroyBranding'])->name('settings.branding.destroy');

        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
        Route::post('themes/{slug}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
        Route::post('themes/{slug}/deactivate', [ThemeController::class, 'deactivate'])->name('themes.deactivate');

        Route::get('customizer', [CustomizerController::class, 'index'])->name('customizer.index');
        Route::put('customizer/draft', [CustomizerController::class, 'saveDraft'])->name('customizer.draft');
        Route::post('customizer/publish', [CustomizerController::class, 'publish'])->name('customizer.publish');
        Route::post('customizer/reset', [CustomizerController::class, 'reset'])->name('customizer.reset');

        Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('chat/{conversation}/reply', [ChatController::class, 'reply'])->name('chat.reply');
        Route::post('chat/{conversation}/assign', [ChatController::class, 'assign'])->name('chat.assign');
        Route::post('chat/{conversation}/status', [ChatController::class, 'status'])->name('chat.status');
        Route::get('chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
        Route::post('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

        Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
        Route::get('seo/settings', [SeoController::class, 'settings'])->name('seo.settings');
        Route::put('seo/settings', [SeoController::class, 'updateSettings'])->name('seo.settings.update');
        Route::get('seo/analysis', [SeoController::class, 'analysis'])->name('seo.analysis');
        Route::get('seo/sitemap', [SeoController::class, 'sitemap'])->name('seo.sitemap');
        Route::post('seo/analyze', [SeoController::class, 'analyze'])->name('seo.analyze');
        Route::get('seo/redirects', [RedirectController::class, 'index'])->name('seo.redirects.index');
        Route::post('seo/redirects', [RedirectController::class, 'store'])->name('seo.redirects.store');
        Route::put('seo/redirects/{redirect}', [RedirectController::class, 'update'])->name('seo.redirects.update');
        Route::delete('seo/redirects/{redirect}', [RedirectController::class, 'destroy'])->name('seo.redirects.destroy');
        Route::patch('seo/redirects/{redirect}/toggle', [RedirectController::class, 'toggle'])->name('seo.redirects.toggle');

        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::put('menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
    });
