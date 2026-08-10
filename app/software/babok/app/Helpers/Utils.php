<?php

namespace App\Software\Babok\Helpers;

/**
 * توابع کمکی ماژول BABOK
 */
class Utils
{
    /**
     * دریافت URL کامل ماژول
     */
    public static function url($route = '', $params = [])
    {
        $url = CURRENT_MODULE_URL . '?route=' . $route;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }

    /**
     * بررسی آیا درخواست AJAX است
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * دریافت IP کاربر
     */
    public static function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * فرمت کردن تاریخ
     */
    public static function formatDate($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($timestamp));
    }

    /**
     * محاسبه درصد پیشرفت
     */
    public static function calculateProgress($completed, $total)
    {
        if ($total == 0) return 0;
        return round(($completed / $total) * 100, 2);
    }

    /**
     * دریافت وضعیت به صورت فارسی
     */
    public static function statusLabel($status)
    {
        $labels = [
            'not_started' => 'انجام نشده',
            'in_progress' => 'در حال انجام',
            'completed' => 'تکمیل شده',
            'deferred' => 'به تعویق افتاده'
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * دریافت کلاس CSS وضعیت
     */
    public static function statusClass($status)
    {
        $classes = [
            'not_started' => 'secondary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'deferred' => 'danger'
        ];
        return $classes[$status] ?? 'secondary';
    }

    /**
     * دریافت کلاس CSS دسته‌بندی تکنیک
     */
    public static function categoryClass($category)
    {
        $classes = [
            'collaborative' => 'info',
            'research' => 'primary',
            'experimental' => 'warning',
            'management' => 'secondary',
            'strategic' => 'danger',
            'modeling' => 'success'
        ];
        return $classes[$category] ?? 'secondary';
    }

    /**
     * دریافت فاز به صورت فارسی
     */
    public static function phaseLabel($phase)
    {
        $labels = [
            'initiation' => 'شروع',
            'planning' => 'برنامه‌ریزی',
            'analysis' => 'تحلیل',
            'design' => 'طراحی',
            'implementation' => 'پیاده‌سازی',
            'evaluation' => 'ارزیابی'
        ];
        return $labels[$phase] ?? $phase;
    }

    /**
     * دریافت متدولوژی به صورت فارسی
     */
    public static function methodologyLabel($methodology)
    {
        $labels = [
            'waterfall' => 'آبشاری',
            'agile' => 'چابک',
            'hybrid' => 'ترکیبی'
        ];
        return $labels[$methodology] ?? $methodology;
    }

    /**
     * پاکسازی ورودی
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * نمایش پیام فلش
     */
    public static function flash($key, $message = null)
    {
        if ($message === null) {
            $message = $_SESSION['flash_' . $key] ?? null;
            unset($_SESSION['flash_' . $key]);
            return $message;
        }
        $_SESSION['flash_' . $key] = $message;
    }

    /**
     * بررسی وجود کلید در آرایه
     */
    public static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * تبدیل متن به slug
     */
    public static function slug($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return $text;
    }

    /**
     * دریافت زمان به صورت فارسی
     */
    public static function timeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'لحظاتی پیش';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' دقیقه پیش';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' ساعت پیش';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' روز پیش';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }

        /**
     * دریافت برچسب فارسی دسته‌بندی تکنیک
     */
    public static function categoryLabel($category)
    {
        $labels = [
            'collaborative' => 'همکاری',
            'research' => 'تحقیقاتی',
            'experimental' => 'آزمایشی',
            'management' => 'مدیریتی',
            'strategic' => 'استراتژیک',
            'modeling' => 'مدل‌سازی'
        ];
        return $labels[$category] ?? $category;
    }
}