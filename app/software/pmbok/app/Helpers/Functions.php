<?php
/**
 * توابع کمکی ماژول PMBOK
 */

/**
 * تولید URL برای ماژول PMBOK
 */
function pmbok_url($path = '')
{
    $baseUrl = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/pmbok-analyzer/';
    
    if (strpos($path, '?') === 0) {
        return $baseUrl . 'index.php' . $path;
    }
    
    if (empty($path)) {
        return $baseUrl;
    }
    
    return $baseUrl . '?' . ltrim($path, '?');
}

/**
 * بررسی فعال بودن منو
 */
function pmbok_isActiveMenu($menu)
{
    $current = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'dashboard';
    return strtolower($current) === strtolower($menu) ? 'active' : '';
}

/**
 * فرار از HTML
 */
function pmbok_e($string)
{
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * برچسب فاز پروژه
 */
function pmbok_getPhaseLabel($phase)
{
    $labels = [
        'initiation' => 'آغاز',
        'planning' => 'برنامه‌ریزی',
        'execution' => 'اجرا',
        'monitoring_controlling' => 'نظارت و کنترل',
        'closure' => 'اختتام'
    ];
    return $labels[$phase] ?? $phase;
}

/**
 * رنگ فاز پروژه
 */
function pmbok_getPhaseColor($phase)
{
    $colors = [
        'initiation' => 'info',
        'planning' => 'primary',
        'execution' => 'warning',
        'monitoring_controlling' => 'secondary',
        'closure' => 'success'
    ];
    return $colors[$phase] ?? 'secondary';
}

/**
 * برچسب متودولوژی
 */
function pmbok_getMethodologyLabel($methodology)
{
    $labels = [
        'waterfall' => 'آبشاری',
        'agile' => 'چابک',
        'hybrid' => 'ترکیبی',
        'adaptive' => 'تطبیقی'
    ];
    return $labels[$methodology] ?? $methodology;
}

/**
 * برچسب وضعیت تسک
 */
function pmbok_getTaskStatusLabel($status)
{
    $labels = [
        'not_started' => 'شروع نشده',
        'in_progress' => 'در حال انجام',
        'completed' => 'تکمیل شده',
        'deferred' => 'به تعویق افتاده'
    ];
    return $labels[$status] ?? $status;
}

/**
 * برچسب وضعیت ریسک
 */
function pmbok_getRiskStatusLabel($status)
{
    $labels = [
        'identified' => 'شناسایی شده',
        'analyzed' => 'تحلیل شده',
        'planned' => 'برنامه‌ریزی شده',
        'implemented' => 'اجرا شده',
        'closed' => 'بسته شده'
    ];
    return $labels[$status] ?? $status;
}

/**
 * برچسب احتمال ریسک
 */
function pmbok_getProbabilityLabel($probability)
{
    $labels = [
        'very_low' => 'بسیار کم',
        'low' => 'کم',
        'medium' => 'متوسط',
        'high' => 'بالا',
        'very_high' => 'بسیار بالا'
    ];
    return $labels[$probability] ?? $probability;
}

/**
 * برچسب تاثیر ریسک
 */
function pmbok_getImpactLabel($impact)
{
    $labels = [
        'very_low' => 'بسیار کم',
        'low' => 'کم',
        'medium' => 'متوسط',
        'high' => 'بالا',
        'very_high' => 'بسیار بالا'
    ];
    return $labels[$impact] ?? $impact;
}

/**
 * رنگ احتمال/تاثیر ریسک
 */
function pmbok_getRiskColor($level)
{
    $colors = [
        'very_low' => '#10B981',
        'low' => '#3B82F6',
        'medium' => '#F59E0B',
        'high' => '#EF4444',
        'very_high' => '#DC2626'
    ];
    return $colors[$level] ?? '#6B7280';
}

/**
 * نمایش تاریخ
 */
function pmbok_showDate($date, $format = 'Y/m/d')
{
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * محاسبه درصد پیشرفت
 */
function pmbok_getProgressPercentage($completed, $total)
{
    if ($total == 0) return 0;
    return round(($completed / $total) * 100);
}

/**
 * کوتاه کردن متن
 */
function pmbok_truncateText($text, $length = 100, $suffix = '...')
{
    $text = strip_tags($text);
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}