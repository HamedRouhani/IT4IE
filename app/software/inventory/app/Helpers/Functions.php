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
