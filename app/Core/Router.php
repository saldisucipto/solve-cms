<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {
            $this->abort(404);
            return;
        }

        [$controller, $methodName] = $this->routes[$method][$uri];

        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            $this->abort(500, 'Controller Not Found');
            return;
        }

        $instance = new $controllerClass();
        if (!method_exists($instance, $methodName)) {
            $this->abort(500, 'Method Not Found');
            return;
        }

        call_user_func([$instance, $methodName]);
    }

    // fungsi normalize url
    protected function normalize(string $uri): string
    {
        $uri = rtrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    // simple function abort 
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        echo $message ?: "{$code} Error";
    }
}
