<?php
/** Inventory Analyzer module entry point. */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MODULAR_APP_PATH')) define('MODULAR_APP_PATH', __DIR__);
if (!defined('CURRENT_MODULE')) define('CURRENT_MODULE', 'inventory');
if (!defined('CURRENT_MODULE_URL')) define('CURRENT_MODULE_URL', '/software/inventory-analyzer/');
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', dirname(dirname(dirname(__DIR__))) . '/views');

spl_autoload_register(static function ($class) {
    $prefix = 'App\\Software\\Inventory\\';
    if (strpos($class, $prefix) !== 0) return false;
    $relative = substr($class, strlen($prefix));
    $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
        return true;
    }
    return false;
}, true, true);

require_once MODULAR_APP_PATH . '/app/Helpers/Functions.php';

$controllerMap = [
    'dashboard' => 'DashboardController',
    'item'      => 'ItemController',
    'movement'  => 'MovementController',
    'analysis'  => 'AnalysisController',
];
$controllerKey = strtolower((string) ($_GET['controller'] ?? 'dashboard'));
$action = (string) ($_GET['action'] ?? 'index');
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

try {
    if ($controllerKey === 'exit') {
        unset($_SESSION['current_software']);
        header('Location: /software');
        exit;
    }
    if (!isset($controllerMap[$controllerKey])) {
        http_response_code(404);
        throw new RuntimeException('بخش درخواستی پیدا نشد.');
    }
    $class = 'App\\Software\\Inventory\\Controllers\\' . $controllerMap[$controllerKey];
    if (!class_exists($class)) throw new RuntimeException('بارگذاری بخش درخواستی ممکن نشد.');
    $controller = new $class();
    if (!method_exists($controller, $action) || str_starts_with($action, '_')) {
        http_response_code(404);
        throw new RuntimeException('عملیات درخواستی پیدا نشد.');
    }
    $id === null ? $controller->$action() : $controller->$action($id);
} catch (Throwable $e) {
    error_log('Inventory module error: ' . $e->getMessage());
    if ($isAjax) {
        http_response_code(http_response_code() >= 400 ? http_response_code() : 500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<main dir="rtl" style="max-width:760px;margin:3rem auto;padding:2rem;font-family:Vazirmatn,sans-serif">';
        echo '<h1>خطا در Inventory Analyzer</h1><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="' . htmlspecialchars(CURRENT_MODULE_URL, ENT_QUOTES, 'UTF-8') . '">بازگشت به داشبورد</a></main>';
    }
}
