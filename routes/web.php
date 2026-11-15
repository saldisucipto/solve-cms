<?php

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\ChatController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\TagController;
use App\Http\Controllers\Seo\RobotsController;
use App\Http\Controllers\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', SitemapController::class)->name('seo.sitemap');
Route::get('robots.txt', RobotsController::class)->name('seo.robots');

Route::middleware('active')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');

    Route::get('blog', BlogController::class)->name('frontend.blog');
    Route::get('blog/{slug}', PostController::class)->name('frontend.posts.show');
    Route::post('blog/{slug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('frontend.comments.store');

    Route::get('category/{slug}', CategoryController::class)->name('frontend.categories.show');
    Route::get('tag/{slug}', TagController::class)->name('frontend.tags.show');
    Route::get('search', SearchController::class)->name('frontend.search');

    Route::get('chat', [ChatController::class, 'show'])->middleware('throttle:60,1')->name('frontend.chat.show');
    Route::post('chat/start', [ChatController::class, 'start'])->middleware('throttle:10,1')->name('frontend.chat.start');
    Route::post('chat/messages', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('frontend.chat.messages');
    Route::post('chat/read', [ChatController::class, 'read'])->middleware('throttle:60,1')->name('frontend.chat.read');
});

Route::middleware(['auth', 'verified', 'active'])
    ->get('dashboard', fn () => redirect()->route('admin.dashboard'))
    ->name('dashboard');

require __DIR__.'/admin.php';

require __DIR__.'/settings.php';

/*
| Pages live at the site root for clean URLs. This route is registered last and
| excludes every path that belongs to another part of the application, so it
| never shadows authentication, admin or SEO endpoints.
*/
$reserved = implode('|', [
    'admin', 'login', 'logout', 'register', 'forgot-password', 'reset-password',
    'email', 'verify-email', 'confirm-password', 'two-factor', 'passkey', 'user',
    'settings', 'dashboard', 'auth', 'api', 'storage', 'build', 'up', '_inertia',
    'well-known', 'blog', 'category', 'tag', 'search', 'chat', 'sitemap\.xml', 'robots\.txt',
]);

Route::middleware('active')
    ->get('/{slug}', PageController::class)
    ->where('slug', '^(?!('.$reserved.')$)[A-Za-z0-9\-_]+$')
    ->name('frontend.pages.show');
