<?php
/**
 * ============================================================
 * Entry Point ماژول MCDM Analyzer
 * ============================================================
 * مسیر: app/software/mcdm/index.php
 * فراخوانی از public/index.php وقتی URL = /software/mcdm-analyzer/
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'mcdm');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/mcdm-analyzer/');
}

// ============================================================
// Autoloader ماژول MCDM
// ============================================================
spl_autoload_register(function ($class) {
    // ۱. namespace ماژول: App\Software\Mcdm\
    $modulePrefix = 'App\Software\Mcdm\\';
    $moduleLen = strlen($modulePrefix);
    if (strncmp($modulePrefix, $class, $moduleLen) === 0) {
        $relative = substr($class, $moduleLen);
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // ۲. namespace قدیمی App\ (سازگاری با Core اصلی)
    $legacyPrefix = 'App\\';
    $legacyLen = strlen($legacyPrefix);
    if (strncmp($legacyPrefix, $class, $legacyLen) === 0) {
        $relative = substr($class, $legacyLen);
        $parts = explode('\\', $relative);
        if (count($parts) >= 2) {
            $type = $parts[0]; // Controllers, Models, Helpers, Core, Services
            if (in_array($type, ['Controllers', 'Models', 'Helpers', 'Core', 'Services'])) {
                $newPath = MODULAR_APP_PATH . '/app/' . $type . '/' . implode('/', array_slice($parts, 1)) . '.php';
                if (file_exists($newPath)) {
                    require $newPath;
                    return true;
                }
            }
        }
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
}, true, true);

// لود Helper ها
require_once MODULAR_APP_PATH . '/app/Helpers/Functions.php';

// دریافت پارامترها
$controller = $_GET['controller'] ?? 'dashboard';
$action     = $_GET['action'] ?? 'index';
$id         = $_GET['id'] ?? null;

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// ============================================================
// Router اصلی
// ============================================================
$controllerMap = [
    'dashboard'     => 'DashboardController',
    'project'       => 'ProjectController',
    'method'        => 'MethodController',
    'knowledgearea' => 'KnowledgeAreaController',
    'calculator'    => 'CalculatorController',
    'report'        => 'ReportController',
    'assistant'     => 'AssistantController',
    'industry'      => 'IndustryController',
];

try {
    // خروج از نرم‌افزار
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

    $controllerClass = '\App\Software\Mcdm\Controllers\\' . $controllerMap[$controllerKey];

    if (!class_exists($controllerClass)) {
        throw new \Exception("کلاس '{$controllerClass}' یافت نشد.");
    }

    $controllerInstance = new $controllerClass();

    if (!method_exists($controllerInstance, $action)) {
        throw new \Exception("متد '{$action}' در کنترلر '{$controller}' یافت نشد.");
    }

    if ($id !== null) {
        $controllerInstance->$action($id);
    } else {
        $controllerInstance->$action();
    }

} catch (\Exception $e) {
    error_log("MCDM Module Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<div style="font-family: Vazirmatn, Tahoma; direction: rtl; padding: 50px; text-align: center;">';
        echo '<h1 style="color: #e74c3c;">❌ خطا در پردازش</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<a href="' . CURRENT_MODULE_URL . '" style="color: #3498db;">بازگشت به داشبورد</a>';
        echo '</div>';
    }
}