<?php

declare(strict_types=1);

if (!function_exists('fl_e')) {
    function fl_e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('fl_url')) {
    function fl_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        return '/software/factory-layout-analyzer/?' . http_build_query(array_merge([
            'controller' => $controller,
            'action' => $action,
        ], $params));
    }
}

if (!function_exists('fl_flash_set')) {
    function fl_flash_set(string $type, string $message): void
    {
        $_SESSION['fl_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('fl_flash_get')) {
    function fl_flash_get(): ?array
    {
        $flash = $_SESSION['fl_flash'] ?? null;
        unset($_SESSION['fl_flash']);
        return is_array($flash) ? $flash : null;
    }
}

if (!function_exists('fl_latin_digits')) {
    function fl_latin_digits(string $value): string
    {
        return strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            '٫'=>'.','٬'=>'',
        ]);
    }
}
