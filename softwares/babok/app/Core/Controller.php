<?php
namespace App\Core;

class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        
        ob_start();
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }
        $content = ob_get_clean();
        
        $layoutPath = __DIR__ . '/../../views/layouts/main.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * هدایت کاربر به آدرس مورد نظر
     * پشتیبانی از هر دو فرمت:
     * - /projects/view/1 (فرمت قدیمی)
     * - ?route=projects_view&id=1 (فرمت جدید)
     */
    protected function redirect($url)
    {
        // اگر URL با ?route= شروع شده، همان را استفاده کن
        if (strpos($url, '?route=') !== false) {
            header("Location: " . $url);
            exit;
        }

        // اگر URL با http شروع شد، همان را استفاده کن
        if (strpos($url, 'http') === 0) {
            header("Location: {$url}");
            exit;
        }

        // تبدیل مسیرهای قدیمی به فرمت جدید
        $basePath = '/babok/public';
        
        // تبدیل /projects/view/1 -> ?route=projects_view&id=1
        if (preg_match('#^/projects/view/([0-9]+)$#', $url, $matches)) {
            $url = $basePath . '/?route=projects_view&id=' . $matches[1];
        }
        // تبدیل /projects/create -> ?route=projects_create
        elseif ($url === '/projects/create') {
            $url = $basePath . '/?route=projects_create';
        }
        // تبدیل /projects/edit/1 -> ?route=projects_edit&id=1
        elseif (preg_match('#^/projects/edit/([0-9]+)$#', $url, $matches)) {
            $url = $basePath . '/?route=projects_edit&id=' . $matches[1];
        }
        // تبدیل /projects -> ?route=projects
        elseif ($url === '/projects') {
            $url = $basePath . '/?route=projects';
        }
        // تبدیل /tasks -> ?route=tasks
        elseif ($url === '/tasks') {
            $url = $basePath . '/?route=tasks';
        }
        // تبدیل /techniques -> ?route=techniques
        elseif ($url === '/techniques') {
            $url = $basePath . '/?route=techniques';
        }
        // تبدیل /knowledge-areas -> ?route=knowledge_areas
        elseif ($url === '/knowledge-areas') {
            $url = $basePath . '/?route=knowledge_areas';
        }
        // تبدیل /planning/1 -> ?route=planning&id=1
        elseif (preg_match('#^/planning/([0-9]+)$#', $url, $matches)) {
            $url = $basePath . '/?route=planning&id=' . $matches[1];
        }
        // مسیرهای دیگر: فقط base path را اضافه کن
        else {
            $url = $basePath . '/' . ltrim($url, '/');
        }

        header("Location: {$url}");
        exit;
    }
}