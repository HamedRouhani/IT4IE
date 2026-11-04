<?php
/** Engineering Economy Analyzer — نقطهٔ ورود ماژول. */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!defined('MODULAR_APP_PATH')) define('MODULAR_APP_PATH', __DIR__);
if (!defined('CURRENT_MODULE')) define('CURRENT_MODULE', 'engineering-economy');
if (!defined('CURRENT_MODULE_URL')) define('CURRENT_MODULE_URL', '/software/engineering-economy/');

spl_autoload_register(static function (string $class): bool {
    $prefix = 'App\\Software\\EngineeringEconomy\\';
    if (!str_starts_with($class, $prefix)) return false;
    $relative = substr($class, strlen($prefix));
    $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (!is_file($file)) return false;
    require_once $file;
    return true;
}, true, true);

require_once MODULAR_APP_PATH . '/app/Helpers/Functions.php';

// گارد CSRF سراسری سامانه برای همهٔ درخواست‌های تغییردهندهٔ داده.
if (function_exists('it4ie_require_csrf')) {
    it4ie_require_csrf();
}

$controllers = [
    'project' => 'ProjectController',
    'cashflow' => 'CashFlowController',
    'analysis' => 'AnalysisController',
    'learn' => 'LearnController',
];

$controllerKey = strtolower((string) ($_GET['controller'] ?? 'project'));
$action = (string) ($_GET['action'] ?? 'index');
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

try {
    if ($controllerKey === 'exit') {
        unset($_SESSION['current_software']);
        header('Location: /software');
        exit;
    }

    if (!isset($controllers[$controllerKey])) throw new RuntimeException('بخش درخواستی پیدا نشد.');
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $action)) throw new RuntimeException('نام عملیات معتبر نیست.');

    $class = 'App\\Software\\EngineeringEconomy\\Controllers\\' . $controllers[$controllerKey];
    if (!class_exists($class)) throw new RuntimeException('بارگذاری بخش درخواستی ممکن نشد.');

    $controller = new $class();
    if (!method_exists($controller, $action) || str_starts_with($action, '_')) {
        throw new RuntimeException('عملیات درخواستی پیدا نشد.');
    }

    if ($id === null) {
        $controller->{$action}();
    } else {
        $controller->{$action}((int) $id);
    }
} catch (Throwable $e) {
    error_log('Engineering Economy module error: ' . $e->getMessage());

    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo '<main dir="rtl" style="max-width:760px;margin:3rem auto;padding:2rem;font-family:Vazirmatn,sans-serif">';
        echo '<h1>خطا در ماژول اقتصاد مهندسی</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="' . CURRENT_MODULE_URL . '">بازگشت به فهرست پروژه‌ها</a></main>';
    }
}
