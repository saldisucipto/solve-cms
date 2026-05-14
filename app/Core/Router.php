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

        if (Cms::isBooted()) {
            Cms::hooks()->doAction('router.before_dispatch', $uri, $method);
            Cms::events()->dispatch('router.before_dispatch', [
                'uri' => $uri,
                'method' => $method,
            ]);
        }

        foreach ($this->routes as $route) {
            $params = [];
            if (($route['uri'] === $uri || $this->match($route['uri'], $uri, $params)) && $route['method'] === $method) {
                $this->runMiddleware($route['middleware']);
                if (Cms::isBooted()) {
                    Cms::hooks()->doAction('router.route_matched', $route);
                    Cms::events()->dispatch('router.route_matched', $route);
                }
                $this->runAction($route['action'], $params);

                if (Cms::isBooted()) {
                    Cms::hooks()->doAction('router.after_dispatch', $uri, $method, $route);
                    Cms::events()->dispatch('router.after_dispatch', [
                        'uri' => $uri,
                        'method' => $method,
                        'route' => $route,
                    ]);
                }
                return;
            }
        }

        if (Cms::isBooted()) {
            Cms::hooks()->doAction('router.not_found', $uri, $method);
            Cms::events()->dispatch('router.not_found', [
                'uri' => $uri,
                'method' => $method,
            ]);
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

    protected function runAction($action, array $params = [])
    {
        if ($action instanceof \Closure) {
            $action(...$params);
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

            $instance->$method(...$params);
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
    public function abort(int $code = 404)
    {
        http_response_code($code);
        $viewPath = BASE_PATH . "/themes/default/errors/{$code}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<h1>Error {$code}</h1>";
        }
        exit;
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
