<?php
// ==========================================================
// BOOTSTRAP - ورودی یکپارچه و ماژولار (نسخه نهایی)
// ==========================================================

// تنظیمات اولیه و مسیرها
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// فعال‌سازی نمایش خطاها
error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع نشست
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// بارگذاری فایل .env اصلی
// ==========================================================
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// ==========================================================
// اتولودر ماژولار (تمام کلاس‌های App\ را در هر دو مسیر جستجو می‌کند)
// ==========================================================
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    
    // اگر کلاس با App\ شروع نشد، ادامه نده
    if (strncmp($prefix, $class, $len) !== 0) {
        return false;
    }
    
    // ۱. تبدیل Namespace به مسیر فایل
    $relative_class = substr($class, $len);
    $classPath = str_replace('\\', '/', $relative_class) . '.php';
    
    // ۲. ابتدا در پوشه اصلی سایت جستجو کن
    $mainFile = APP_PATH . '/' . $classPath;
    if (file_exists($mainFile)) {
        require $mainFile;
        return true;
    }
    
    // ۳. اگر در پوشه نرم‌افزار ماژولار هستیم، در آنجا جستجو کن
    if (defined('MODULAR_APP_PATH') && !empty(MODULAR_APP_PATH)) {
        $moduleFile = MODULAR_APP_PATH . '/app/' . $classPath;
        if (file_exists($moduleFile)) {
            require $moduleFile;
            return true;
        }
    }
    
    return false;
});

// ==========================================================
// بارگذاری فایل‌های اصلی (Core و مدل‌های عمومی)
// ==========================================================
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/models/Software.php';
require_once APP_PATH . '/models/Post.php';
require_once APP_PATH . '/models/Category.php';
require_once APP_PATH . '/models/Setting.php';
require_once APP_PATH . '/models/Message.php';
require_once APP_PATH . '/models/User.php';

// ==========================================================
// سیستم مسیریابی (Router)
// ==========================================================
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// اگر درخواست خالی بود، به صفحه اصلی برو
if ($url === '') {
    require_once APP_PATH . '/controllers/HomeController.php';
    $controller = new App\Controllers\HomeController();
    $controller->index();
    exit;
}

// ==========================================================
// ۱. مسیریابی نرم‌افزارهای ماژولار (اولویت اول)
// ==========================================================
if (strpos($url, 'software/') === 0) {
    // استخراج نام اسلاگ (مثلاً software/babok-analyzer/public)
    $segments = explode('/', $url);
    $slug = $segments[1] ?? null;
    
    if ($slug) {
        // اگر نام پوشه با اسلاگ فرق داشت، اینجا اصلاح کن
        $folderName = $slug;
        if ($slug === 'babok-analyzer') {
            $folderName = 'babok';
        }

        // مسیر فیزیکی پوشه نرم‌افزار
        $softwareDir = ROOT_PATH . '/softwares/' . $folderName;
        
        if (is_dir($softwareDir)) {
            // تعریف مسیر برای اتولودر
            define('MODULAR_APP_PATH', $softwareDir);
            
            // استخراج زیرمسیر (بقیه آدرس بعد از اسلاگ)
            $remainingPath = substr($url, strlen('software/' . $slug));
            $remainingPath = ltrim($remainingPath, '/');
            
            // اگر زیرمسیر خالی است (فقط به روت نرم‌افزار رفته‌اند)
            if (empty($remainingPath)) {
                $entryFile = $softwareDir . '/index.php';
                if (file_exists($entryFile)) {
                    require_once $entryFile;
                    exit;
                } else {
                    // اگر index.php در روت نیست، به public/index.php هدایت کن
                    $publicFile = $softwareDir . '/public/index.php';
                    if (file_exists($publicFile)) {
                        require_once $publicFile;
                        exit;
                    }
                }
            } 
            // اگر زیرمسیر وجود دارد (مثلاً public, assets, css)
            else {
                $targetPath = $softwareDir . '/' . $remainingPath;
                
                if (file_exists($targetPath)) {
                    // اگر فایل است (مثلاً public/index.php یا یک فایل CSS)
                    if (is_file($targetPath)) {
                        require_once $targetPath;
                        exit;
                    }
                    // اگر پوشه است و index.php دارد
                    elseif (is_dir($targetPath) && file_exists($targetPath . '/index.php')) {
                        require_once $targetPath . '/index.php';
                        exit;
                    }
                    // فایل‌های استاتیک مثل تصاویر و CSS
                    else {
                        http_response_code(200);
                        exit;
                    }
                } else {
                    http_response_code(404);
                    echo "فایل درخواستی در نرم‌افزار یافت نشد.";
                    exit;
                }
            }
        } else {
            http_response_code(404);
            echo "نرم‌افزار درخواستی یافت نشد.";
            exit;
        }
    }
}

// ==========================================================
// ۲. مسیریابی صفحات معمولی سایت (غیر از نرم‌افزارها)
// ==========================================================

// Static pages
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

// Auth
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

if (strpos($url, 'reset-password/') === 0) {
    $token = substr($url, strlen('reset-password/'));
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new App\Controllers\AuthController();
    $controller->reset($token);
    exit;
}

// Posts & Categories
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

// Software List
if ($url === 'software') {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    $controller = new App\Controllers\SoftwareController();
    $controller->index();
    exit;
}

// Admin Routes
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

// ==========================================================
// ۴. اگر هیچ مسیری پیدا نشد
// ==========================================================
http_response_code(404);
echo "404 Not Found - URL: " . htmlspecialchars($url);