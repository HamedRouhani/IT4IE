<?php
namespace App\Software\Statlab\Core;

class Controller
{
    protected $moduleName = 'statlab';
    protected $softwareName = 'StatLab Analyzer';
    protected $currentUserId = null;
    protected $currentUser = null;

    public function __construct()
    {
        $this->currentUserId = $_SESSION['user_id'] ?? null;
        $this->currentUser = $this->getCurrentUser();
    }

    protected function isAuthenticated(): bool
    {
        return $this->currentUserId !== null;
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_message'] = 'برای دسترسی به این بخش لطفاً وارد شوید.';
            header('Location: /login');
            exit;
        }
    }

    protected function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) return null;
        return [
            'id'    => $this->currentUserId,
            'name'  => $_SESSION['user_name'] ?? 'کاربر',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? 'user'
        ];
    }

    protected function authorizeOwnership($recordUserId): bool
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('controller=dashboard');
            return false;
        }
        if (($this->currentUser['role'] ?? '') === 'admin') return true;
        if ((int)$recordUserId !== (int)$this->currentUserId) {
            $_SESSION['error'] = 'شما به این رکورد دسترسی ندارید.';
            $this->redirect('controller=dashboard');
            return false;
        }
        return true;
    }

    /**
     * رندر ویو با layout نرم‌افزار (مشترک با IT4IE)
     */
    protected function view(string $view, array $data = []): void
    {
        $data['moduleName']      = $this->moduleName;
        $data['softwareName']    = $this->softwareName;
        $data['currentUser']     = $this->currentUser;
        $data['isAuthenticated'] = $this->isAuthenticated();
        
        if (!isset($data['title'])) {
            $data['title'] = ($data['pageTitle'] ?? 'StatLab Analyzer') . ' - ' . $this->softwareName;
        }
        
        extract($data);
        ob_start();
        
        $vp = MODULAR_APP_PATH . '/views/' . $view . '.php';
        if (file_exists($vp)) {
            include $vp;
        } else {
            throw new \Exception("StatLab View not found: {$view}");
        }
        $content = ob_get_clean();
        
        // Layout مشترک IT4IE
        $lf = VIEWS_PATH . '/layouts/software.php';
        if (file_exists($lf)) {
            include $lf;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $url): void
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = CURRENT_MODULE_URL . '?' . $url;
        } elseif (strpos($url, '/') === 0 && strpos($url, '/software/') !== 0) {
            $url = CURRENT_MODULE_URL . ltrim($url, '/');
        }
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function flashSuccess(string $msg): void { $_SESSION['message'] = $msg; }
    protected function flashError(string $msg): void   { $_SESSION['error'] = $msg; }

    /**
     * ثبت فعالیت در جدول عمومی software_activity_logs
     */
    protected function logActivity(string $action, ?string $recordType = null, ?int $recordId = null): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->prepare("INSERT INTO software_activity_logs
                (software_slug, user_id, user_name, ip_address, action, record_type, record_id)
                VALUES (?,?,?,?,?,?,?)")
            ->execute([
                'statlab-analyzer',
                $this->currentUserId,
                $this->currentUser['name'] ?? 'مهمان',
                $this->getClientIP(),
                $action,
                $recordType,
                $recordId
            ]);
        } catch (\Exception $e) {
            error_log("StatLab Log Error: " . $e->getMessage());
        }
    }

    protected function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}