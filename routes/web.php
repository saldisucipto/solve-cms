<?php


/** @var \App\Core\Router $router */

$router->get('/', ['Front\HomeController', 'index']);
$router->get('/post/{slug}', ['Front\HomeController', 'show']);
