<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Software;
use App\Models\Category;
use App\Models\Setting;
use App\Models\SoftwareActivityLog;

class SoftwareController extends Controller
{
    /**
     * لیست نرم‌افزارها
     */
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

        $this->render('software/index', [
            'title' => 'نرم‌افزارهای تخصصی - IT4IE',
            'softwareList' => $softwareList,
            'categories' => $categories,
            'settings' => $settings,
            'totalSoftware' => $totalSoftware,
            'totalDownloads' => $totalDownloads
        ]);
    }

    /**
     * جزئیات یک نرم‌افزار
     */
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
     * 🆕 اجرای یک نرم‌افزار ماژولار
     */
    public function run($slug)
    {
        $softwareModel = new Software();
        $software = $softwareModel->getBySlug($slug);

        if (!$software) {
            $_SESSION['error'] = 'نرم‌افزار مورد نظر یافت نشد.';
            $this->redirect('/software');
            return;
        }

        if (!$software['is_active']) {
            $_SESSION['error'] = 'این نرم‌افزار در حال حاضر غیرفعال است.';
            $this->redirect('/software');
            return;
        }

        // ذخیره اطلاعات نرم‌افزار در session
        $_SESSION['current_software'] = [
            'id' => $software['id'],
            'slug' => $software['slug'],
            'name' => $software['name']
        ];

        // ✅ ثبت لاگ ورود به نرم‌افزار (اصلاح شده)
        $this->logActivity($software['slug'], 'enter', 'software', $software['id']);

        // ریدایرکت به entry point ماژول
        $entryPoint = '/software/' . $slug . '/';
        $this->redirect($entryPoint);
    }

    /**
     * 🆕 خروج از نرم‌افزار
     */
    public function exitSoftware()
    {
        if (isset($_SESSION['current_software'])) {
            // ✅ ثبت لاگ خروج (اصلاح شده)
            $this->logActivity(
                $_SESSION['current_software']['slug'],
                'exit',
                'software',
                $_SESSION['current_software']['id']
            );
            unset($_SESSION['current_software']);
        }
        $this->redirect('/software');
    }

    /**
     * ✅ ثبت لاگ فعالیت در نرم‌افزار
     * 
     * امضای متد log() در SoftwareActivityLog:
     * log($softwareSlug, $action, $recordType = null, $recordId = null, $oldValue = null, $newValue = null)
     */
    private function logActivity($softwareSlug, $action, $recordType = null, $recordId = null, $oldValue = null, $newValue = null)
    {
        try {
            $logModel = new SoftwareActivityLog();
            
            // ✅ ارسال آرگومان‌ها به صورت جداگانه (نه آرایه)
            $logModel->log(
                $softwareSlug,
                $action,
                $recordType,
                $recordId,
                $oldValue,
                $newValue
            );
        } catch (\Exception $e) {
            error_log("Failed to log software activity: " . $e->getMessage());
        }
    }
}