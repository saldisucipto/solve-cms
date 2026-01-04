<?php

/** @var App\Core\Router $router */

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($router) {
    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/users', [DashboardController::class, 'index']);
});
