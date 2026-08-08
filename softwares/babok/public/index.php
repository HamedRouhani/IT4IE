<?php
// تنظیمات خطا
error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع نشست
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// *** بارگذاری فیزیکی مدل‌ها قبل از هرگونه مسیریابی ***
// ==========================================================
require_once '/home/itieir/public_html/softwares/babok/app/Models/ProjectTask.php';
require_once '/home/itieir/public_html/softwares/babok/app/Models/KnowledgeArea.php';
require_once '/home/itieir/public_html/softwares/babok/app/Models/Project.php';
require_once '/home/itieir/public_html/softwares/babok/app/Models/Technique.php';
require_once '/home/itieir/public_html/softwares/babok/app/Models/Task.php';

// ==========================================================
// مسیریابی نرم‌افزار
// ==========================================================
$route = $_GET['route'] ?? 'home';
$id = $_GET['id'] ?? null;

try {
    switch ($route) {
        case 'home':
        case '':
            $controller = new App\Controllers\DashboardController();
            $controller->index();
            break;
        
        case 'projects':
            $controller = new App\Controllers\ProjectController();
            $controller->index();
            break;
        
        case 'projects_create':
            $controller = new App\Controllers\ProjectController();
            $controller->create();
            break;
        
        case 'projects_view':
            if ($id) {
                $controller = new App\Controllers\ProjectController();
                $controller->show($id);
            }
            break;
        
        case 'tasks':
            $controller = new App\Controllers\TaskController();
            $controller->index();
            break;
        
        case 'techniques':
            $controller = new App\Controllers\TechniqueController();
            $controller->index();
            break;
        
        case 'requirement':
            $controller = new App\Controllers\RequirementController();
            $controller->index();
            break;
        
        case 'requirement_analyze':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new App\Controllers\RequirementController();
                $controller->analyze();
            }
            break;
        
        default:
            http_response_code(404);
            echo "صفحه نرم‌افزار پیدا نشد.";
            break;
    }
} catch (Exception $e) {
    echo "<h1>❌ خطا در نرم‌افزار BABOK</h1>";
    echo "<p><strong>پیام:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>فایل:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>خط:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}