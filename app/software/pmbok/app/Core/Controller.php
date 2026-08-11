<?php

namespace App\Software\Pmbok\Core;

/**
 * کنترلر پایه ماژول PMBOK - با پشتیبانی احراز هویت
 */
class Controller
{
    protected $moduleName = 'pmbok';
    protected $softwareName = 'PMBOK Analyzer';
    protected $currentUserId = null;
    protected $currentUser = null;

    public function __construct()
    {
        $this->currentUserId = $_SESSION['user_id'] ?? null;
        $this->currentUser = $this->getCurrentUser();
    }

    /**
     * آیا کاربر وارد شده است؟
     */
    protected function isAuthenticated()
    {
        return $this->currentUserId !== null;
    }

    /**
     * الزام به ورود - اگر وارد نشده باشد redirect می‌شود
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_message'] = 'برای دسترسی به این بخش لطفاً وارد شوید.';
            header('Location: /login');
            exit;
        }
    }

    /**
     * دریافت کاربر فعلی
     */
    protected function getCurrentUser()
    {
        if (!$this->isAuthenticated()) return null;
        return [
            'id' => $this->currentUserId,
            'name' => $_SESSION['user_name'] ?? 'کاربر',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    /**
     * بررسی دسترسی به یک رکورد (فقط مالک می‌تواند ببیند/ویرایش کند)
     */
    protected function authorizeOwnership($record, $recordUserId)
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('controller=dashboard');
            return false;
        }
        
        // ادمین دسترسی کامل دارد
        if (($this->currentUser['role'] ?? '') === 'admin') {
            return true;
        }
        
        // فقط مالک رکورد دسترسی دارد
        if ((int)$recordUserId !== (int)$this->currentUserId) {
            $_SESSION['error'] = 'شما به این رکورد دسترسی ندارید.';
            $this->redirect('controller=dashboard');
            return false;
        }
        
        return true;
    }

    /**
     * رندر ویو با layout ترکیبی IT4IE
     */
    protected function view($view, $data = [])
    {
        $data['moduleName'] = $this->moduleName;
        $data['softwareName'] = $this->softwareName;
        $data['currentUser'] = $this->currentUser;
        $data['isAuthenticated'] = $this->isAuthenticated();
        
        if (!isset($data['title'])) {
            $data['title'] = ($data['pageTitle'] ?? 'PMBOK') . ' - ' . $this->softwareName;
        }

        extract($data);
        ob_start();

        $viewPath = MODULAR_APP_PATH . '/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("PMBOK View not found: {$view}");
        }

        $content = ob_get_clean();

        $layoutFile = VIEWS_PATH . '/layouts/software.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * ریدایرکت به مسیر ماژول
     */
    protected function redirect($url)
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = CURRENT_MODULE_URL . '?' . $url;
        } elseif (strpos($url, '/') === 0 && strpos($url, '/software/') !== 0) {
            $url = CURRENT_MODULE_URL . ltrim($url, '/');
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
     * ثبت لاگ فعالیت
     */
    protected function logActivity($action, $recordType = null, $recordId = null)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO software_activity_logs 
                 (software_slug, user_id, user_name, ip_address, action, record_type, record_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                'pmbok-analyzer',
                $this->currentUserId,
                $this->currentUser['name'] ?? 'مهمان',
                $this->getClientIP(),
                $action,
                $recordType,
                $recordId
            ]);
        } catch (\Exception $e) {
            error_log("PMBOK Activity Log Error: " . $e->getMessage());
        }
    }

    protected function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}