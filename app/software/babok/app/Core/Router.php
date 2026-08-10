<?php

namespace App\Software\Babok\Core;

/**
 * روتر ماژول BABOK
 * مسیرها از طریق ?route= و &id= مدیریت می‌شوند
 */
class Router
{
    private $routes = [];
    private $notFoundCallback;

    public function addRoute($method, $route, $callback)
    {
        $this->routes[$method][$route] = $callback;
    }

    public function setNotFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $route = $_GET['route'] ?? 'home';
        $id = $_GET['id'] ?? null;

        // بررسی مسیرهای دقیق
        if (isset($this->routes[$method][$route])) {
            return $this->executeCallback($this->routes[$method][$route], [$id]);
        }

        // مسیر 404
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        echo "404 - Route Not Found: " . htmlspecialchars($route);
    }

    private function executeCallback($callback, $params = [])
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controllerClass, $methodName) = explode('@', $callback);
            $controllerClass = 'App\\Software\\Babok\\Controllers\\' . $controllerClass;

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    return $controller->$methodName(...$params);
                }
            }
        }

        throw new \Exception("Invalid callback");
    }
}