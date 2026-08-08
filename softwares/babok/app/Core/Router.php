<?php
namespace App\Core;

class Router
{
    private $routes = [];
    private $notFoundCallback;

    public function addRoute($method, $uri, $callback)
    {
        $this->routes[$method][$uri] = $callback;
    }

    public function setNotFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // دیباگ: نمایش URI دریافتی
        // echo "URI: " . $uri . "<br>";
        
        // حذف base path
        $basePath = '/babok/public';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        if ($uri === '' || $uri === null) {
            $uri = '/';
        }
        
        // دیباگ: نمایش URI پس از پردازش
        // echo "Processed URI: " . $uri . "<br>";
        // echo "Method: " . $method . "<br>";

        // بررسی مسیرهای دقیق
        if (isset($this->routes[$method][$uri])) {
            return $this->executeCallback($this->routes[$method][$uri], []);
        }

        // بررسی مسیرهای با پارامتر
        foreach ($this->routes[$method] as $route => $callback) {
            $pattern = $this->convertToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return $this->executeCallback($callback, $matches);
            }
        }

        // مسیر 404
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        echo "404 - Page Not Found";
    }

    private function convertToRegex($route)
    {
        $pattern = preg_replace('/\{[a-z]+\}/', '([0-9]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function executeCallback($callback, $params)
    {
        if (is_callable($callback)) {
            return $callback($params);
        }

        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controllerClass, $methodName) = explode('@', $callback);
            $controllerClass = 'App\\Controllers\\' . $controllerClass;
            
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