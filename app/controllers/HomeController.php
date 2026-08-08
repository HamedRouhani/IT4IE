<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $postModel = new Post();
        $settingModel = new Setting();
        
        $posts = $postModel->getPublished();
        $settings = $settingModel->getAll();
        
        $this->render('home/index', [
            'title' => 'IT4IE - مشاوره بین‌رشته‌ای',
            'posts' => $posts,
            'settings' => $settings
        ]);
    }
    
    public function post($slug)
    {
        $postModel = new Post();
        $settingModel = new Setting();
        
        $post = $postModel->getBySlug($slug);
        
        if (!$post) {
            http_response_code(404);
            echo "404 Not Found";
            exit;
        }
        
        $postModel->incrementView($post['id']);
        $settings = $settingModel->getAll();
        
        $this->render('home/post', [
            'title' => $post['title'],
            'post' => $post,
            'settings' => $settings
        ]);
    }
    
    public function category($slug)
    {
        $categoryModel = new Category();
        $postModel = new Post();
        $settingModel = new Setting();
        
        $category = $categoryModel->getBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            echo "404 Not Found";
            exit;
        }
        
        $posts = $postModel->getByCategory($category['id']);
        $settings = $settingModel->getAll();
        
        $this->render('home/category', [
            'title' => $category['name'],
            'category' => $category,
            'posts' => $posts,
            'settings' => $settings
        ]);
    }
}