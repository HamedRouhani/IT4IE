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

function mcdm_getRankingExplanation($methodCode)
{
    $explanations = [
        'AHP' => 'در پیاده‌سازی فعلی، AHP وزن معیارها را از مقایسه‌های زوجی به دست می‌دهد و رتبه‌بندی گزینه‌ها با SAW انجام می‌شود؛ این زنجیره را به‌صورت AHP–SAW گزارش کنید.',
        'SAW' => 'SAW پس از نرمال‌سازی معیارهای سودی و هزینه‌ای، امتیازهای وزنی را جمع می‌کند. امتیازها به گزینه‌های واردشده وابسته‌اند و با تغییر مجموعه گزینه‌ها ممکن است عوض شوند.',
        'TOPSIS' => 'TOPSIS گزینه‌ها را بر پایه نزدیکی به راه‌حل ایده‌آل مثبت و دوری از راه‌حل ایده‌آل منفی رتبه‌بندی می‌کند؛ امتیاز، احتمال موفقیت گزینه نیست.',
        'VIKOR' => 'VIKOR راه‌حل سازشی را با توجه به مطلوبیت گروهی و بیشینه پشیمانی رتبه‌بندی می‌کند؛ پیش از توصیه، شرایط پذیرش راه‌حل سازشی و پایداری رتبه را بررسی کنید.',
    ];

    return $explanations[strtoupper((string) $methodCode)]
        ?? 'رتبه به روش انتخاب‌شده، وزن‌ها، جهت معیارها، داده‌ها و گزینه‌های واردشده وابسته است؛ فرض‌ها را همراه نتیجه گزارش کنید.';
}

function mcdm_showDate($date, $format = 'Y/m/d')
{
    return \App\Helpers\DateHelper::toJalali($date, $format);
}

function mcdm_truncateText($text, $length = 100, $suffix = '...')
{
    $text = strip_tags((string)$text);
    if (mb_strlen($text, 'UTF-8') <= $length) return $text;
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}
