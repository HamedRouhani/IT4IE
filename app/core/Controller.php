<?php

namespace App\Core;

use App\Models\Category;

abstract class Controller
{
    protected $data = [];
    protected $layout = 'main';

    /**
     * رندر ویو با layout اصلی IT4IE
     */
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

    /**
     * رندر برای صفحات احراز هویت
     */
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

        $layoutFile = VIEWS_PATH . '/layouts/auth.php';

        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * رندر برای پنل ادمین
     */
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

    /**
     * رندر برای صفحه پروفایل (بدون سایدبار و فوتر)
     */
    public function renderProfile($view, $data = [])
    {
        // لود تنظیمات سایت برای هدر
        $settingModel = new \App\Models\Setting();
        $settings = $settingModel->getAll();
        
        $this->data = array_merge($this->data, $data, ['settings' => $settings]);
        extract($this->data);
        
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $view");
        }
        $content = ob_get_clean();
        
        // استفاده از layout پروفایل (بدون سایدبار و فوتر)
        $layoutFile = VIEWS_PATH . '/layouts/profile.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * 🆕 رندر محتوای نرم‌افزار با layout ترکیبی
     * هدر و فوتر IT4IE + سایدبار نرم‌افزار + محتوای ماژول
     */
    public function renderSoftware($view, $data = [], $moduleName = 'babok')
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getTree();

        $this->data = array_merge($this->data, $data, [
            'categories' => $categories,
            'hideSidebar' => true,
            'softwareMode' => true,
            'moduleName' => $moduleName,
            'softwareName' => $data['softwareName'] ?? 'BABOK Analyzer'
        ]);

        extract($this->data);

        ob_start();

        // مسیر ویو در ماژول
        $viewFile = APP_PATH . '/software/' . $moduleName . '/views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            // fallback به مسیر قدیمی
            $viewFile = VIEWS_PATH . '/software/' . $moduleName . '/' . $view . '.php';
        }

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("Software view not found: $view (looked in: $viewFile)");
        }

        $content = ob_get_clean();

        // استفاده از layout ترکیبی
        $layoutFile = VIEWS_PATH . '/layouts/software.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * ارسال پاسخ JSON
     */
    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * ریدایرکت
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * بررسی احراز هویت
     */
    protected function requireAuth($redirectUrl = '/login')
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_message'] = 'برای انجام این عملیات لطفاً وارد شوید.';
            $this->redirect($redirectUrl);
        }
    }

    /**
     * بررسی دسترسی ادمین
     */
    protected function requireAdmin($redirectUrl = '/')
    {
        $this->requireAuth($redirectUrl);
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'editor'])) {
            $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید.';
            $this->redirect($redirectUrl);
        }
    }

    /**
     * دریافت کاربر فعلی
     */
    protected function currentUser()
    {
        if (!isset($_SESSION['user_id'])) return null;
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'کاربر',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    /**
     * تنظیم پیام موفقیت
     */
    protected function setSuccess($message)
    {
        $_SESSION['message'] = $message;
    }

    /**
     * تنظیم پیام خطا
     */
    protected function setError($message)
    {
        $_SESSION['error'] = $message;
    }
}