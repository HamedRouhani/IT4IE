<?php

if (!function_exists('inv_e')) {
    function inv_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('inv_url')) {
    function inv_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        $query = array_merge(['controller' => $controller, 'action' => $action], $params);
        return (defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/inventory-analyzer/') . '?' . http_build_query($query);
    }
}

if (!function_exists('inv_flash_set')) {
    function inv_flash_set(string $type, string $message): void
    {
        $_SESSION['inv_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('inv_flash_get')) {
    function inv_flash_get(): ?array
    {
        $flash = $_SESSION['inv_flash'] ?? null;
        unset($_SESSION['inv_flash']);
        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('inv_num')) {
    function inv_num($value, int $precision = 2): string
    {
        if ($value === null || !is_numeric($value) || !is_finite((float) $value)) return '—';
        return number_format((float) $value, $precision, '.', ',');
    }
}

if (!function_exists('inv_money')) {
    /**
     * نمایش مبلغ با جداکنندهٔ هزارگان؛ اعشار فقط وقتی مقدار گرد نیست.
     */
    function inv_money($value): string
    {
        if ($value === null || !is_numeric($value) || !is_finite((float) $value)) return '—';
        $value = (float) $value;
        $decimals = abs($value - round($value)) < 0.005 ? 0 : 2;

        return number_format($value, $decimals, '.', ',');
    }
}

if (!function_exists('inv_pct')) {
    /**
     * تبدیل کسر (0.96) به درصد نمایشی (96.00٪).
     */
    function inv_pct($fraction, int $precision = 2): string
    {
        if ($fraction === null || !is_numeric($fraction) || !is_finite((float) $fraction)) return '—';

        return number_format((float) $fraction * 100.0, $precision, '.', ',') . '٪';
    }
}

if (!function_exists('inv_ratio')) {
    function inv_ratio($value, int $precision = 2): string
    {
        return inv_num($value, $precision);
    }
}

if (!function_exists('inv_date')) {
    function inv_date(?string $value): string
    {
        if (!$value) return '—';
        if (class_exists('App\\Helpers\\DateHelper')) {
            return \App\Helpers\DateHelper::toJalali($value, 'Y/m/d H:i');
        }
        return $value;
    }
}
