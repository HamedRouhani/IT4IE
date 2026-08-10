<?php
namespace App\Core;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            ob_start();
            include $viewPath;
            $content = ob_get_clean();
            
            $layoutPath = __DIR__ . '/../../views/layouts/main.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }
}