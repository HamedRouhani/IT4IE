<?php
namespace App\Software\Or\Core;

class Controller
{
    protected $moduleName = 'or';
    protected $softwareName = 'OR Analyzer';
    protected $currentUserId = null;
    protected $currentUser = null;

    public function __construct()
    {
        $this->currentUserId = $_SESSION['user_id'] ?? null;
        $this->currentUser = $this->getCurrentUser();
    }

    protected function isAuthenticated() { return $this->currentUserId !== null; }

    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['auth_message'] = 'برای دسترسی به این بخش لطفاً وارد شوید.';
            header('Location: /login'); exit;
        }
    }

    protected function getCurrentUser()
    {
        if (!$this->isAuthenticated()) return null;
        return [
            'id'    => $this->currentUserId,
            'name'  => $_SESSION['user_name'] ?? 'کاربر',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? 'user'
        ];
    }

    protected function authorizeOwnership($recordUserId)
    {
        if (!$this->isAuthenticated()) { $this->redirect('controller=dashboard'); return false; }
        if (($this->currentUser['role'] ?? '') === 'admin') return true;
        if ((int)$recordUserId !== (int)$this->currentUserId) {
            $_SESSION['error'] = 'شما به این رکورد دسترسی ندارید.';
            $this->redirect('controller=dashboard'); return false;
        }
        return true;
    }

    protected function view($view, $data = [])
    {
        $data['moduleName']      = $this->moduleName;
        $data['softwareName']    = $this->softwareName;
        $data['currentUser']     = $this->currentUser;
        $data['isAuthenticated'] = $this->isAuthenticated();
        if (!isset($data['title']))
            $data['title'] = ($data['pageTitle'] ?? 'OR Analyzer') . ' - ' . $this->softwareName;
        extract($data);
        ob_start();
        $vp = MODULAR_APP_PATH . '/views/' . $view . '.php';
        if (file_exists($vp)) include $vp;
        else throw new \Exception("OR View not found: {$view}");
        $content = ob_get_clean();
        $content = it4ie_inject_csrf_fields($content);
        $csrfTokenJson = json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $csrfHelpers = '<script>window.OR_CSRF_TOKEN=' . $csrfTokenJson . ';'
            . 'window.orSubmitPost=function(url){var f=document.createElement("form");f.method="POST";f.action=url;'
            . 'var i=document.createElement("input");i.type="hidden";i.name="_csrf_token";i.value=window.OR_CSRF_TOKEN;'
            . 'f.appendChild(i);document.body.appendChild(f);f.submit();};'
            . 'window.orPostFetch=function(url,options){options=options||{};var h=new Headers(options.headers||{});'
            . 'h.set("X-CSRF-Token",window.OR_CSRF_TOKEN);h.set("X-Requested-With","XMLHttpRequest");'
            . 'options.method="POST";options.headers=h;return fetch(url,options);};</script>';
        $content = $csrfHelpers . $content;
        $lf = VIEWS_PATH . '/layouts/software.php';
        if (file_exists($lf)) include $lf; else echo $content;
    }

    protected function redirect($url)
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0)
            $url = CURRENT_MODULE_URL . '?' . $url;
        elseif (strpos($url, '/') === 0 && strpos($url, '/software/') !== 0)
            $url = CURRENT_MODULE_URL . ltrim($url, '/');
        header("Location: {$url}"); exit;
    }

    protected function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE); exit;
    }

    protected function flashSuccess($msg) { $_SESSION['message'] = $msg; }
    protected function flashError($msg)   { $_SESSION['error'] = $msg; }

    protected function logActivity($action, $recordType = null, $recordId = null)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->prepare("INSERT INTO software_activity_logs
                (software_slug, user_id, user_name, ip_address, action, record_type, record_id)
                VALUES (?,?,?,?,?,?,?)")
            ->execute([
                'or-analyzer', $this->currentUserId,
                $this->currentUser['name'] ?? 'مهمان',
                $this->getClientIP(), $action, $recordType, $recordId
            ]);
        } catch (\Exception $e) { error_log("OR Log Error: ".$e->getMessage()); }
    }

    protected function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}