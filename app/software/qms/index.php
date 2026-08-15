<?php
/**
 * ============================================================
 * Entry Point ماژول QMS - سیستم مدیریت کیفیت ISO 9001:2015
 * ============================================================
 * مسیر: app/software/qms/index.php
 * 
 * این فایل توسط public/index.php اصلی فراخوانی می‌شود.
 * ============================================================
 */

// ۱. مدیریت Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ۲. تعریف ثابت‌های ماژول
if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'qms');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/qms/');
}

// ۳. Autoloader اختصاصی ماژول QMS
spl_autoload_register(function ($class) {
    // الف) جستجو در Namespace ماژول QMS
    $modulePrefix = 'App\\Software\\Qms\\';
    $moduleLen = strlen($modulePrefix);
    
    if (strncmp($modulePrefix, $class, $moduleLen) === 0) {
        $relative = substr($class, $moduleLen);
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    // ب) سازگاری با Namespace اصلی برنامه (App\)
    $legacyPrefix = 'App\\';
    $legacyLen = strlen($legacyPrefix);
    
    if (strncmp($legacyPrefix, $class, $legacyLen) === 0) {
        $relative = substr($class, $legacyLen);
        $parts = explode('\\', $relative);
        
        if (count($parts) >= 2) {
            $type = $parts[0]; // مثلاً Controllers, Models, Core
            if (in_array($type, ['Controllers', 'Models', 'Services', 'Core', 'Helpers'])) {
                $newPath = MODULAR_APP_PATH . '/app/' . $type . '/' . implode('/', array_slice($parts, 1)) . '.php';
                if (file_exists($newPath)) {
                    require $newPath;
                    return true;
                }
            }
        }
        
        // جستجوی عمومی در پوشه app/ ماژول
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    return false;
}, true, true);

// ۴. بارگذاری توابع کمکی (Helpers)
$helperPath = MODULAR_APP_PATH . '/app/Helpers/Functions.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

// ۵. دریافت و پاکسازی پارامترهای URL
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// پاکسازی برای امنیت (فقط حروف انگلیسی برای نام کنترلر و اکشن)
$controller = preg_replace('/[^a-zA-Z]/', '', $controller);
$action = preg_replace('/[^a-zA-Z]/', '', $action);
$id = is_numeric($id) ? (int)$id : null;

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ۶. بررسی احراز هویت (فقط برای عملیات حساس)
$writeActions = [
    'store', 'update', 'delete', 
    'addAuditee', 'removeAuditee', 'addEvidence', 'removeEvidence',
    'closeNc', 'rejectNc', 'reopenNc',
    'generateCar', 'approveCar', 'verifyCar', 'closeCar',
    'addTask', 'updateTask', 'completeTask',
    'finalizeReport', 'approveReport'
];

$requiresAuth = in_array($action, $writeActions) || 
                in_array($controller, ['auditplans', 'nonconformities', 'car', 'reports', 'managementreviews']);

if ($requiresAuth && !isset($_SESSION['user_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'برای انجام این عملیات لطفاً وارد شوید.',
            'requires_auth' => true,
            'redirect' => '/login'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['auth_message'] = 'برای دسترسی به این بخش لطفاً وارد شوید.';
        header('Location: /login');
        exit;
    }
}

// ۷. نگاشت کنترلرها (Controller Map)
$controllerMap = [
    'dashboard'         => 'DashboardController',
    'isoclauses'        => 'IsoClauseController',
    'departments'       => 'DepartmentController',
    'auditors'          => 'AuditorController',
    'auditplans'        => 'AuditPlanController',
    'auditsessions'     => 'AuditSessionController',
    'nonconformities'   => 'NonconformityController',
    'car'               => 'CarController',
    'reports'           => 'ReportController',
    'managementreviews' => 'ManagementReviewController',
];

// ۸. اجرای کنترلر
try {
    // مدیریت خروج از نرم‌افزار
    if ($controller === 'exit') {
        if (isset($_SESSION['current_software'])) {
            unset($_SESSION['current_software']);
        }
        header('Location: /software');
        exit;
    }
    
    $controllerKey = strtolower($controller);
    
    if (!isset($controllerMap[$controllerKey])) {
        throw new \Exception("کنترلر '{$controller}' در ماژول QMS تعریف نشده است.");
    }
    
    $controllerClass = '\\App\\Software\\Qms\\Controllers\\' . $controllerMap[$controllerKey];
    
    if (!class_exists($controllerClass)) {
        throw new \Exception("کلاس '{$controllerClass}' یافت نشد. لطفاً وجود فایل و Namespace را بررسی کنید.");
    }
    
    $controllerInstance = new $controllerClass();
    
    if (!method_exists($controllerInstance, $action)) {
        throw new \Exception("متد '{$action}' در کنترلر '{$controllerMap[$controllerKey]}' وجود ندارد.");
    }
    
    // فراخوانی متد با یا بدون پارامتر ID
    if ($id !== null) {
        $controllerInstance->$action($id);
    } else {
        $controllerInstance->$action();
    }

} catch (\Exception $e) {
    // ثبت خطا در لاگ سرور
    error_log("QMS Module Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // نمایش خطا به کاربر
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="UTF-8"><title>خطا</title>';
        echo '<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">';
        echo '<style>body{font-family:"Vazirmatn",Tahoma,sans-serif;background:#f8f9fa;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}</style>';
        echo '</head><body>';
        echo '<div style="background:white;padding:40px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);text-align:center;max-width:500px;">';
        echo '<h1 style="color:#e74c3c;margin-bottom:20px;">❌ خطا در پردازش ماژول QMS</h1>';
        echo '<p style="color:#4a5568;line-height:1.8;margin-bottom:25px;">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<a href="' . CURRENT_MODULE_URL . '" style="display:inline-block;background:#6C3CE1;color:white;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;">بازگشت به داشبورد QMS</a>';
        echo '</div></body></html>';
    }
    exit;
}