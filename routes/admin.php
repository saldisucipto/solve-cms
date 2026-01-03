<?php

/** @var App\Core\Router $router */


$router->get('/admin/login', ['Admin\AuthController', 'loginForm']);
$router->post('/admin/login', ['Admin\AuthController', 'login']);
$router->get('/admin/logout', ['Admin\AuthController', 'logout']);

$router->get('/admin', ['Admin\DashboardController', 'index']);
