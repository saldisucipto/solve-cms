<?php

/** @var App\Core\Router $router */

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;

$router->get('/admin/login', [AuthController::class, 'loginForm']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->get('/admin/logout', [AuthController::class, 'logout']);

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function ($router) {
    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/users', [DashboardController::class, 'index']);
});
