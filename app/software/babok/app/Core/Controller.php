<?php

namespace App\Software\Babok\Core;

/**
 * کنترلر پایه ماژول BABOK
 * رندر ویوها از طریق layout ترکیبی IT4IE انجام می‌شود
 */
class Controller
{
    protected $moduleName = 'babok';
    protected $softwareName = 'BABOK Analyzer';

    /**
     * رندر ویو با layout ترکیبی IT4IE
     */
    protected function view($view, $data = [])
    {
        // تنظیمات پیش‌فرض
        $data['moduleName'] = $this->moduleName;
        $data['softwareName'] = $this->softwareName;
        
        if (!isset($data['title'])) {
            $data['title'] = $this->softwareName . ' - IT4IE';
        }

        extract($data);
        ob_start();

        // مسیر ویو در ماژول
        $viewPath = MODULAR_APP_PATH . '/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            // fallback: جستجو در app/views
            $viewPath = MODULAR_APP_PATH . '/app/views/' . $view . '.php';
        }

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("BABOK View not found: {$view} (path: {$viewPath})");
        }

        $content = ob_get_clean();

        // استفاده از layout ترکیبی IT4IE
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
    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * ریدایرکت به مسیر ماژول
     */
    protected function redirect($url)
    {
        // اگر URL نسبی بود و با ?route= شروع نمی‌شد، به مسیر ماژول اضافه کن
        if (strpos($url, 'http') !== 0 && strpos($url, '?route=') === false && strpos($url, '/') !== 0) {
            $url = CURRENT_MODULE_URL . '?route=' . $url;
        } elseif (strpos($url, '/') === 0 && strpos($url, '/software/') !== 0) {
            // تبدیل مسیرهای قدیمی مثل /projects به مسیر ماژول
            $url = CURRENT_MODULE_URL . '?route=' . ltrim($url, '/');
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * تنظیم پیام فلش موفقیت
     */
    protected function flashSuccess($message)
    {
        $_SESSION['message'] = $message;
    }

    /**
     * تنظیم پیام فلش خطا
     */
    protected function flashError($message)
    {
        $_SESSION['error'] = $message;
    }

    /**
     * بررسی احراز هویت
     */
    protected function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_message'] = 'برای انجام این عملیات لطفاً وارد شوید.';
            header('Location: /login');
            exit;
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
     * ثبت لاگ فعالیت در نرم‌افزار
     */
    protected function logActivity($action, $recordType = null, $recordId = null, $oldValue = null, $newValue = null)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO software_activity_logs 
                 (software_slug, user_id, user_name, ip_address, action, record_type, record_id, old_value, new_value) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                'babok-analyzer',
                $_SESSION['user_id'] ?? null,
                $_SESSION['user_name'] ?? 'مهمان',
                $this->getClientIP(),
                $action,
                $recordType,
                $recordId,
                $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
                $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null
            ]);
        } catch (\Exception $e) {
            // ثبت خطا در لاگ PHP برای عیب‌یابی
            error_log("BABOK Activity Log Error: " . $e->getMessage());
        }
    }

    /**
     * دریافت IP کاربر
     */
    protected function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}