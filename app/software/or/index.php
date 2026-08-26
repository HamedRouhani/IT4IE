<?php
/**
 * ============================================================
 * Entry Point ماژول OR Analyzer
 * ============================================================
 * مسیر: app/software/or/index.php
 * URL: /software/or-analyzer/
 * ============================================================
 */

// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تعریف ثابت‌های ماژول
if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'or');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/or-analyzer/');
}
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', dirname(dirname(dirname(__DIR__))) . '/views');
}

// ============================================
// AUTOLOADER ماژول OR
// ============================================
spl_autoload_register(function ($class) {
    // ۱. Namespace ماژول OR
    $prefix = 'App\\Software\\Or\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    // ۲. Namespace عمومی (سازگاری با Core)
    $legacyPrefix = 'App\\';
    if (strpos($class, $legacyPrefix) === 0) {
        $relative = substr($class, strlen($legacyPrefix));
        $classPath = str_replace('\\', '/', $relative) . '.php';
        
        // جستجو در Core اصلی سایت
        $mainFile = dirname(dirname(dirname(__DIR__))) . '/app/' . $classPath;
        if (file_exists($mainFile)) {
            require $mainFile;
            return true;
        }
        
        // جستجو در ماژول
        $moduleFile = MODULAR_APP_PATH . '/app/' . $classPath;
        if (file_exists($moduleFile)) {
            require $moduleFile;
            return true;
        }
    }
    return false;
}, true, true);

// لود helpers
if (file_exists(MODULAR_APP_PATH . '/app/Helpers/Functions.php')) {
    require_once MODULAR_APP_PATH . '/app/Helpers/Functions.php';
}

// ============================================
// ROUTING
// ============================================
$controller = $_GET['controller'] ?? 'dashboard';
$action     = $_GET['action'] ?? 'index';
$id         = $_GET['id'] ?? null;

// بررسی AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ============================================
// 🎯 ROUTER ماژول OR
// ============================================
$controllerMap = [
    'dashboard'    => 'DashboardController',
    'project'      => 'ProjectController',
    'problem_type' => 'ProblemTypeController',
    'method'       => 'MethodController',
    'transport'    => 'TransportController',
    'assignment'   => 'AssignmentController',
    'simplex'      => 'SimplexController',
    'sensitivity'  => 'SensitivityController',
    'report'       => 'ReportController',
];

try {
    // خروج از ماژول
    if ($controller === 'exit') {
        if (isset($_SESSION['current_software'])) {
            unset($_SESSION['current_software']);
        }
        header('Location: /software');
        exit;
    }
    
    $controllerKey = strtolower($controller);
    
    if (!isset($controllerMap[$controllerKey])) {
        throw new \Exception("کنترلر '{$controller}' یافت نشد.");
    }
    
    $controllerClass = 'App\\Software\\Or\\Controllers\\' . $controllerMap[$controllerKey];
    
    if (!class_exists($controllerClass)) {
        throw new \Exception("کلاس '{$controllerClass}' یافت نشد.");
    }
    
    $controllerInstance = new $controllerClass();
    
    if (!method_exists($controllerInstance, $action)) {
        throw new \Exception("متد '{$action}' در کنترلر '{$controller}' یافت نشد.");
    }
    
    // فراخوانی اکشن
    if ($id !== null) {
        $controllerInstance->$action($id);
    } else {
        $controllerInstance->$action();
    }
    
} catch (\Exception $e) {
    error_log("OR Module Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()),
            'line'    => $e->getLine()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<div style="font-family:Tahoma;direction:rtl;padding:50px;text-align:center;">';
        echo '<h1 style="color:#e74c3c;">❌ خطا در ماژول OR Analyzer</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo '<pre style="direction:ltr;text-align:left;background:#1e293b;color:#10b981;padding:20px;">';
            echo $e->getTraceAsString();
            echo '</pre>';
        }
        echo '<a href="' . CURRENT_MODULE_URL . '">بازگشت به داشبورد</a>';
        echo '</div>';
    }
}