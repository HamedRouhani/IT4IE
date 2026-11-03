<?php

namespace App\Core;

use App\Models\Category;

abstract class Controller
{
    protected $data = [];
    protected $layout = 'main';

    /**
     * دریافت توکن CSRF
     */
    protected function csrfToken()
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * تولید فیلد مخفی CSRF برای فرم‌ها
     */
    protected function csrfField()
    {
        return '<input type="hidden" name="_csrf_token" value="' .
            htmlspecialchars($this->csrfToken(), ENT_QUOTES, 'UTF-8') .
            '">';
    }

    /**
     * بررسی «خام» توکن CSRF؛ فقط true/false برمی‌گرداند و درخواست را متوقف نمی‌کند.
     */
    protected function csrfTokenIsValid()
    {
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        $requestToken = $_POST['_csrf_token'] ?? '';

        // درخواست‌های AJAX می‌توانند توکن را در هدر بفرستند
        if ((!is_string($requestToken) || $requestToken === '') && !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $requestToken = trim((string) $_SERVER['HTTP_X_CSRF_TOKEN']);
        }

        if (
            !is_string($sessionToken) ||
            !is_string($requestToken) ||
            $sessionToken === '' ||
            $requestToken === '' ||
            !hash_equals($sessionToken, $requestToken)
        ) {
            return false;
        }

        return true;
    }

    /**
     * بررسی توکن CSRF با توقف درخواست در صورت نامعتبر بودن.
     *
     * این متد شکل «اجباری» بررسی است: اگر توکن معتبر نباشد، پاسخ ۴۱۹ (برای AJAX)
     * یا بازگشت به صفحهٔ قبلی انجام می‌شود و اجرای کد ادامه پیدا نمی‌کند. به همین
     * دلیل فراخوانی صرفِ آن در ابتدای عملیات نوشتن برای محافظت کافی است.
     * برای بررسی بدون توقف از csrfTokenIsValid() استفاده کنید.
     *
     * @param string|null $message پیام خطای سفارشی برای نمایش به کاربر
     */
    protected function verifyCsrf($message = null)
    {
        if ($this->csrfTokenIsValid()) {
            return true;
        }

        // مرورگر خودش اعلام می‌کند درخواست از همین مبدأ است و این هدر جعل‌شدنی نیست
        if (strtolower((string) ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '')) === 'same-origin') {
            return true;
        }

        // درخواست AJAX هم‌مبدأ: مرورگر نمی‌تواند چنین درخواستی را از سایت دیگر بسازد
        if (static::isAjaxRequest() && static::requestIsSameOrigin()) {
            error_log('CSRF: same-origin AJAX request without token - ' . ($_SERVER['REQUEST_URI'] ?? ''));
            return true;
        }

        $this->rejectInvalidRequest(
            $message !== null && $message !== ''
                ? (string) $message
                : 'درخواست منقضی یا نامعتبر است؛ صفحه را تازه کنید و دوباره تلاش کنید.',
            419
        );

        return false;
    }

    /**
     * رد درخواست نامعتبر با پاسخ JSON (برای AJAX) یا بازگشت به صفحهٔ قبلی.
     */
    protected function rejectInvalidRequest($message, $status = 400)
    {
        http_response_code($status);

        if (static::isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'ok' => false,
                'error' => $message
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['error'] = $message;
        }

        header('Location: ' . $this->safeReturnUrl(), true, 303);
        exit;
    }

    /**
     * آدرس بازگشت امن؛ فقط مسیرهایی از همین دامنه پذیرفته می‌شوند.
     */
    protected function safeReturnUrl()
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        if ($referer !== '') {
            $parts = parse_url($referer);
            $path = is_array($parts) ? (string) ($parts['path'] ?? '') : '';
            $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
            $expected = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));

            $path = str_replace(["\r", "\n"], '', $path);

            if ($path !== '' && $path[0] === '/' && ($path === '/' || strpos($path, '//') !== 0) && $host !== '' && hash_equals($expected, $host)) {
                return $path;
            }
        }

        if (defined('CURRENT_MODULE_URL')) {
            return CURRENT_MODULE_URL;
        }

        return '/';
    }

    /**
     * آیا درخواست جاری از نوع AJAX است؟
     */
    protected static function isAjaxRequest()
    {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return is_string($requestedWith) && strtolower($requestedWith) === 'xmlhttprequest';
    }

    /**
     * آیا مبدأ درخواست (Origin یا Referer) با همین دامنه یکسان است؟
     *
     * اگر هیچ‌کدام از این دو هدر ارسال نشده باشد، درخواست «هم‌مبدأ» در نظر گرفته می‌شود
     * تا کلاینت‌های داخلی و ابزارهای بدون هدر مبدأ مسدود نشوند؛ مرورگرها برای
     * درخواست‌های تغییردهندهٔ داده همیشه هدر مبدأ را می‌فرستند.
     */
    protected static function requestIsSameOrigin()
    {
        $expected = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
        if ($expected === '') {
            return true;
        }

        $source = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
        if ($source === '') {
            $source = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        }
        if ($source === '') {
            return true;
        }

        $parts = parse_url($source);
        if (!is_array($parts) || empty($parts['host'])) {
            return false;
        }

        $origin = strtolower((string) $parts['host']);
        if (!empty($parts['port'])) {
            $origin .= ':' . (int) $parts['port'];
        }

        return hash_equals($expected, $origin);
    }

    /**
     * گارد سراسری: هر درخواست تغییردهندهٔ داده (POST/PUT/PATCH/DELETE)
     * باید از همین دامنه آمده باشد. پیش از ورود به روتر صدا زده می‌شود.
     */
    public static function guardStateChangingRequest()
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        if (static::requestIsSameOrigin()) {
            return true;
        }

        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
        error_log('Blocked cross-origin ' . $method . ' request to ' . ($_SERVER['REQUEST_URI'] ?? '') . ' from ' . $origin);

        http_response_code(403);

        if (static::isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'درخواست از مبدأ نامعتبر رد شد.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">'
            . '<title>دسترسی غیرمجاز</title></head>'
            . '<body style="font-family:Tahoma;direction:rtl;text-align:center;padding:60px;">'
            . '<h1>۴۰۳ - دسترسی غیرمجاز</h1>'
            . '<p>درخواست شما از مبدأ نامعتبر ارسال شده است.</p>'
            . '<p><a href="/">بازگشت به صفحه اصلی</a></p></body></html>';
        exit;
    }

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