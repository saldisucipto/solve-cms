<?php

/** @var App\Core\Router $router */

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\CmsPageController;
use App\Controllers\Admin\CmsPostController;
use App\Controllers\Admin\CmsSeoController;
use App\Controllers\Admin\RegisterController;
use App\Controllers\Admin\SettingController;

$router->group(['middleware' => ['guest']], function ($router) {
    $router->get('/login', [AuthController::class, 'loginForm']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [RegisterController::class, 'showForm']);
    $router->post('/register', [RegisterController::class, 'register']);
});

$router->get('/logout', [AuthController::class, 'logout']);

$router->group(['middleware' => ['auth', 'permission:admin.view']], function ($router) {
    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/users', [UserController::class, 'index']);
    $router->get('/admin/users/create', [UserController::class, 'create']);
    $router->post('/admin/users', [UserController::class, 'store']);
    $router->get('/admin/users/{id}/edit', [UserController::class, 'edit']);
    $router->post('/admin/users/{id}', [UserController::class, 'update']);
    $router->get('/admin/users/{id}/delete', [UserController::class, 'delete']);

    // CMS Content
    $router->get('/admin/cms/posts', [CmsPostController::class, 'index']);
    $router->get('/admin/cms/posts/create', [CmsPostController::class, 'create']);
    $router->post('/admin/cms/posts', [CmsPostController::class, 'store']);

    $router->get('/admin/cms/pages', [CmsPageController::class, 'index']);
    $router->get('/admin/cms/pages/create', [CmsPageController::class, 'create']);
    $router->post('/admin/cms/pages', [CmsPageController::class, 'store']);

    $router->get('/admin/cms/seo', [CmsSeoController::class, 'index']);
    $router->post('/admin/cms/seo', [CmsSeoController::class, 'update']);

    // Setting 
    $router->get('/admin/settings', [SettingController::class, 'settings']);
});
