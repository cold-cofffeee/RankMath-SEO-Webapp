<?php
/**
 * Simple Router for API Endpoints
 */

namespace RankMathWebapp\Core;

class Router {
    private $routes = [];
    private $middleware = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function addMiddleware($middleware) {
        $this->middleware[] = $middleware;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if running in subdirectory
        $basePath = '/rankmath/webapp';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        // Apply middleware
        foreach ($this->middleware as $mw) {
            $result = call_user_func($mw);
            if ($result === false) {
                return;
            }
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                return $this->handleRoute($route['handler'], $params);
            }
        }

        // No route found
        $this->sendJson(['error' => 'Route not found'], 404);
    }

    private function matchPath($routePath, $requestPath, &$params) {
        $params = [];
        
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    private function handleRoute($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, [$params]);
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($class, $method) = explode('@', $handler);
            $controller = new $class();
            return call_user_func_array([$controller, $method], [$params]);
        }

        $this->sendJson(['error' => 'Invalid handler'], 500);
    }

    public static function sendJson($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
