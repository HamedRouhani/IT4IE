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
        $categoryModel = new Category();
        $settingModel = new Setting();
        
        $posts = $postModel->getPublished();
        $categories = $categoryModel->getTree();
        $settings = $settingModel->getAll();
        
        $this->render('home/index', [
            'title' => 'IT4IE - مشاوره بین‌رشته‌ای',
            'posts' => $posts,
            'categories' => $categories,
            'settings' => $settings
        ]);
    }
}