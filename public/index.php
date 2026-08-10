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
// 🎯 SLUG → FOLDER MAPPING
// نگاشت slug در دیتابیس به نام پوشه فیزیکی
// ============================================
$moduleSlugs = [
    'babok-analyzer' => 'babok',
    'pmbok-analyzer' => 'pmbok',
];

// ============================================
// AUTOLOADER (نسخه نهایی و ماژولار)
// ============================================
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    
    if (strpos($class, $prefix) !== 0) {
        return false;
    }
    
    $relative_class = substr($class, strlen($prefix));
    $classPath = str_replace('\\', '/', $relative_class) . '.php';
    
    // 1. جستجو در app/ اصلی
    $mainFile = APP_PATH . '/' . $classPath;
    if (file_exists($mainFile)) {
        require $mainFile;
        return true;
    }
    
    // 2. جستجو در ماژول نرم‌افزاری (در صورت فعال بودن)
    if (defined('MODULAR_APP_PATH')) {
        $moduleFile = MODULAR_APP_PATH . '/app/' . $classPath;
        if (file_exists($moduleFile)) {
            require $moduleFile;
            return true;
        }
    }
    
    // 3. جستجو از طریق $_ENV (برای سازگاری با کدهای قدیمی)
    if (isset($_ENV['CURRENT_SOFTWARE_PATH']) && !empty($_ENV['CURRENT_SOFTWARE_PATH'])) {
        $moduleFile = $_ENV['CURRENT_SOFTWARE_PATH'] . '/app/' . $classPath;
        if (file_exists($moduleFile)) {
            require $moduleFile;
            return true;
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
// DIRECT INCLUDE MODELS (با بررسی وجود فایل)
// ============================================
$commonModels = ['Post', 'Category', 'Setting', 'Message', 'User', 'Software', 
                 'SoftwareActivityLog', 'SoftwareUsageLimit'];
foreach ($commonModels as $model) {
    $file = APP_PATH . '/models/' . $model . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

// ============================================
// ROUTING
// ============================================
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = trim($url, '/');

// ============================================
// 📊 ثبت بازدید (فقط صفحات عمومی سایت)
// ============================================
try {
    $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($_SERVER['REQUEST_METHOD'] === 'GET'
        && !$isAjaxRequest
        && strpos($url, 'admin') !== 0) {

        require_once APP_PATH . '/models/Visit.php';
        $visitModel = new \App\Models\Visit();
        $visitModel->record();
    }
} catch (\Exception $e) {
    // خطای ثبت بازدید هرگز سایت را متوقف نکند
    error_log("Visit tracking error: " . $e->getMessage());
}

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
    (new App\Controllers\AuthController())->login();
    exit;
}
if ($url === 'register') {
    require_once APP_PATH . '/controllers/AuthController.php';
    (new App\Controllers\AuthController())->register();
    exit;
}
if ($url === 'logout') {
    require_once APP_PATH . '/controllers/AuthController.php';
    (new App\Controllers\AuthController())->logout();
    exit;
}
if ($url === 'forgot-password') {
    require_once APP_PATH . '/controllers/AuthController.php';
    (new App\Controllers\AuthController())->forgot();
    exit;
}
if (strpos($url, 'reset-password/') === 0) {
    $token = substr($url, strlen('reset-password/'));
    require_once APP_PATH . '/controllers/AuthController.php';
    (new App\Controllers\AuthController())->reset($token);
    exit;
}
if (strpos($url, 'verify/') === 0) {
    $token = substr($url, strlen('verify/'));
    require_once APP_PATH . '/controllers/AuthController.php';
    (new App\Controllers\AuthController())->verify($token);
    exit;
}

// ============================================
// PROFILE ROUTES
// ============================================
if ($url === 'profile') {
    require_once APP_PATH . '/controllers/ProfileController.php';
    (new App\Controllers\ProfileController())->index();
    exit;
}

if ($url === 'profile/edit') {
    require_once APP_PATH . '/controllers/ProfileController.php';
    (new App\Controllers\ProfileController())->edit();
    exit;
}

if ($url === 'profile/update') {
    require_once APP_PATH . '/controllers/ProfileController.php';
    (new App\Controllers\ProfileController())->update();
    exit;
}

if ($url === 'profile/password') {
    require_once APP_PATH . '/controllers/ProfileController.php';
    (new App\Controllers\ProfileController())->updatePassword();
    exit;
}

// ============================================
// POST & CATEGORY ROUTES
// ============================================
if (strpos($url, 'post/') === 0) {
    $slug = substr($url, strlen('post/'));
    require_once APP_PATH . '/controllers/HomeController.php';
    (new App\Controllers\HomeController())->post($slug);
    exit;
}
if (strpos($url, 'category/') === 0) {
    $slug = substr($url, strlen('category/'));
    require_once APP_PATH . '/controllers/HomeController.php';
    (new App\Controllers\HomeController())->category($slug);
    exit;
}

// ============================================
// 🎯 SOFTWARE ROUTES (اصلاح شده)
// ============================================

// 1. لیست نرم‌افزارها
if ($url === 'software') {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    (new App\Controllers\SoftwareController())->index();
    exit;
}

// 2. جزئیات نرم‌افزار
if (preg_match('/^software\/detail\/([a-z0-9\-]+)$/', $url, $m)) {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    (new App\Controllers\SoftwareController())->detail($m[1]);
    exit;
}

// 3. اجرای نرم‌افزار (دکمه Run)
if (preg_match('/^software\/run\/([a-z0-9\-]+)$/', $url, $m)) {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    (new App\Controllers\SoftwareController())->run($m[1]);
    exit;
}

// 4. خروج از نرم‌افزار
if ($url === 'software/exit') {
    require_once APP_PATH . '/controllers/SoftwareController.php';
    (new App\Controllers\SoftwareController())->exitSoftware();
    exit;
}

// 5. 🎯 اجرای ماژول نرم‌افزار (مثل /software/babok-analyzer/)
if (preg_match('/^software\/([a-z0-9\-]+)(\/.*)?$/', $url, $m)) {
    $slugOrModule = strtolower($m[1]);
    
    // لیست route های رزرو شده
    $reservedRoutes = ['detail', 'run', 'exit'];
    
    if (!in_array($slugOrModule, $reservedRoutes)) {
        // تبدیل slug به نام پوشه فیزیکی
        $moduleName = $moduleSlugs[$slugOrModule] ?? $slugOrModule;
        
        // 🎯 مسیر صحیح: app/software/{moduleName}
        $modulePath = APP_PATH . '/software/' . $moduleName;
        
        // 🔍 دیباگ - با ?debug=1
        if (isset($_GET['debug'])) {
            echo "<pre style='direction:ltr;text-align:left;background:#1e293b;color:#10b981;padding:20px;font-family:monospace'>";
            echo "=== Module Debug ===\n";
            echo "URL: " . htmlspecialchars($url) . "\n";
            echo "Slug: {$slugOrModule}\n";
            echo "Resolved Module: {$moduleName}\n";
            echo "Module Path: {$modulePath}\n";
            echo "Path Exists: " . (is_dir($modulePath) ? '✅ YES' : '❌ NO') . "\n";
            echo "APP_PATH: " . APP_PATH . "\n";
            echo "ROOT_PATH: " . ROOT_PATH . "\n\n";
            
            if (is_dir(APP_PATH . '/software')) {
                echo "Contents of app/software/:\n";
                foreach (scandir(APP_PATH . '/software') as $item) {
                    if ($item !== '.' && $item !== '..') {
                        $type = is_dir(APP_PATH . '/software/' . $item) ? '📁' : '📄';
                        echo "  {$type} {$item}\n";
                    }
                }
            }
            
            if (is_dir($modulePath)) {
                echo "\nContents of {$moduleName}/:\n";
                foreach (scandir($modulePath) as $item) {
                    if ($item !== '.' && $item !== '..') {
                        $type = is_dir($modulePath . '/' . $item) ? '📁' : '📄';
                        echo "  {$type} {$item}\n";
                    }
                }
            }
            echo "</pre>";
            exit;
        }
        
        if (is_dir($modulePath)) {
            // تعریف ثابت‌های ماژول
            if (!defined('MODULAR_APP_PATH')) {
                define('MODULAR_APP_PATH', $modulePath);
            }
            if (!defined('CURRENT_MODULE')) {
                define('CURRENT_MODULE', $moduleName);
            }
            if (!defined('CURRENT_MODULE_URL')) {
                define('CURRENT_MODULE_URL', '/software/' . $slugOrModule . '/');
            }
            
            // تنظیم برای autoloader قدیمی
            $_ENV['CURRENT_SOFTWARE_PATH'] = $modulePath;
            
            // لود entry point ماژول
            $entryFile = $modulePath . '/index.php';
            if (file_exists($entryFile)) {
                require $entryFile;
                exit;
            } else {
                http_response_code(500);
                echo "❌ Entry file not found: {$entryFile}";
                exit;
            }
        } else {
            // ماژول یافت نشد
            http_response_code(404);
            echo "❌ Software not found: {$slugOrModule} (resolved: {$moduleName})<br>";
            echo "Expected path: {$modulePath}";
            exit;
        }
    }
}

// ============================================
// ️ ADMIN ROUTES (کامل)
// ============================================
if (strpos($url, 'admin') === 0) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new App\Controllers\AdminController();

    $adminUrl = ltrim(substr($url, strlen('admin')), '/');

    // ----------------------------------------
    // ۱) مسیرهای با پارامتر عددی
    // ----------------------------------------
    $paramRoutes = [
        '#^posts/edit/(\d+)$#'       => 'editPost',
        '#^posts/delete/(\d+)$#'     => 'deletePost',
        '#^messages/view/(\d+)$#'    => 'viewMessage',
        '#^messages/delete/(\d+)$#'  => 'deleteMessage',
        '#^users/edit/(\d+)$#'       => 'editUser',
        '#^users/delete/(\d+)$#'     => 'deleteUser',
        '#^users/update$#'           => 'updateUser',
        '#^categories/edit/(\d+)$#'  => 'editCategory',
        '#^categories/delete/(\d+)$#'=> 'deleteCategory',
        '#^tags/delete/(\d+)$#'      => 'deleteTag',
        '#^software/edit/(\d+)$#'    => 'editSoftware',
        '#^software/delete/(\d+)$#'  => 'deleteSoftware',
    ];

    foreach ($paramRoutes as $pattern => $method) {
        if (preg_match($pattern, $adminUrl, $m)) {
            if (method_exists($controller, $method)) {
                $controller->$method($m[1]);
            } else {
                http_response_code(404);
                echo "404 - متد {$method} یافت نشد";
            }
            exit;
        }
    }

    // ----------------------------------------
    // ۲) مسیرهای ساده
    // ----------------------------------------
    $simpleRoutes = [
        ''                    => 'dashboard',
        'posts'               => 'posts',
        'posts/create'        => 'createPost',
        'messages'            => 'messages',
        'users'               => 'users',
        'categories'          => 'categories',
        'categories/create'   => 'createCategory',
        'tags'                => 'tags',
        'settings'            => 'settings',
        'logs'                => 'logs',
        'visits'              => 'visits',
        'software'            => 'software',
        'software-activity'   => 'softwareActivity',
        'software-limits'     => 'softwareLimits',
        'software-usage'      => 'softwareUsage',
    ];

    if (array_key_exists($adminUrl, $simpleRoutes)) {
        $method = $simpleRoutes[$adminUrl];
        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(404);
            echo "404 - متد {$method} در AdminController یافت نشد";
        }
        exit;
    }

    // ----------------------------------------
    // ۳) مسیر ناشناخته
    // ----------------------------------------
    http_response_code(404);
    echo "404 Not Found - Admin: " . htmlspecialchars($adminUrl);
    exit;
}

// ============================================
// 404 NOT FOUND
// ============================================
http_response_code(404);