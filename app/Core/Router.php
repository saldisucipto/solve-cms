<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][] = [
            'uri' => $this->normalize($uri),
            'action' => $action
        ];
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][] = [
            'uri' => $this->normalize($uri),
            'action' => $action
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method])) {
            $this->abort(404);
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $params = [];

            if ($this->match($route['uri'], $uri, $params)) {
                [$controller, $methodName] = $route['action'];

                $controllerClass = "App\\Controllers\\{$controller}";
                $instance = new $controllerClass();

                call_user_func_array(
                    [$instance, $methodName],
                    $params
                );
                return;
            }
        }

        $this->abort(404);
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

    // function match 
    protected function match(string $routeUri, string $requestUri, array &$params): bool
    {
        $routeParts = explode('/', trim($routeUri, '/'));
        $requestParts = explode('/', trim($requestUri, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        foreach ($routeParts as $index => $part) {
            if (preg_match('/^{(.+)}$/', $part, $matches)) {
                // parameter
                $params[] = $requestParts[$index];
            } elseif ($part !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
    }
}
