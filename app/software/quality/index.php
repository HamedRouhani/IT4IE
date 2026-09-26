<?php
/**
 * ============================================================
 * Entry Point ماژول Quality Analyzer
 * ============================================================
 * مسیر: app/software/quality/index.php
 * URL: /software/quality-analyzer/
 * 
 * بر اساس استانداردهای:
 *   - AIAG SPC Manual (2nd Edition)
 *   - AIAG MSA Manual (4th Edition)
 *   - ISO 22514 (Process Capability)
 *   - Nelson Rules (1984)
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// تعریف ثابت‌های ماژول
// ============================================
if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'quality');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/quality-analyzer/');
}
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', dirname(dirname(dirname(__DIR__))) . '/views');
}

// ============================================
// AUTOLOADER ماژول Quality
// ============================================
spl_autoload_register(function ($class) {
    // Namespace اختصاصی ماژول Quality
    $prefix = 'App\Software\Quality\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Namespace عمومی (Core اصلی سایت)
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

        // جستجو با نام lowercase (سازگاری لینوکس)
        $parts = explode('/', $classPath);
        if (count($parts) > 1) {
            $dir = strtolower(implode('/', array_slice($parts, 0, -1)));
            $file = end($parts);
            $lowerFile = dirname(dirname(dirname(__DIR__))) . '/app/' . $dir . '/' . $file;
            if (file_exists($lowerFile)) {
                require $lowerFile;
                return true;
            }
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

// ============================================
// لود Helperهای ماژول Quality
// ============================================
$moduleHelperPath = MODULAR_APP_PATH . '/app/Helpers/Functions.php';
if (file_exists($moduleHelperPath)) {
    require_once $moduleHelperPath;
}

// ============================================
// لود DateHelper (اگر وجود ندارد)
// ============================================
$dateHelperPath = dirname(dirname(dirname(__DIR__))) . '/app/helpers/DateHelper.php';
if (file_exists($dateHelperPath) && !class_exists('App\Helpers\DateHelper')) {
    require_once $dateHelperPath;
}

// ============================================
// ROUTING
// ============================================
$controller = $_GET['controller'] ?? 'dashboard';
$action     = $_GET['action']     ?? 'index';
$id         = $_GET['id']         ?? null;

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ============================================
// 🎯 نقشه کنترلرهای ماژول Quality
// ============================================
$controllerMap = [
    'dashboard'       => 'DashboardController',
    'system'          => 'SystemController',
    'project'         => 'ProjectController',
    'dataset'         => 'DatasetController',
    'control_chart'   => 'ControlChartController',
    'attribute_chart' => 'AttributeChartController',
    'capability'      => 'CapabilityController',
    'msa'             => 'MsaController',
    'sampling'        => 'SamplingController',
    'report'          => 'ReportController', 
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

    $controllerClass = 'App\\Software\\Quality\\Controllers\\' . $controllerMap[$controllerKey];

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

} catch (\Throwable $e) {
    error_log("Quality Module Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

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
        echo '<h1 style="color:#e74c3c;">❌ خطا در ماژول Quality Analyzer</h1>';
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