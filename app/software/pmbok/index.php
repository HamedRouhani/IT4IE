<?php
/**
 * ============================================================
 * Entry Point ماژول PMBOK Analyzer
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'pmbok');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/pmbok-analyzer/');
}

// Autoloader ماژول
spl_autoload_register(function ($class) {
    $modulePrefix = 'App\\Software\\Pmbok\\';
    $moduleLen = strlen($modulePrefix);
    
    if (strncmp($modulePrefix, $class, $moduleLen) === 0) {
        $relative = substr($class, $moduleLen);
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    $legacyPrefix = 'App\\';
    $legacyLen = strlen($legacyPrefix);
    
    if (strncmp($legacyPrefix, $class, $legacyLen) === 0) {
        $relative = substr($class, $legacyLen);
        $parts = explode('\\', $relative);
        
        if (count($parts) >= 2) {
            $type = $parts[0];
            if (in_array($type, ['Controllers', 'Models', 'Helpers'])) {
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
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

$controller = preg_replace('/[^a-zA-Z]/', '', $controller);
$action = preg_replace('/[^a-zA-Z]/', '', $action);
$id = is_numeric($id) ? (int)$id : null;

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// ============================================================
// 🆕 عملیات‌هایی که حتماً نیاز به ورود دارند
// ============================================================
$writeActions = [
    'store', 'update', 'delete', 'create',
    'addDeliverable', 'addStakeholder', 'addRisk', 'addTask',
    'deleteDeliverable', 'deleteStakeholder', 'deleteRisk', 'deleteTask',
];

$writePostActions = [
    'store', 'update', 'delete',
    'addDeliverable', 'addStakeholder', 'addRisk', 'addTask',
    'deleteDeliverable', 'deleteStakeholder', 'deleteRisk', 'deleteTask',
];

$isWriteRequest = in_array($action, $writePostActions) && $_SERVER['REQUEST_METHOD'] === 'POST';
$isCreatePage = in_array($action, ['create']) && $_SERVER['REQUEST_METHOD'] === 'GET';
$isEditPage = in_array($action, ['edit']) && $_SERVER['REQUEST_METHOD'] === 'GET';
$requiresAuth = $isWriteRequest || $isCreatePage || $isEditPage;

// ============================================================
// 🆕 بررسی احراز هویت
// ============================================================
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

// ============================================================
// ✅ Router اصلی - اصلاح شده برای case sensitivity
// ============================================================

// ✅ کلیدها همه lowercase هستند
$controllerMap = [
    'dashboard'      => 'DashboardController',
    'project'        => 'ProjectController',
    'task'           => 'TaskController',
    'knowledgearea'  => 'KnowledgeAreaController',  // ✅ lowercase
    'technique'      => 'TechniqueController',
    'risk'           => 'RiskController',
    'report'         => 'ReportController',
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
    
    // ✅ تبدیل به lowercase برای مقایسه
    $controllerKey = strtolower($controller);
    
    if (!isset($controllerMap[$controllerKey])) {
        throw new \Exception("کنترلر '{$controller}' یافت نشد.");
    }
    
    $controllerClass = '\\App\\Software\\Pmbok\\Controllers\\' . $controllerMap[$controllerKey];
    
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
    error_log("PMBOK Module Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<div style="font-family: Vazirmatn, Tahoma; direction: rtl; padding: 50px; text-align: center;">';
        echo '<h1 style="color: #e74c3c;">❌ خطا در پردازش</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<a href="' . CURRENT_MODULE_URL . '" style="color: #3498db;">بازگشت به داشبورد</a>';
        echo '</div>';
    }
}