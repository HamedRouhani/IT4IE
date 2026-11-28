<?php

declare(strict_types=1);

/** Factory Layout Analyzer — educational foundation (development release). */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MODULAR_APP_PATH')) define('MODULAR_APP_PATH', __DIR__);
if (!defined('CURRENT_MODULE')) define('CURRENT_MODULE', 'factory-layout-analyzer');
if (!defined('CURRENT_MODULE_URL')) define('CURRENT_MODULE_URL', '/software/factory-layout-analyzer/');

spl_autoload_register(static function (string $class): bool {
    $prefix = 'App\\Software\\FactoryLayoutAnalyzer\\';
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
    'project' => 'ProjectController',
    'product' => 'ProductController',
    'route' => 'RouteController',
    'service' => 'ServiceController',
    'relationship' => 'RelationshipController',
    'flow' => 'MaterialFlowController',
    'learn' => 'LearnController',
];
$allowedActions = [
    'dashboard' => ['index'],
    'project' => ['index', 'create', 'store', 'show', 'edit', 'update', 'delete'],
    'product' => ['create', 'store', 'edit', 'update', 'delete'],
    'route' => ['create', 'store', 'edit', 'update', 'delete', 'move'],
    'service' => ['create', 'store', 'edit', 'update', 'delete'],
    'relationship' => ['store', 'update', 'delete'],
    'flow' => ['store', 'update', 'delete'],
    'learn' => ['index', 'show'],
];
$controllerKey = strtolower((string) ($_GET['controller'] ?? 'dashboard'));
$action = (string) ($_GET['action'] ?? 'index');
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$projectId = filter_var($_GET['project_id'] ?? $_POST['project_id'] ?? null, FILTER_VALIDATE_INT);
$productId = filter_var($_GET['product_id'] ?? $_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

try {
    if (function_exists('it4ie_require_csrf')) it4ie_require_csrf();
    if ($controllerKey === 'exit') {
        unset($_SESSION['current_software']);
        header('Location: /software');
        exit;
    }
    if (!isset($controllers[$controllerKey]) || !in_array($action, $allowedActions[$controllerKey] ?? [], true)) {
        http_response_code(404);
        throw new RuntimeException('بخش درخواستی پیدا نشد.');
    }

    $class = 'App\\Software\\FactoryLayoutAnalyzer\\Controllers\\' . $controllers[$controllerKey];
    if (!class_exists($class)) throw new RuntimeException('بارگذاری بخش درخواستی ممکن نشد.');
    $controller = new $class();
    if ($controllerKey === 'project' && in_array($action, ['show', 'edit', 'update', 'delete'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('پروژهٔ درخواستی پیدا نشد.');
        }
        $controller->{$action}($id);
    } elseif ($controllerKey === 'product' && in_array($action, ['edit', 'update', 'delete'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('محصول درخواستی پیدا نشد.');
        }
        $controller->{$action}($id);
    } elseif ($controllerKey === 'product' && in_array($action, ['create', 'store'], true)) {
        if (!is_int($projectId) || $projectId < 1) {
            http_response_code(404);
            throw new RuntimeException('پروژهٔ درخواستی پیدا نشد.');
        }
        $controller->{$action}($projectId);
    } elseif ($controllerKey === 'route' && in_array($action, ['edit', 'update', 'delete', 'move'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('گام مسیر درخواستی پیدا نشد.');
        }
        $controller->{$action}($id);
    } elseif ($controllerKey === 'route' && in_array($action, ['create', 'store'], true)) {
        if (!is_int($productId) || $productId < 1) {
            http_response_code(404);
            throw new RuntimeException('محصول درخواستی پیدا نشد.');
        }
        $controller->{$action}($productId);
    } elseif ($controllerKey === 'relationship' && in_array($action, ['delete', 'update'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('Relationship not found.');
        }
        $controller->{$action}($id);
    } elseif ($controllerKey === 'flow' && in_array($action, ['delete', 'update'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('Flow not found.');
        }
        $controller->{$action}($id);
    } elseif ($controllerKey === 'service' && in_array($action, ['edit', 'update', 'delete'], true)) {
        if (!is_int($id) || $id < 1) {
            http_response_code(404);
            throw new RuntimeException('خدمت پشتیبان درخواستی پیدا نشد.');
        }
        $controller->{$action}($id);
    } elseif (in_array($controllerKey, ['service', 'relationship', 'flow'], true) && in_array($action, ['create', 'store'], true)) {
        if (!is_int($projectId) || $projectId < 1) {
            http_response_code(404);
            throw new RuntimeException('پروژهٔ درخواستی پیدا نشد.');
        }
        $controller->{$action}($projectId);
    } else {
        $controller->{$action}();
    }
} catch (Throwable $e) {
    error_log('Factory Layout Analyzer error: ' . $e->getMessage());
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'خطای داخلی رخ داد. لطفاً بعداً دوباره تلاش کنید.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (http_response_code() < 400) http_response_code(500);
    echo '<main dir="rtl" style="max-width:760px;margin:3rem auto;padding:2rem;font-family:Vazirmatn,sans-serif">';
    echo '<h1>خطا در ماژول طراحی چیدمان کارخانه</h1><p>امکان نمایش این بخش وجود ندارد.</p>';
    echo '<a href="' . htmlspecialchars(CURRENT_MODULE_URL, ENT_QUOTES, 'UTF-8') . '">بازگشت به صفحهٔ اصلی</a></main>';
}
