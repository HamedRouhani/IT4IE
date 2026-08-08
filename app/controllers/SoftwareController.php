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
        
        $softwareList = $softwareModel->getActiveSoftware();
        $categories = $categoryModel->getTree();
        $settings = $settingModel->getAll();
        
        $totalSoftware = count($softwareList);
        $totalDownloads = $softwareModel->getTotalDownloads();
        
        // تغییر کلیدی: استفاده از render و سپس لود کردن دستی ویو با main_layout
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

    /**
     * اجرای نرم‌افزارهای مستقل ماژولار
     * پارامترها: {slug} نام نرم‌افزار (مثلاً babok-analyzer) و {params} ادامه مسیر (مثلاً public)
     */
    public function execute($slug, $params = '')
    {
        // ۱. نام پوشه نرم‌افزار را مشخص کن
        $folderName = $slug;
        if ($slug === 'babok-analyzer') {
            $folderName = 'babok';
        }

        // ۲. مسیر فیزیکی پوشه
        $softwareDir = ROOT_PATH . '/softwares/' . $folderName;

        // ۳. اگر پوشه وجود ندارد، خطا بده
        if (!is_dir($softwareDir)) {
            http_response_code(404);
            echo "نرم‌افزار درخواستی یافت نشد.";
            exit;
        }

        // ۴. تنظیم مسیر برای اتولودر اصلی (مهم‌ترین خط)
        if (!defined('MODULAR_APP_PATH')) {
            define('MODULAR_APP_PATH', $softwareDir);
        }

        // ۵. اگر پارامتر params خالی است، به روت نرم‌افزار برو
        if (empty($params)) {
            $entryFile = $softwareDir . '/index.php';
            if (file_exists($entryFile)) {
                require_once $entryFile;
                exit;
            } else {
                http_response_code(404);
                echo "فایل ورودی نرم‌افزار یافت نشد.";
                exit;
            }
        }

        // ۶. اگر پارامتر params وجود دارد
        $targetPath = $softwareDir . '/' . $params;
        if (file_exists($targetPath)) {
            if (is_file($targetPath)) {
                require_once $targetPath;
                exit;
            } elseif (is_dir($targetPath) && file_exists($targetPath . '/index.php')) {
                require_once $targetPath . '/index.php';
                exit;
            } else {
                http_response_code(200);
                exit;
            }
        } else {
            http_response_code(404);
            echo "فایل یا پوشه درخواستی در نرم‌افزار یافت نشد.";
            exit;
        }
    }
}