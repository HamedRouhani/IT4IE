<?php
/**
 * ==========================================================
 * BOOTSTRAP - ورودی یکپارچه و ماژولار IT4IE
 * مسیر: public/index.php
 * ==========================================================
 */

// ==========================================================
// ۱. شروع Session
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// ۲. تنظیمات مسیرها (اصلاح شده برای ساختار public/)
// ==========================================================
// __DIR__ = /home/itieir/public_html/public
// ROOT_PATH = /home/itieir/public_html (یک سطح بالاتر)
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/app/config');

// حالت توسعه (برای نمایش خطاهای دقیق)
define('APP_DEBUG', true);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==========================================================
// ۳. بارگذاری فایل‌های Core (ضروری)
// ==========================================================
$coreFiles = [
    '/core/Database.php',
    '/core/Model.php',
    '/core/Controller.php',
];

foreach ($coreFiles as $file) {
    $path = APP_PATH . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        die("فایل اصلی یافت نشد: {$file}");
    }
}

// ==========================================================
// ۴. بارگذاری مدل‌های عمومی (در صورت وجود)
// ==========================================================
$commonModels = [
    'Software', 'Post', 'Category', 'Setting', 
    'Message', 'User', 'SoftwareActivityLog', 'SoftwareUsageLimit'
];

foreach ($commonModels as $model) {
    $modelFile = APP_PATH . '/models/' . $model . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
}

// ==========================================================
// ۵. Autoloader ماژولار (PSR-4 Like)
// ==========================================================
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $prefixLen = strlen($prefix);
    
    // فقط کلاس‌های با پیشوند App\ را پردازش کن
    if (strncmp($prefix, $class, $prefixLen) !== 0) {
        return false;
    }
    
    $relativeClass = substr($class, $prefixLen);
    $classPath = str_replace('\\', '/', $relativeClass) . '.php';
    
    // ۱. جستجو در app/ اصلی IT4IE
    $mainFile = APP_PATH . '/' . $classPath;
    if (file_exists($mainFile)) {
        require $mainFile;
        return true;
    }
    
    // ۲. جستجو در ماژول‌های نرم‌افزاری
    // الگو: App\Software\Babok\Controllers\X → app/software/babok/app/Controllers/X.php
    if (preg_match('/^Software\/([^\/]+)\/(.+)$/', $relativeClass, $matches)) {
        $moduleName = strtolower($matches[1]);
        $moduleClassPath = $matches[2];
        $moduleFile = APP_PATH . '/software/' . $moduleName . '/app/' . $moduleClassPath . '.php';
        
        if (file_exists($moduleFile)) {
            require $moduleFile;
            return true;
        }
    }
    
    return false;
});

// ==========================================================
// ۶. Mapping بین slug و نام پوشه فیزیکی
// ==========================================================
// این نگاشت برای حل مشکل تفاوت slug (babok-analyzer) و نام پوشه (babok)
$moduleSlugs = [
    'babok-analyzer' => 'babok',
    'pmbok-analyzer' => 'pmbok',
    // می‌توانید نرم‌افزارهای بعدی را اینجا اضافه کنید
];

// ==========================================================
// ۷. دریافت URL و پردازش اولیه
// ==========================================================
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = trim($url, '/');

// تابع کمکی برای لود سریع کنترلرها
function loadController($controllerName) {
    $file = APP_PATH . '/controllers/' . $controllerName . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

// ==========================================================
// ۸. سیستم مسیریابی (Router)
// ==========================================================

// ----------------------------------------
// ۸.۱ صفحه اصلی
// ----------------------------------------
if ($url === '' || $url === 'home') {
    if (loadController('HomeController')) {
        $controller = new App\Controllers\HomeController();
        $controller->index();
        exit;
    }
}

// ----------------------------------------
// ۸.۲ صفحات استاتیک
// ----------------------------------------
if ($url === 'about') {
    if (loadController('PageController')) {
        $controller = new App\Controllers\PageController();
        $controller->about();
        exit;
    }
}

if ($url === 'contact') {
    if (loadController('PageController')) {
        $controller = new App\Controllers\PageController();
        $controller->contact();
        exit;
    }
}

// ----------------------------------------
// ۸.۳ احراز هویت
// ----------------------------------------
if (in_array($url, ['login', 'register', 'logout'])) {
    if (loadController('AuthController')) {
        $controller = new App\Controllers\AuthController();
        $controller->$url();
        exit;
    }
}

// ----------------------------------------
// ۸.۴ صفحه لیست نرم‌افزارها
// ----------------------------------------
if ($url === 'software') {
    if (loadController('SoftwareController')) {
        $controller = new App\Controllers\SoftwareController();
        $controller->index();
        exit;
    }
}

// ----------------------------------------
// ۸.۵ جزئیات نرم‌افزار
// ----------------------------------------
if (preg_match('/^software\/detail\/([a-z0-9\-]+)$/', $url, $matches)) {
    if (loadController('SoftwareController')) {
        $controller = new App\Controllers\SoftwareController();
        $controller->detail($matches[1]);
        exit;
    }
}

// ----------------------------------------
// ۸.۶ اجرای نرم‌افزار (Run)
// ----------------------------------------
if (preg_match('/^software\/run\/([a-z0-9\-]+)$/', $url, $matches)) {
    if (loadController('SoftwareController')) {
        $controller = new App\Controllers\SoftwareController();
        $controller->run($matches[1]);
        exit;
    }
}

// ----------------------------------------
// ۸.۷ خروج از نرم‌افزار
// ----------------------------------------
if ($url === 'software/exit') {
    if (loadController('SoftwareController')) {
        $controller = new App\Controllers\SoftwareController();
        $controller->exitSoftware();
        exit;
    }
}

// ----------------------------------------
// ۸.۸ 🎯 اجرای ماژول نرم‌افزاری
// ----------------------------------------
if (preg_match('/^software\/([a-z0-9\-]+)(\/.*)?$/', $url, $matches)) {
    $slugOrModule = strtolower($matches[1]);
    
    // Route های رزرو شده (قبلاً هندل شده‌اند)
    $reservedRoutes = ['detail', 'run', 'exit'];
    
    if (!in_array($slugOrModule, $reservedRoutes)) {
        // تبدیل slug به نام پوشه فیزیکی
        $moduleName = $moduleSlugs[$slugOrModule] ?? $slugOrModule;
        $modulePath = APP_PATH . '/software/' . $moduleName;
        
        // 🐛 دیباگ - فقط در حالت توسعه
        if (APP_DEBUG && isset($_GET['debug'])) {
            echo "<h3>🔍 Debug Module</h3>";
            echo "<strong>URL:</strong> " . htmlspecialchars($url) . "<br>";
            echo "<strong>Slug/Module:</strong> {$slugOrModule}<br>";
            echo "<strong>Resolved Name:</strong> {$moduleName}<br>";
            echo "<strong>Module Path:</strong> {$modulePath}<br>";
            echo "<strong>Exists:</strong> " . (is_dir($modulePath) ? '✅ YES' : '❌ NO') . "<br>";
            echo "<strong>ROOT_PATH:</strong> " . ROOT_PATH . "<br>";
            echo "<strong>APP_PATH:</strong> " . APP_PATH . "<br>";
            echo "<hr>";
            echo "<strong>محتویات app/software/:</strong><br>";
            if (is_dir(APP_PATH . '/software')) {
                echo "<ul>";
                foreach (scandir(APP_PATH . '/software') as $item) {
                    if ($item !== '.' && $item !== '..') {
                        echo "<li>{$item}</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "❌ پوشه app/software/ وجود ندارد!";
            }
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
            
            // لود فایل entry point ماژول
            $moduleEntry = $modulePath . '/index.php';
            if (file_exists($moduleEntry)) {
                require $moduleEntry;
                exit;
            } else {
                http_response_code(500);
                echo "❌ فایل index.php ماژول {$moduleName} یافت نشد.";
                exit;
            }
        } else {
            // ماژول یافت نشد
            http_response_code(404);
            if (file_exists(VIEWS_PATH . '/errors/404.php')) {
                require_once VIEWS_PATH . '/errors/404.php';
            } else {
                echo "<h1>404 - ماژول {$slugOrModule} یافت نشد</h1>";
                echo "<p><a href='/software'>بازگشت به لیست نرم‌افزارها</a></p>";
            }
            exit;
        }
    }
}

// ----------------------------------------
// ۸.۹ مسیرهای پنل مدیریت
// ----------------------------------------
if (strpos($url, 'admin') === 0) {
    if (loadController('AdminController')) {
        $controller = new App\Controllers\AdminController();
        $adminUrl = ltrim(substr($url, 5), '/'); // حذف 'admin' از ابتدا
        
        // جدول مسیریابی ادمین
        $adminRoutes = [
            '' => 'dashboard',
            'posts' => 'posts',
            'posts/create' => 'createPost',
            'messages' => 'messages',
            'settings' => 'settings',
            'software-usage' => 'softwareUsage',
            'software-limits' => 'softwareLimits',
            'software-activity' => 'softwareActivity',
        ];
        
        if (isset($adminRoutes[$adminUrl])) {
            $method = $adminRoutes[$adminUrl];
            if (method_exists($controller, $method)) {
                $controller->$method();
                exit;
            }
        }
        
        // مسیرهای با پارامتر (posts/edit/X و posts/delete/X)
        if (preg_match('/^posts\/edit\/(\d+)$/', $adminUrl, $m)) {
            $controller->editPost($m[1]);
            exit;
        }
        
        if (preg_match('/^posts\/delete\/(\d+)$/', $adminUrl, $m)) {
            $controller->deletePost($m[1]);
            exit;
        }
        
        // مسیر ناشناخته در ادمین
        http_response_code(404);
        echo "<h1>404 - صفحه ادمین یافت نشد</h1>";
        echo "<p>مسیر: " . htmlspecialchars($adminUrl) . "</p>";
        echo "<p><a href='/admin'>بازگشت به داشبورد</a></p>";
        exit;
    }
}

// ----------------------------------------
// ۸.۱۰ مسیرهای دسته‌بندی و پست‌ها (در صورت وجود)
// ----------------------------------------
if (preg_match('/^category\/([a-z0-9\-]+)$/', $url, $matches)) {
    if (loadController('CategoryController')) {
        $controller = new App\Controllers\CategoryController();
        if (method_exists($controller, 'show')) {
            $controller->show($matches[1]);
            exit;
        }
    }
}

if (preg_match('/^post\/([a-z0-9\-]+)$/', $url, $matches)) {
    if (loadController('PostController')) {
        $controller = new App\Controllers\PostController();
        if (method_exists($controller, 'show')) {
            $controller->show($matches[1]);
            exit;
        }
    }
}

// ==========================================================
// ۹. صفحه 404 (پایان خط)
// ==========================================================
http_response_code(404);
if (file_exists(VIEWS_PATH . '/errors/404.php')) {
    require_once VIEWS_PATH . '/errors/404.php';
} else {
    echo "<!DOCTYPE html>
    <html lang='fa' dir='rtl'>
    <head>
        <meta charset='UTF-8'>
        <title>404 - صفحه یافت نشد</title>
        <style>
            body { font-family: Tahoma, sans-serif; text-align: center; padding: 50px; background: #f4f7f9; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { font-size: 4rem; color: #e74c3c; margin: 0; }
            p { color: #666; font-size: 1.1rem; }
            a { color: #3498db; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>404</h1>
            <h2>صفحه مورد نظر یافت نشد</h2>
            <p>مسیر درخواستی: <code>" . htmlspecialchars($url) . "</code></p>
            <p><a href='/'>🏠 بازگشت به صفحه اصلی</a> | <a href='/software'>💻 لیست نرم‌افزارها</a></p>
        </div>
    </body>
    </html>";
}
exit;