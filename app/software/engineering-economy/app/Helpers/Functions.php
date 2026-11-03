<?php

/**
 * توابع کمکی ماژول اقتصاد مهندسی.
 * همهٔ توابع با function_exists محافظت شده‌اند تا بارگذاری مکرر خطا ندهد.
 */

if (!function_exists('ee_e')) {
    function ee_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ee_url')) {
    function ee_url(string $controller = 'project', string $action = 'index', array $params = []): string
    {
        return '/software/engineering-economy/?' . http_build_query(array_merge([
            'controller' => $controller,
            'action' => $action,
        ], $params));
    }
}

if (!function_exists('ee_money')) {
    /**
     * نمایش مبلغ با جداکنندهٔ هزارگان و حداکثر دو رقم اعشار.
     */
    function ee_money($value, ?string $currency = null): string
    {
        if ($value === null || !is_numeric($value)) return '—';
        $formatted = number_format((float) $value, abs((float) $value - round((float) $value)) < 0.005 ? 0 : 2, '.', ',');

        return $currency ? $formatted . ' ' . $currency : $formatted;
    }
}

if (!function_exists('ee_num')) {
    function ee_num($value, int $decimals = 2): string
    {
        if ($value === null || !is_numeric($value) || !is_finite((float) $value)) return '—';

        return number_format((float) $value, $decimals, '.', ',');
    }
}

if (!function_exists('ee_pct')) {
    /**
     * درصد را از کسر (0.1234) به رشته تبدیل می‌کند (۱۲٫۳۴٪).
     */
    function ee_pct($fraction, int $decimals = 2): string
    {
        if ($fraction === null || !is_numeric($fraction)) return '—';

        return number_format((float) $fraction * 100.0, $decimals, '.', ',') . '٪';
    }
}

if (!function_exists('ee_ratio')) {
    function ee_ratio($value, int $decimals = 2): string
    {
        if ($value === null || !is_numeric($value)) return '—';

        return number_format((float) $value, $decimals, '.', ',');
    }
}

if (!function_exists('ee_flash_set')) {
    function ee_flash_set(string $type, string $message): void
    {
        $_SESSION['ee_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('ee_flash_get')) {
    function ee_flash_get(): ?array
    {
        $flash = $_SESSION['ee_flash'] ?? null;
        unset($_SESSION['ee_flash']);

        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('ee_status_label')) {
    function ee_status_label(string $status): string
    {
        return [
            'draft' => 'پیش‌نویس',
            'active' => 'فعال',
            'completed' => 'نهایی‌شده',
            'archived' => 'بایگانی',
        ][$status] ?? $status;
    }
}

if (!function_exists('ee_kind_label')) {
    function ee_kind_label(string $kind): string
    {
        return [
            'investment' => 'سرمایه‌گذاری',
            'replacement' => 'جایگزینی دارایی',
            'make_buy' => 'ساخت یا خرید',
            'public' => 'پروژهٔ عمومی',
            'cost_only' => 'فقط هزینه',
        ][$kind] ?? $kind;
    }
}

if (!function_exists('ee_method_label')) {
    function ee_method_label(string $method): string
    {
        return [
            'none' => 'بدون استهلاک',
            'straight' => 'خط مستقیم',
            'declining' => 'نزولی',
            'soyd' => 'مجموع سنوات',
            'uop' => 'واحد تولید',
        ][$method] ?? $method;
    }
}

if (!function_exists('ee_date')) {
    function ee_date(?string $value, string $format = 'Y/m/d'): string
    {
        if (!$value) return '—';
        if (class_exists('App\\Helpers\\DateHelper')) {
            return \App\Helpers\DateHelper::toJalali($value, $format);
        }

        return $value;
    }
}
