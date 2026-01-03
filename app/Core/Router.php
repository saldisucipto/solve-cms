<?php

namespace App\Core;

use Exception;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];

    public function group(array $option, callable $callback): void
    {
        $this->groupStack[] = $option;
        $callback($this);
        array_pop($this->groupStack);
    }


    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function addRoute(string $method, string $uri, $action): void
    {

        if (is_string($action)) {
            throw new \Exception('String route action is not allowed. Use array syntax.');
        }
        $middleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array)$group['middleware']);
            }
        }

        $this->routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                $this->runMiddleware($route['middleware']);
                $this->runAction($route['action']);
                return;
            }
        }
        $this->abort(404);
    }

    protected function runMiddleware(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            [$name, $class, $param] = Middleware::resolve($middleware);

            $instance = $param ? new $class($param) : new $class();

            $instance->handle();
        }
    }

    protected function runAction($action)
    {
        if ($action instanceof \Closure) {
            $action();
            return;
        }

        // array controller [Classname::class, Method]
        if (is_array($action)) {
            if (count($action) !== 2) {
                throw new Exception('Route action, setidaknya memliki 2 element array');
            }

            [$controller, $method] = $action;
            if (!class_exists($controller)) {
                throw new \Exception("Controller [$controller] not found");
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new \Exception("Method [$method] not found in controller [$controller]");
            }

            $instance->$method();
            return;
        }

        throw new \Exception('Invalid route action type');
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
