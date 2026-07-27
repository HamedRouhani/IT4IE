<?php
namespace App\Core;

abstract class Controller
{
    protected $data = [];
    protected $layout = 'main';
    
    public function render($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
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
    
    /**
     * Render auth pages (without sidebar and footer)
     */
    public function renderAuth($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $view");
        }
        $content = ob_get_clean();
        
        $layoutFile = VIEWS_PATH . '/layouts/auth.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * Render admin pages (only header)
     */
    public function renderAdmin($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
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