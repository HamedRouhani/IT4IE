<?php
/**
 * ============================================================
 * CSRF HELPERS (سراسری)
 * ============================================================
 * ماژول‌هایی که از کلاس App\Core\Controller ارث نمی‌برند (یا صفحه‌شان خارج از
 * کنترلر رندر می‌شود) به این توابع نیاز دارند. کلاس Controller همین منطق را
 * برای کنترلرهای اصلی دارد.
 *
 * مسیر: app/helpers/csrf.php
 */

if (!function_exists('csrf_token')) {
    /**
     * توکن CSRF جاری نشست را برمی‌گرداند و در صورت نبودن می‌سازد.
     */
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * فیلد مخفی آمادهٔ درج در فرم.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' .
            htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_request_is_ajax')) {
    function csrf_request_is_ajax(): bool
    {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return is_string($requestedWith) && strtolower($requestedWith) === 'xmlhttprequest';
    }
}

if (!function_exists('csrf_request_is_same_origin')) {
    /**
     * آیا Origin/Referer درخواست با دامنهٔ جاری یکی است؟ اگر هیچ‌کدام ارسال نشده
     * باشد true برمی‌گرداند (مرورگرها برای POST همیشه می‌فرستند).
     */
    function csrf_request_is_same_origin(): bool
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
}

if (!function_exists('csrf_token_is_valid')) {
    /**
     * بررسی توکن CSRF؛ توکن از POST یا هدر X-CSRF-Token خوانده می‌شود.
     */
    function csrf_token_is_valid(): bool
    {
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        $requestToken = $_POST['_csrf_token'] ?? '';

        if ((!is_string($requestToken) || $requestToken === '') && !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $requestToken = (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return is_string($sessionToken)
            && is_string($requestToken)
            && $sessionToken !== ''
            && trim($requestToken) !== ''
            && hash_equals($sessionToken, trim($requestToken));
    }
}

if (!function_exists('csrf_reject')) {
    /**
     * رد درخواست نامعتبر: پاسخ ۴۱۹ برای AJAX، بازگشت به صفحهٔ قبلی برای فرم.
     */
    function csrf_reject(string $message = 'درخواست منقضی یا نامعتبر است؛ صفحه را تازه کنید و دوباره تلاش کنید.'): void
    {
        http_response_code(419);

        if (csrf_request_is_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['error'] = $message;

        $target = '/';
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');

        if ($referer !== '') {
            $parts = parse_url($referer);
            $path = is_array($parts) ? (string) ($parts['path'] ?? '') : '';
            $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
            $expected = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
            $path = str_replace(["\r", "\n"], '', $path);

            if ($path !== '' && $path[0] === '/' && $host !== '' && hash_equals($expected, $host)) {
                $target = $path;
            }
        }

        header('Location: ' . $target, true, 303);
        exit;
    }
}

if (!function_exists('it4ie_require_csrf')) {
    /**
     * گارد CSRF نقاط ورود ماژول‌ها؛ فقط درخواست‌های تغییردهندهٔ داده را بررسی
     * می‌کند. درخواست AJAX هم‌مبدأ بدون توکن مجاز است تا کلاینت‌های موجود نشکنند.
     */
    function it4ie_require_csrf(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if (csrf_token_is_valid()) {
            return;
        }

        // مرورگر خودش اعلام می‌کند درخواست از همین مبدأ است و این هدر جعل‌شدنی نیست.
        if (strtolower((string) ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '')) === 'same-origin') {
            return;
        }

        // درخواست‌های برنامه‌ای (XHR یا JSON) از سایت دیگر با فرم ساده قابل ساخت نیستند
        // و مرورگر برای آن‌ها preflight انجام می‌دهد.
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $isProgrammatic = csrf_request_is_ajax() || strpos($contentType, 'application/json') !== false;

        if ($isProgrammatic && csrf_request_is_same_origin()) {
            error_log('CSRF: same-origin programmatic request without token - ' . ($_SERVER['REQUEST_URI'] ?? ''));
            return;
        }

        csrf_reject();
    }
}

if (!function_exists('it4ie_inject_csrf_fields')) {
    /**
     * تزریق خودکار فیلد مخفی CSRF در همهٔ فرم‌های POST یک صفحه هنگام رندر.
     * برای ماژول‌هایی که فرم‌هایشان هنوز توکن ندارند.
     */
    function it4ie_inject_csrf_fields(string $html): string
    {
        $field = csrf_field();

        $result = preg_replace_callback('/<form\b[^>]*>/i', static function (array $match) use ($field): string {
            $tag = $match[0];

            if (!preg_match('/\bmethod\s*=\s*["\']?post/i', $tag)) {
                return $tag;
            }

            if (stripos($tag, '_csrf_token') !== false) {
                return $tag;
            }

            return $tag . $field;
        }, $html);

        return $result ?? $html;
    }
}
