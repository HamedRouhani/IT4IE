<?php
/**
 * توابع کمکی اختصاصی ماژول StatLab
 */

if (!function_exists('stat_url')) {
    /**
     * ساخت URL داخلی ماژول
     * مثال: stat_url('controller=descriptive&action=create')
     */
    function stat_url(string $query = ''): string
    {
        return CURRENT_MODULE_URL . ($query !== '' ? '?' . $query : '');
    }
}

if (!function_exists('stat_e')) {
    /**
     * Escape ایمن برای نمایش در HTML
     */
    function stat_e($value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('stat_getStatusLabel')) {
    /**
     * تبدیل کد وضعیت به متن فارسی
     */
    function stat_getStatusLabel(string $status): string
    {
        $labels = [
            'draft'       => 'پیش‌نویس',
            'in_progress' => 'در حال تحلیل',
            'completed'   => 'تکمیل شده',
            'failed'      => 'ناموفق',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('stat_format_number')) {
    /**
     * فرمت اعداد اعشاری برای نمایش
     */
    function stat_format_number($value, int $decimals = 4): string
    {
        if ($value === null || $value === '') return '-';
        return number_format((float)$value, $decimals);
    }
}