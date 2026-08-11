<?php
/**
 * ============================================================
 * Entry Point ماژول BABOK Analyzer
 * ============================================================
 * مسیر: app/software/babok/index.php
 * 
 * این فایل از طریق index.php اصلی IT4IE فراخوانی می‌شود
 * وقتی URL به صورت /software/babok/ باشد
 * 
 * ثابت‌های مورد نیاز (از index.php اصلی تنظیم می‌شوند):
 * - MODULAR_APP_PATH: مسیر ماژول
 * - CURRENT_MODULE: نام ماژول (babok)
 * - CURRENT_MODULE_URL: URL پایه ماژول (/software/babok/)
 * - APP_PATH, VIEWS_PATH, PUBLIC_PATH: مسیرهای اصلی IT4IE
 * ============================================================
 */

// بررسی session از سیستم اصلی
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی وجود ثابت‌های مورد نیاز
if (!defined('MODULAR_APP_PATH')) {
    define('MODULAR_APP_PATH', __DIR__);
}
if (!defined('CURRENT_MODULE')) {
    define('CURRENT_MODULE', 'babok');
}
if (!defined('CURRENT_MODULE_URL')) {
    define('CURRENT_MODULE_URL', '/software/babok/');
}

// ============================================================
// Autoloader ماژول BABOK
// ============================================================
spl_autoload_register(function ($class) {
    // ۱. اولویت با namespace ماژول: App\Software\Babok\
    $modulePrefix = 'App\\Software\\Babok\\';
    $moduleLen = strlen($modulePrefix);
    
    if (strncmp($modulePrefix, $class, $moduleLen) === 0) {
        $relative = substr($class, $moduleLen);
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    // ۲. namespace قدیمی App\ (برای سازگاری با کدهای اصلی BABOK)
    $legacyPrefix = 'App\\';
    $legacyLen = strlen($legacyPrefix);
    
    if (strncmp($legacyPrefix, $class, $legacyLen) === 0) {
        $relative = substr($class, $legacyLen);
        
        // تبدیل App\Controllers\X به App\Software\Babok\Controllers\X
        // تبدیل App\Models\X به App\Software\Babok\Models\X
        // تبدیل App\Services\X به App\Software\Babok\Services\X
        // تبدیل App\Core\X به App\Software\Babok\Core\X
        
        $parts = explode('\\', $relative);
        if (count($parts) >= 2) {
            $type = $parts[0]; // Controllers, Models, Services, Core
            
            if (in_array($type, ['Controllers', 'Models', 'Services', 'Core'])) {
                $newPath = MODULAR_APP_PATH . '/app/' . $type . '/' . implode('/', array_slice($parts, 1)) . '.php';
                if (file_exists($newPath)) {
                    require $newPath;
                    return true;
                }
            }
        }
        
        // جستجوی مستقیم در ماژول
        $file = MODULAR_APP_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    return false;
}, true, true); // prepend = true برای اولویت بالاتر

// ============================================================
// دریافت پارامترهای route
// ============================================================
$route = $_GET['route'] ?? 'home';
$id = $_GET['id'] ?? null;

// بررسی اینکه آیا درخواست AJAX است
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// ============================================================
// بررسی احراز هویت برای عملیات write
// ============================================================
$writeRoutes = [
    'projects_store',
    'projects_update',
    'projects_delete',
    'planning_add_task',
    'planning_remove_task',
    'planning_update_status',
    'tasks_add_technique',
    'tasks_remove_technique'
];

if (in_array($route, $writeRoutes) && !isset($_SESSION['user_id'])) {
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
        $_SESSION['auth_message'] = 'برای انجام این عملیات لطفاً وارد شوید.';
        header('Location: /login');
        exit;
    }
}

// مسیرهایی که حتماً نیاز به ورود دارند
$authRequiredRoutes = [
    'projects', 'projects_create', 'projects_store', 'projects_view',
    'projects_edit', 'projects_update', 'projects_delete',
    'planning', 'planning_add_task', 'planning_remove_task',
    'planning_update_status', 'planning_recommended',
];

if (in_array($route, $authRequiredRoutes) && !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['auth_message'] = 'برای مدیریت پروژه‌ها لطفاً وارد شوید.';
    header('Location: /login');
    exit;
}

// ============================================================
// Router اصلی ماژول BABOK
// ============================================================
try {
    switch ($route) {
        
        // ==========================================
        // صفحه اصلی (داشبورد)
        // ==========================================
        case 'home':
        case '':
            $controller = new \App\Software\Babok\Controllers\DashboardController();
            $controller->index();
            break;
        
        // ==========================================
        // مدیریت پروژه‌ها
        // ==========================================
        case 'projects':
            $controller = new \App\Software\Babok\Controllers\ProjectController();
            $controller->index();
            break;
        
        case 'projects_create':
            $controller = new \App\Software\Babok\Controllers\ProjectController();
            $controller->create();
            break;
        
        case 'projects_store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\ProjectController();
                $controller->store();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        case 'projects_view':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\ProjectController();
                $controller->show($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        case 'projects_edit':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\ProjectController();
                $controller->edit($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        case 'projects_update':
            if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\ProjectController();
                $controller->update($id);
            } else {
                throw new \Exception('درخواست نامعتبر.');
            }
            break;
        
        case 'projects_delete':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\ProjectController();
                $controller->delete($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        // ==========================================
        // برنامه‌ریزی پروژه
        // ==========================================
        case 'planning':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\ProjectPlanningController();
                $controller->index($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        case 'planning_add_task':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\ProjectPlanningController();
                $controller->addTask();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        case 'planning_remove_task':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\ProjectPlanningController();
                $controller->removeTask();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        case 'planning_update_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\ProjectPlanningController();
                $controller->updateTaskStatus();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        case 'planning_recommended':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\ProjectPlanningController();
                $controller->getRecommendedTasks($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        // ==========================================
        // مدیریت وظایف
        // ==========================================
        case 'tasks':
            $controller = new \App\Software\Babok\Controllers\TaskController();
            $controller->index();
            break;
        
        case 'tasks_view':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\TaskController();
                $controller->show($id);
            } else {
                throw new \Exception('شناسه وظیفه مشخص نشده است.');
            }
            break;
        
        case 'tasks_techniques':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\TaskController();
                $controller->techniques($id);
            } else {
                throw new \Exception('شناسه وظیفه مشخص نشده است.');
            }
            break;
        
        case 'tasks_add_technique':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\TaskController();
                $controller->addTechnique();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        case 'tasks_remove_technique':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\TaskController();
                $controller->removeTechnique();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        // ==========================================
        // مدیریت تکنیک‌ها
        // ==========================================
        case 'techniques':
            $controller = new \App\Software\Babok\Controllers\TechniqueController();
            $controller->index();
            break;
        
        case 'techniques_view':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\TechniqueController();
                $controller->show($id);
            } else {
                throw new \Exception('شناسه تکنیک مشخص نشده است.');
            }
            break;
        
        case 'techniques_category':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\TechniqueController();
                $controller->byCategory($id);
            } else {
                throw new \Exception('دسته‌بندی مشخص نشده است.');
            }
            break;
        
        case 'techniques_search':
            $controller = new \App\Software\Babok\Controllers\TechniqueController();
            $controller->search();
            break;
        
        // ==========================================
        // مدیریت حوزه‌های دانشی
        // ==========================================
        case 'knowledge_areas':
            $controller = new \App\Software\Babok\Controllers\KnowledgeAreaController();
            $controller->index();
            break;
        
        case 'knowledge_areas_view':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\KnowledgeAreaController();
                $controller->show($id);
            } else {
                throw new \Exception('شناسه حوزه دانشی مشخص نشده است.');
            }
            break;
        
        // ==========================================
        // پیشنهادات (Recommendations)
        // ==========================================
        case 'recommendations_task':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\RecommendationController();
                $controller->showTaskRecommendations($id);
            } else {
                throw new \Exception('شناسه وظیفه مشخص نشده است.');
            }
            break;
        
        case 'recommendations_for_task':
            $controller = new \App\Software\Babok\Controllers\RecommendationController();
            $controller->forTask();
            break;
        
        case 'recommendations_for_project':
            if ($id) {
                $controller = new \App\Software\Babok\Controllers\RecommendationController();
                $controller->forProject($id);
            } else {
                throw new \Exception('شناسه پروژه مشخص نشده است.');
            }
            break;
        
        case 'recommendations_analyzer':
            $controller = new \App\Software\Babok\Controllers\RecommendationController();
            $controller->analyzer();
            break;
        
        case 'recommendations_analyze':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\RecommendationController();
                $controller->analyzeText();
            } else {
                throw new \Exception('روش درخواست مجاز نیست.');
            }
            break;
        
        // ==========================================
        // استخراج و تحلیل یکپارچه نیازمندی
        // ==========================================
        case 'requirement':
            $controller = new \App\Software\Babok\Controllers\RequirementController();
            $controller->index();
            break;
        
        case 'requirement_result':
            $controller = new \App\Software\Babok\Controllers\RequirementController();
            $controller->result();
            break;
        
        case 'requirement_analyze':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new \App\Software\Babok\Controllers\RequirementController();
                $controller->analyze();
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(405);
                    echo json_encode(['error' => 'روش درخواست مجاز نیست.'], JSON_UNESCAPED_UNICODE);
                } else {
                    throw new \Exception('روش درخواست مجاز نیست.');
                }
            }
            break;
        
        // ==========================================
        // خروج از نرم‌افزار
        // ==========================================
        case 'exit':
            if (isset($_SESSION['current_software'])) {
                unset($_SESSION['current_software']);
            }
            header('Location: /software');
            exit;
        
        // ==========================================
        // مسیر 404
        // ==========================================
        default:
            http_response_code(404);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'مسیر مورد نظر یافت نشد',
                    'route' => $route
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo '<div style="font-family: Tahoma; direction: rtl; padding: 50px; text-align: center;">';
                echo '<h1>404 - صفحه مورد نظر یافت نشد</h1>';
                echo '<p>مسیر درخواستی: <code>' . htmlspecialchars($route) . '</code></p>';
                echo '<hr>';
                echo '<h3>📋 مسیرهای موجود:</h3>';
                echo '<ul style="list-style: none; padding: 0;">';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=home">🏠 خانه</a></li>';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=projects">📁 پروژه‌ها</a></li>';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=tasks">📋 وظایف</a></li>';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=techniques">🔧 تکنیک‌ها</a></li>';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=knowledge_areas">📊 حوزه‌های دانشی</a></li>';
                echo '<li><a href="' . CURRENT_MODULE_URL . '?route=requirement">🤖 استخراج و تحلیل نیازمندی</a></li>';
                echo '</ul>';
                echo '<a href="' . CURRENT_MODULE_URL . '?route=home" style="color: #3498db;">بازگشت به داشبورد</a>';
                echo '</div>';
            }
            break;
    }
} catch (\Exception $e) {
    // ثبت خطا در لاگ
    error_log("BABOK Module Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // نمایش خطا به کاربر
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<div style="font-family: Tahoma; direction: rtl; padding: 50px; text-align: center;">';
        echo '<h1 style="color: #e74c3c;">❌ خطا در پردازش</h1>';
        echo '<p style="font-size: 1.1rem;">' . htmlspecialchars($e->getMessage()) . '</p>';
        
        // فقط در حالت development جزئیات بیشتر نمایش داده شود
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo '<p style="font-size: 0.85rem; color: #999;">📁 فایل: ' . $e->getFile() . '</p>';
            echo '<p style="font-size: 0.85rem; color: #999;">📍 خط: ' . $e->getLine() . '</p>';
            echo '<pre style="text-align: left; direction: ltr; background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto;">' . $e->getTraceAsString() . '</pre>';
        }
        
        echo '<a href="' . CURRENT_MODULE_URL . '?route=home" style="color: #3498db; display: inline-block; margin-top: 20px;">بازگشت به داشبورد</a>';
        echo '</div>';
    }
}