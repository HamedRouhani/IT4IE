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
// AUTOLOADER (نسخه نهایی و کاملاً دقیق)
// ============================================
spl_autoload_register(function ($class) {
    
    // 1. ابتدا در پوشه اصلی سایت جستجو کن
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
    
    // 2. اگر در مسیر نرم‌افزار ماژولار بودیم، در آنجا جستجو کن
    if (isset($_ENV['CURRENT_SOFTWARE_PATH']) && !empty($_ENV['CURRENT_SOFTWARE_PATH'])) {
        // مسیر پوشه app درون نرم‌افزار
        $module_dir = $_ENV['CURRENT_SOFTWARE_PATH'] . '/app/';
        
        if (strpos($class, $prefix) === 0) {
            $relative_class = substr($class, strlen($prefix));
            
            // ساخت مسیر فایل درون نرم‌افزار (دقت کنید: به جای App\، از app/ استفاده می‌شود)
            $file = $module_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
    }
    
    return false;
});

// ============================================
// DIRECT INCLUDE HELPERS & CORE
// ============================================
if (file_exists(APP_PATH . '/helpers/functions.php')) {
    require_once APP_PATH . '/helpers/functions.php';
}
if (file_exists(APP_PATH . '/helpers/Captcha.php')) {
    require_once APP_PATH . '/helpers/Captcha.php';
}
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
require_once APP_PATH . '/models/Software.php';

// ============================================
// ROUTING
// ============================================
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// ============================================
// HOME & STATIC PAGES
// ============================================
if ($url === '' || $url === 'home' || $url === 'index') {
    require_once APP_PATH . '/controllers/HomeController.php';
    $controller = new App\Controllers\HomeController();
    $controller->index();
    exit;
}
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
// POST & CATEGORY ROUTES
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
// SOFTWARE ROUTES
// ============================================

// 1. لیست نرم‌افزارها
if ($url === 'software' || $url === 'software/') {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    $controller = new App\Controllers\SoftwareController();
    $controller->index();
    exit;
}

// 2. اجرای نرم‌افزار ماژولار
if (strpos($url, 'software/') === 0) {
    
    // استخراج نام اسلاگ
    $segments = explode('/', $url);
    $slug = $segments[1] ?? null;
    
    if ($slug) {
        
        // تعریف مسیرهای احتمالی برای پوشه
        $possiblePaths = [
            ROOT_PATH . '/softwares/' . $slug,
            ROOT_PATH . '/softwares/babok' // پشتیبانی از حالت babok
        ];
        
        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                $foundPath = $path;
                break;
            }
        }
        
        if ($foundPath) {
            // تنظیم مسیر برای اتولودر
            $_ENV['CURRENT_SOFTWARE_PATH'] = $foundPath;
            
            // استخراج زیرمسیر
            $subPath = substr($url, strlen('software/' . $slug));
            $subPath = ltrim($subPath, '/');
            
            // ساخت مسیر کامل فیزیکی
            $targetPath = $foundPath . '/' . $subPath;
            $targetPath = rtrim($targetPath, '/');
            
            // اگر زیرمسیر وجود دارد
            if (!empty($subPath)) {
                if (file_exists($targetPath)) {
                    if (is_file($targetPath)) {
                        require_once $targetPath;
                        exit;
                    } elseif (is_dir($targetPath) && file_exists($targetPath . '/index.php')) {
                        require_once $targetPath . '/index.php';
                        exit;
                    } else {
                        http_response_code(200);
                        exit;
                    }
                } else {
                    http_response_code(404);
                    echo "File not found: " . $subPath;
                    exit;
                }
            } else {
                // اگر زیرمسیری ندارد، روت نرم‌افزار را لود کن
                $entryFile = $foundPath . '/index.php';
                if (file_exists($entryFile)) {
                    require_once $entryFile;
                    exit;
                } else {
                    http_response_code(404);
                    echo "Software entry file not found.";
                    exit;
                }
            }
        } else {
            http_response_code(404);
            echo "Software not found: " . $slug;
            exit;
        }
    }
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
        echo "404 Not Found - Admin";
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