<?php
namespace App\Helpers;

use App\Config\Env;

class Utils
{
    /**
     * دریافت URL کامل
     */
    public static function url($path = '')
    {
        $baseUrl = rtrim(Env::get('APP_URL', ''), '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    /**
     * تولید کلید تصادفی
     */
    public static function generateRandomKey($length = 32)
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * هش کردن متن
     */
    public static function hash($text)
    {
        $salt = Env::get('HASH_SALT', '');
        return hash_hmac('sha256', $text, $salt);
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
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * فرمت کردن تاریخ
     */
    public static function formatDate($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($timestamp));
    }

    /**
     * نمایش پیام‌های فلش
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
     * محاسبه درصد پیشرفت
     */
    public static function calculateProgress($completed, $total)
    {
        if ($total == 0) return 0;
        return round(($completed / $total) * 100, 2);
    }

    /**
     * تولید slug
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
     * دریافت کلاس وضعیت
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
     * دریافت کلاس دسته‌بندی تکنیک
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
}