<?php
// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ============================================
// LOAD ENVIRONMENT VARIABLES
// ============================================
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// ============================================
// AUTOLOADER
// ============================================
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

// ============================================
// DIRECT INCLUDE HELPERS
// ============================================
if (file_exists(APP_PATH . '/helpers/functions.php')) {
    require_once APP_PATH . '/helpers/functions.php';
}
if (file_exists(APP_PATH . '/helpers/Captcha.php')) {
    require_once APP_PATH . '/helpers/Captcha.php';
}

// ============================================
// DIRECT INCLUDE CORE CLASSES
// ============================================
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Database.php';

// ============================================
// DIRECT INCLUDE MODELS
// ============================================
require_once APP_PATH . '/models/Post.php';
require_once APP_PATH . '/models/Category.php';
require_once APP_PATH . '/models/Setting.php';
require_once APP_PATH . '/models/Message.php';
require_once APP_PATH . '/models/User.php';

// ============================================
// ROUTING
// ============================================
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// ============================================
// HOME ROUTES
// ============================================
if ($url === '' || $url === 'home' || $url === 'index') {
    require_once APP_PATH . '/controllers/HomeController.php';
    $controller = new App\Controllers\HomeController();
    $controller->index();
    exit;
}

// ============================================
// STATIC PAGES
// ============================================
if ($url === 'about') {
    require_once APP_PATH . '/controllers/PageController.php';
    $controller = new App\Controllers\PageController();
    $controller->about();
    exit;
}

if ($url === 'contact') {
    require_once APP_PATH . '/controllers/PageController.php';
    $controller = new App\Controllers\PageController();
    $controller->contact();
    exit;
}

// ============================================
// AUTH ROUTES
// ============================================
if ($url === 'login') {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->login();
    exit;
}

if ($url === 'register') {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->register();
    exit;
}

if ($url === 'logout') {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->logout();
    exit;
}

if ($url === 'forgot-password') {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->forgot();
    exit;
}

if (strpos($url, 'reset-password/') === 0) {
    $token = substr($url, strlen('reset-password/'));
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->reset($token);
    exit;
}

if (strpos($url, 'verify/') === 0) {
    $token = substr($url, strlen('verify/'));
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->verify($token);
    exit;
}

// ============================================
// POST ROUTES
// ============================================
if (strpos($url, 'post/') === 0) {
    $slug = substr($url, strlen('post/'));
    require_once APP_PATH . '/controllers/HomeController.php';
    $controller = new App\Controllers\HomeController();
    $controller->post($slug);
    exit;
}

if (strpos($url, 'category/') === 0) {
    $slug = substr($url, strlen('category/'));
    require_once APP_PATH . '/controllers/HomeController.php';
    $controller = new App\Controllers\HomeController();
    $controller->category($slug);
    exit;
}

// ============================================
// SOFTWARE ROUTE
// ============================================
if ($url === 'software') {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    $controller = new App\Controllers\SoftwareController();
    $controller->index();
    exit;
}

// ============================================
// ADMIN ROUTES
// ============================================
if (strpos($url, 'admin') === 0) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new App\Controllers\AdminController();
    
    $adminUrl = substr($url, strlen('admin'));
    $adminUrl = ltrim($adminUrl, '/');
    
    if ($adminUrl === '') {
        $controller->dashboard();
    } elseif ($adminUrl === 'posts') {
        $controller->posts();
    } elseif ($adminUrl === 'posts/create') {
        $controller->createPost();
    } elseif (strpos($adminUrl, 'posts/edit/') === 0) {
        $id = substr($adminUrl, strlen('posts/edit/'));
        $controller->editPost($id);
    } elseif (strpos($adminUrl, 'posts/delete/') === 0) {
        $id = substr($adminUrl, strlen('posts/delete/'));
        $controller->deletePost($id);
    } elseif ($adminUrl === 'messages') {
        $controller->messages();
    } elseif ($adminUrl === 'settings') {
        $controller->settings();
    } else {
        http_response_code(404);
        echo "404 Not Found - Admin: " . $adminUrl;
    }
    exit;
}

// ============================================
// TEST ROUTE
// ============================================
if ($url === 'test') {
    require_once APP_PATH . '/controllers/TestController.php';
    $controller = new App\Controllers\TestController();
    $controller->index();
    exit;
}

// ============================================
// 404 NOT FOUND
// ============================================
http_response_code(404);
echo "404 Not Found - URL: " . $url;