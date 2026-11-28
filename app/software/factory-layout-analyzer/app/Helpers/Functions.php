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
