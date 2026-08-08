<?php
namespace App\Core;

use App\Models\Category;

abstract class Controller
{
    protected $data = [];
    protected $layout = 'main';
    
    public function render($view, $data = [])
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getTree();
        
        $this->data = array_merge($this->data, $data, ['categories' => $categories]);
        extract($this->data);
        
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $view");
        }
        $content = ob_get_clean();
        
        $layoutFile = VIEWS_PATH . '/layouts/' . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    public function renderAuth($view, $data = [])
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getTree();
        
        $this->data = array_merge($this->data, $data, ['categories' => $categories]);
        extract($this->data);
        
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $view");
        }
        $content = ob_get_clean();
        
        // استفاده از لایه auth (بدون سایدبار و فوتر)
        $layoutFile = VIEWS_PATH . '/layouts/auth.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    public function renderAdmin($view, $data = [])
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getTree();
        
        $this->data = array_merge($this->data, $data, ['categories' => $categories]);
        extract($this->data);
        
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $view");
        }
        $content = ob_get_clean();
        
        $layoutFile = VIEWS_PATH . '/layouts/admin.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
}