<?php
use App\Core\Router;

$router = new Router();
$basePath = '/babok/public'; // ⚠️ این مسیر را بررسی کنید

// =============================================
// مسیرهای اصلی (داشبورد)
// =============================================
$router->addRoute('GET', $basePath . '/', 'DashboardController@index');
$router->addRoute('GET', $basePath . '', 'DashboardController@index'); // برای دسترسی بدون اسلش

// =============================================
// مسیرهای پروژه‌ها
// =============================================
$router->addRoute('GET', $basePath . '/projects', 'ProjectController@index');
$router->addRoute('GET', $basePath . '/projects/create', 'ProjectController@create');
$router->addRoute('POST', $basePath . '/projects/store', 'ProjectController@store');
$router->addRoute('GET', $basePath . '/projects/view/{id}', 'ProjectController@view');
$router->addRoute('GET', $basePath . '/projects/edit/{id}', 'ProjectController@edit');
$router->addRoute('POST', $basePath . '/projects/update/{id}', 'ProjectController@update');
$router->addRoute('GET', $basePath . '/projects/delete/{id}', 'ProjectController@delete');

// =============================================
// مسیرهای برنامه‌ریزی پروژه
// =============================================
$router->addRoute('GET', $basePath . '/project-planning/{id}', 'ProjectPlanningController@index');
$router->addRoute('POST', $basePath . '/project-planning/add-task', 'ProjectPlanningController@addTask');
$router->addRoute('POST', $basePath . '/project-planning/remove-task', 'ProjectPlanningController@removeTask');
$router->addRoute('POST', $basePath . '/project-planning/update-status', 'ProjectPlanningController@updateTaskStatus');
$router->addRoute('GET', $basePath . '/project-planning/recommended/{id}', 'ProjectPlanningController@getRecommendedTasks');

// =============================================
// مسیرهای پیشنهادات
// =============================================
$router->addRoute('GET', $basePath . '/recommendations/task', 'RecommendationController@forTask');
$router->addRoute('GET', $basePath . '/recommendations/project/{id}', 'RecommendationController@forProject');
$router->addRoute('GET', $basePath . '/recommendations/task/{id}', 'RecommendationController@showTaskRecommendations');
$router->addRoute('POST', $basePath . '/recommendations/analyze', 'RecommendationController@analyzeText');
$router->addRoute('GET', $basePath . '/recommendations/analyzer', 'RecommendationController@analyzer');

// =============================================
// مسیرهای مدیریت دانش BABOK
// =============================================
$router->addRoute('GET', $basePath . '/knowledge-areas', 'KnowledgeAreaController@index');
$router->addRoute('GET', $basePath . '/knowledge-areas/view/{id}', 'KnowledgeAreaController@view');

$router->addRoute('GET', $basePath . '/tasks', 'TaskController@index');
$router->addRoute('GET', $basePath . '/tasks/view/{id}', 'TaskController@view');
$router->addRoute('GET', $basePath . '/tasks/techniques/{id}', 'TaskController@techniques');
$router->addRoute('POST', $basePath . '/tasks/add-technique', 'TaskController@addTechnique');
$router->addRoute('POST', $basePath . '/tasks/remove-technique', 'TaskController@removeTechnique');

$router->addRoute('GET', $basePath . '/techniques', 'TechniqueController@index');
$router->addRoute('GET', $basePath . '/techniques/view/{id}', 'TechniqueController@view');
$router->addRoute('GET', $basePath . '/techniques/category/{category}', 'TechniqueController@byCategory');
$router->addRoute('GET', $basePath . '/techniques/search', 'TechniqueController@search');

// =============================================
// مسیر 404
// =============================================
$router->setNotFound(function() {
    http_response_code(404);
    echo "404 - صفحه مورد نظر یافت نشد.";
});

// مسیر تست ساده
$router->addRoute('GET', $basePath . '/test', function() {
    echo "✅ مسیر تست به درستی کار می‌کند!";
});

return $router;
?>