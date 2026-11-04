<?php
/** Time Study Analyzer 1.0.0 module entry point. */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!defined('MODULAR_APP_PATH')) define('MODULAR_APP_PATH', __DIR__);
if (!defined('CURRENT_MODULE')) define('CURRENT_MODULE', 'time-study-analyzer');
if (!defined('CURRENT_MODULE_URL')) define('CURRENT_MODULE_URL', '/software/time-study-analyzer/');

spl_autoload_register(static function (string $class): bool {
    $prefix = 'App\\Software\\TimeStudyAnalyzer\\';
    if (!str_starts_with($class, $prefix)) return false;
    $relative = substr($class, strlen($prefix));
    $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (!is_file($file)) return false;
    require_once $file;
    return true;
}, true, true);

require_once MODULAR_APP_PATH . '/app/Helpers/Functions.php';

$controllers = [
    'dashboard' => 'DashboardController',
    'study' => 'StudyController',
    'export' => 'ExportController',
    'learn' => 'LearnController',
];
$controllerKey = strtolower((string) ($_GET['controller'] ?? 'dashboard'));
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
    $class = 'App\\Software\\TimeStudyAnalyzer\\Controllers\\' . $controllers[$controllerKey];
    if (!class_exists($class)) throw new RuntimeException('بارگذاری بخش درخواستی ممکن نشد.');
    $controller = new $class();
    if (!method_exists($controller, $action) || str_starts_with($action, '_')) throw new RuntimeException('عملیات درخواستی پیدا نشد.');
    if ($controllerKey === 'study' && $action === 'recordSample') {
        $slotId = filter_var($_GET['slot'] ?? null, FILTER_VALIDATE_INT);
        $controller->$action((int) $id, (int) ($slotId ?: 0));
    } elseif ($id === null) {
        $controller->$action();
    } else {
        $controller->$action((int) $id);
    }
} catch (Throwable $e) {
    error_log('Time Study module error: ' . $e->getMessage());
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<main dir="rtl" style="max-width:760px;margin:3rem auto;padding:2rem;font-family:Vazirmatn,sans-serif">';
        echo '<h1>خطا در Time Study Analyzer</h1><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="/software/time-study-analyzer/">بازگشت به داشبورد</a></main>';
    }
}
