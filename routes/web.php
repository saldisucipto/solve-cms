<?php


/** @var \App\Core\Router $router */

use App\Controllers\Front\HomeController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/post/{slug}', [HomeController::class, 'show']);
