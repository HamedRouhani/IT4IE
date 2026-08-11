<?php

namespace App\Software\Qms\Core;

/**
 * کنترلر پایه ماژول QMS
 * مدیریت احراز هویت، رندر ویو، پیام‌ها و لاگ فعالیت
 */
class Controller
{
    protected $moduleName = 'qms';
    protected $softwareName = 'QMS Analyzer';
    protected $currentUserId = null;
    protected $currentUser = null;
    protected $prefix = 'qms_';
    protected $db = null;

    public function __construct()
    {
        $this->currentUserId = $_SESSION['user_id'] ?? null;
        $this->currentUser = $this->getCurrentUser();
        
        try {
            $this->db = \App\Core\Database::getInstance();
        } catch (\Exception $e) {
            error_log("QMS Controller DB Error: " . $e->getMessage());
        }
    }

    /**
     * آیا کاربر وارد شده است؟
     */
    protected function isAuthenticated()
    {
        return $this->currentUserId !== null;
    }

    /**
     * الزام به ورود
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
     * بررسی نقش ادمین
     */
    protected function requireAdmin()
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'editor'])) {
            $_SESSION['error'] = 'شما مجوز دسترسی به این بخش را ندارید.';
            $this->redirect('dashboard');
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
     * رندر ویو با layout
     */
    protected function view($view, $data = [])
    {
        $data['moduleName'] = $this->moduleName;
        $data['softwareName'] = $this->softwareName;
        $data['currentUser'] = $this->currentUser;
        $data['isAuthenticated'] = $this->isAuthenticated();
        $data['prefix'] = $this->prefix;
        
        if (!isset($data['title'])) {
            $data['title'] = ($data['pageTitle'] ?? 'QMS') . ' - ' . $this->softwareName;
        }

        extract($data);
        ob_start();

        $viewPath = MODULAR_APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("QMS View not found: {$view}");
        }

        $content = ob_get_clean();

        // استفاده از layout مشترک IT4IE
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
     * ریدایرکت
     */
    protected function redirect($url)
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = CURRENT_MODULE_URL . '?controller=' . $url;
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * پیام موفقیت
     */
    protected function flashSuccess($message)
    {
        $_SESSION['message'] = $message;
    }

    /**
     * پیام خطا
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
            $stmt = $this->db->prepare(
                "INSERT INTO software_activity_logs 
                 (software_slug, user_id, user_name, ip_address, action, record_type, record_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                'qms',
                $this->currentUserId,
                $this->currentUser['name'] ?? 'مهمان',
                $this->getClientIP(),
                $action,
                $recordType,
                $recordId
            ]);
        } catch (\Exception $e) {
            error_log("QMS Activity Log Error: " . $e->getMessage());
        }
    }

    /**
     * دریافت IP کاربر
     */
    protected function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * تولید شماره NC
     */
    protected function generateNcNumber()
    {
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT nc_number FROM {$this->prefix}nonconformities 
            WHERE nc_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(["NC-{$year}-%"]);
        $last = $stmt->fetch();
        
        if ($last) {
            $lastNum = (int)substr($last['nc_number'], -3);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        
        return sprintf('NC-%s-%03d', $year, $newNum);
    }

    /**
     * تولید شماره CAR
     */
    protected function generateCarNumber()
    {
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT car_number FROM {$this->prefix}car_forms 
            WHERE car_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(["CAR-{$year}-%"]);
        $last = $stmt->fetch();
        
        if ($last) {
            $lastNum = (int)substr($last['car_number'], -3);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        
        return sprintf('CAR-%s-%03d', $year, $newNum);
    }

    /**
     * تولید شماره گزارش ممیزی
     */
    protected function generateReportNumber()
    {
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT report_number FROM {$this->prefix}audit_reports 
            WHERE report_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(["RPT-{$year}-%"]);
        $last = $stmt->fetch();
        
        if ($last) {
            $lastNum = (int)substr($last['report_number'], -3);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        
        return sprintf('RPT-%s-%03d', $year, $newNum);
    }
}