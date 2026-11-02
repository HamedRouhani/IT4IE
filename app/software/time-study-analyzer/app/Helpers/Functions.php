<?php

if (!function_exists('ts_e')) {
    function ts_e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('ts_url')) {
    function ts_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        return '/software/time-study-analyzer/?' . http_build_query(array_merge(['controller' => $controller, 'action' => $action], $params));
    }
}

if (!function_exists('ts_num')) {
    function ts_num($value, int $decimals = 2): string
    {
        if ($value === null || !is_numeric($value) || !is_finite((float) $value)) return '—';
        return number_format((float) $value, $decimals, '.', ',');
    }
}

if (!function_exists('ts_date')) {
    function ts_date(?string $value, string $format = 'Y/m/d'): string
    {
        if (!$value) return '—';
        if (class_exists('App\\Helpers\\DateHelper')) return \App\Helpers\DateHelper::toJalali($value, $format);
        return $value;
    }
}

if (!function_exists('ts_flash_set')) {
    function ts_flash_set(string $type, string $message): void { $_SESSION['ts_flash'] = ['type' => $type, 'message' => $message]; }
}

if (!function_exists('ts_flash_get')) {
    function ts_flash_get(): ?array
    {
        $flash = $_SESSION['ts_flash'] ?? null;
        unset($_SESSION['ts_flash']);
        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('ts_status')) {
    function ts_status(string $status): string
    {
        return ['draft' => 'پیش‌نویس', 'active' => 'در حال اجرا', 'completed' => 'نهایی‌شده', 'archived' => 'بایگانی'][ $status ] ?? $status;
    }
}

if (!function_exists('ts_category_type')) {
    function ts_category_type(string $type): string
    {
        return ['productive' => 'مولد', 'machine' => 'کار ماشین', 'personal' => 'نیاز شخصی', 'delay' => 'توقف / انتظار', 'other' => 'سایر'][$type] ?? $type;
    }
}
