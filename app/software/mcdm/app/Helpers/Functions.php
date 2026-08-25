<?php
/**
 * توابع کمکی ماژول MCDM
 */

function mcdm_url($path = '')
{
    $baseUrl = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/mcdm-analyzer/';
    if (strpos($path, '?') === 0) {
        return $baseUrl . 'index.php' . $path;
    }
    if (empty($path)) {
        return $baseUrl;
    }
    return $baseUrl . '?' . ltrim($path, '?');
}

function mcdm_isActiveMenu($menu)
{
    $current = $_GET['controller'] ?? 'dashboard';
    return strtolower($current) === strtolower($menu) ? 'active' : '';
}

function mcdm_e($string)
{
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function mcdm_getPhaseLabel($phase)
{
    $labels = [
        'definition'     => 'تعریف مسئله',
        'criteria_design'=> 'طراحی معیارها',
        'evaluation'     => 'ارزیابی',
        'analysis'       => 'تحلیل',
        'decision'       => 'تصمیم‌گیری'
    ];
    return $labels[$phase] ?? $phase;
}

function mcdm_getPhaseColor($phase)
{
    $colors = [
        'definition'     => 'info',
        'criteria_design'=> 'primary',
        'evaluation'     => 'warning',
        'analysis'       => 'secondary',
        'decision'       => 'success'
    ];
    return $colors[$phase] ?? 'secondary';
}

function mcdm_getCriterionTypeLabel($type)
{
    $labels = [
        'benefit'      => 'سودی (مثبت)',
        'cost'         => 'هزینه‌ای (منفی)',
        'qualitative'  => 'کیفی',
        'quantitative' => 'کمّی'
    ];
    return $labels[$type] ?? $type;
}

function mcdm_getMethodCategoryLabel($category)
{
    $labels = [
        'compensatory'     => 'جبرانی',
        'non_compensatory' => 'غیرجبرانی',
        'pairwise'         => 'مقایسه زوجی',
        'probabilistic'    => 'احتمالاتی',
        'hybrid'           => 'ترکیبی'
    ];
    return $labels[$category] ?? $category;
}

function mcdm_showDate($date, $format = 'Y/m/d')
{
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

function mcdm_truncateText($text, $length = 100, $suffix = '...')
{
    $text = strip_tags((string)$text);
    if (mb_strlen($text, 'UTF-8') <= $length) return $text;
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}