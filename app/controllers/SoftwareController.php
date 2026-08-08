<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Software;
use App\Models\Category;
use App\Models\Setting;

class SoftwareController extends Controller
{
    public function index()
    {
        $softwareModel = new Software();
        $categoryModel = new Category();
        $settingModel = new Setting();
        
        // دریافت همه نرم‌افزارهای فعال
        $softwareList = $softwareModel->getActiveSoftware();
        $categories = $categoryModel->getTree();
        $settings = $settingModel->getAll();
        
        // آمار برای بخش بالای صفحه
        $totalSoftware = count($softwareList);
        $totalDownloads = $softwareModel->getTotalDownloads();
        
        $this->render('software/index', [
            'title' => 'نرم‌افزارهای تخصصی - IT4IE',
            'softwareList' => $softwareList,
            'categories' => $categories,
            'settings' => $settings,
            'totalSoftware' => $totalSoftware,
            'totalDownloads' => $totalDownloads
        ]);
    }
    
    public function detail($slug)
    {
        $softwareModel = new Software();
        $categoryModel = new Category();
        $settingModel = new Setting();
        
        $software = $softwareModel->getBySlug($slug);
        
        if (!$software) {
            http_response_code(404);
            echo "404 Not Found";
            exit;
        }
        
        $categories = $categoryModel->getTree();
        $settings = $settingModel->getAll();
        
        $this->render('software/detail', [
            'title' => $software['name'] . ' - IT4IE',
            'software' => $software,
            'categories' => $categories,
            'settings' => $settings
        ]);
    }
}