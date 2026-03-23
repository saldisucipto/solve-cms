<?php

/** @var App\Core\Router $router */

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ComponentController;
use App\Controllers\Admin\Master\MasterController;
use App\Controllers\Admin\RegisterController;

$router->group(['middleware' => ['guest']], function ($router) {
    $router->get('/login', [AuthController::class, 'loginForm']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [RegisterController::class, 'showForm']);
    $router->post('/register', [RegisterController::class, 'register']);
});

$router->get('/logout', [AuthController::class, 'logout']);

$router->group(['middleware' => ['auth', 'permission:admin.view']], function ($router) {
    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/users', [DashboardController::class, 'index']);
    $router->get('/admin/components', [ComponentController::class, 'index']);
    $router->post('/admin/upload', [ComponentController::class, 'upload']);

    // Master Module 
    $router->get('/admin/master/produk', [DashboardController::class, 'master_produk']);
    $router->get('/admin/master/customer', [DashboardController::class, 'master_customer']);

    // Store Master Module
    $router->post('/admin/master/customer/store', [MasterController::class, 'store_master_customer']);
});
