<?php
/**
 * ============================================================
 * PdM Analyzer - Helper Functions
 * ============================================================
 * مسیر: app/software/pdm/app/Helpers/Functions.php
 * 
 * این فایل کاملاً مستقل است و توابع تبدیل تاریخ شمسی را
 * در خود دارد. هیچ وابستگی به jdf.php یا functions.php 
 * پروژه اصلی ندارد.
 * ============================================================
 */

// ============================================
// 🌐 URL و Escape
// ============================================

if (!function_exists('pdm_e')) {
    function pdm_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pdm_url')) {
    function pdm_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        $base = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/pdm-analyzer/';
        $url  = $base . '?controller=' . urlencode($controller) . '&action=' . urlencode($action);

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }
}

// ============================================
// 💬 Flash Messages
// ============================================

if (!function_exists('pdm_flash_set')) {
    function pdm_flash_set(string $type, string $message): void
    {
        $_SESSION['pdm_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('pdm_flash_get')) {
    function pdm_flash_get(): ?array
    {
        if (isset($_SESSION['pdm_flash'])) {
            $flash = $_SESSION['pdm_flash'];
            unset($_SESSION['pdm_flash']);
            return $flash;
        }
        return null;
    }
}

// ============================================
// 📅 توابع ریاضی تبدیل تاریخ شمسی ↔ میلادی
// (مستقل از jdf.php و functions.php پروژه اصلی)
// ============================================

if (!function_exists('pdm_gregorian_to_jalali')) {
    /**
     * تبدیل تاریخ میلادی به شمسی (الگوریتم ریاضی)
     * 
     * @param int $gy سال میلادی
     * @param int $gm ماه میلادی (1-12)
     * @param int $gd روز میلادی (1-31)
     * @return array [سال شمسی, ماه شمسی, روز شمسی]
     */
    function pdm_gregorian_to_jalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        
        $days = 355666 + (365 * $gy) 
              + ((int) (($gy2 + 3) / 4)) 
              - ((int) (($gy2 + 99) / 100)) 
              + ((int) (($gy2 + 399) / 400)) 
              + $gd 
              + $g_d_m[$gm - 1];
        
        $jy = -1595 + (33 * ((int) ($days / 12053)));
        $days %= 12053;
        
        $jy += 4 * ((int) ($days / 1461));
        $days %= 1461;
        
        if ($days > 365) {
            $jy += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        
        if ($days < 186) {
            $jm = 1 + (int) ($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int) (($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }
        
        return [$jy, $jm, $jd];
    }
}

if (!function_exists('pdm_jalali_to_gregorian')) {
    /**
     * تبدیل تاریخ شمسی به میلادی (الگوریتم ریاضی)
     * 
     * @param int $jy سال شمسی
     * @param int $jm ماه شمسی (1-12)
     * @param int $jd روز شمسی (1-31)
     * @return array [سال میلادی, ماه میلادی, روز میلادی]
     */
    function pdm_jalali_to_gregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        
        $days = -355668 + (365 * $jy) 
              + (((int) ($jy / 33)) * 8) 
              + ((int) ((($jy % 33) + 3) / 4)) 
              + $jd 
              + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
        
        $gy = 400 * ((int) ($days / 146097));
        $days %= 146097;
        
        if ($days > 36524) {
            $gy += 100 * ((int) (--$days / 36524));
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }
        
        $gy += 4 * ((int) ($days / 1461));
        $days %= 1461;
        
        if ($days > 365) {
            $gy += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        
        $gd = $days + 1;
        
        $sal_a = [
            0, 
            31, 
            (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28,
            31, 30, 31, 30, 31, 31, 30, 31, 30, 31
        ];
        
        $gm = 0;
        while ($gm < 13 && $gd > $sal_a[$gm]) {
            $gd -= $sal_a[$gm];
            $gm++;
        }
        
        return [$gy, $gm, $gd];
    }
}

// ============================================
// 📅 توابع تاریخ شمسی (نمایش)
// ============================================

if (!function_exists('pdm_date')) {
    /**
     * تبدیل تاریخ میلادی به شمسی برای نمایش
     * 
     * @param string|null $datetime تاریخ به فرمت MySQL (Y-m-d H:i:s)
     * @param string      $format فرمت خروجی
     * @return string
     */
    function pdm_date(?string $datetime, string $format = 'Y/m/d'): string
    {
        if (empty($datetime) 
            || $datetime === '0000-00-00' 
            || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '-';
        }

        // استخراج اجزای تاریخ میلادی
        $gy = (int) date('Y', $timestamp);
        $gm = (int) date('m', $timestamp);
        $gd = (int) date('d', $timestamp);

        $H = date('H', $timestamp);
        $i = date('i', $timestamp);
        $s = date('s', $timestamp);

        // تبدیل میلادی → شمسی با تابع داخلی
        $jDate = pdm_gregorian_to_jalali($gy, $gm, $gd);
        list($jy, $jm, $jd) = $jDate;

        // جایگزینی توکن‌های فرمت
        // ⚠️ ترتیب مهم: ابتدا توکن‌های حساس (که ممکن است در هم تداخل کنند)
        $replacements = [
            'Y' => (string) $jy,
            'y' => substr((string) $jy, 2),
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'H' => $H,
            'i' => $i,
            's' => $s,
        ];

        $output = $format;
        foreach ($replacements as $token => $value) {
            $output = str_replace($token, $value, $output);
        }

        return $output;
    }
}

if (!function_exists('pdm_date_time')) {
    function pdm_date_time(?string $datetime): string
    {
        return pdm_date($datetime, 'Y/m/d H:i');
    }
}

if (!function_exists('pdm_date_full')) {
    function pdm_date_full(?string $datetime): string
    {
        return pdm_date($datetime, 'Y/m/d H:i:s');
    }
}

if (!function_exists('pdm_today')) {
    function pdm_today(string $format = 'Y/m/d'): string
    {
        return pdm_date(date('Y-m-d'), $format);
    }
}

// ============================================
// 🏷️ برچسب‌های فارسی
// ============================================

if (!function_exists('pdm_criticality_label')) {
    function pdm_criticality_label(string $criticality): string
    {
        $labels = [
            'low'      => 'پایین',
            'medium'   => 'متوسط',
            'high'     => 'بالا',
            'critical' => 'بحرانی',
        ];
        return $labels[$criticality] ?? $criticality;
    }
}

if (!function_exists('pdm_status_label')) {
    function pdm_status_label(string $status): string
    {
        $labels = [
            'active'      => 'فعال',
            'inactive'    => 'غیرفعال',
            'maintenance' => 'در تعمیر',
            'retired'     => 'بازنشسته',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('pdm_wo_status_label')) {
    function pdm_wo_status_label(string $status): string
    {
        $labels = [
            'open'        => 'باز',
            'in_progress' => 'در حال انجام',
            'completed'   => 'تکمیل شده',
            'cancelled'   => 'لغو شده',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('pdm_wo_priority_label')) {
    function pdm_wo_priority_label(string $priority): string
    {
        $labels = [
            'low'    => 'پایین',
            'normal' => 'عادی',
            'high'   => 'بالا',
            'urgent' => 'فوری',
        ];
        return $labels[$priority] ?? $priority;
    }
}

// ============================================
// 🎨 CSS کلاس‌های بحرانیت و وضعیت
// ============================================

if (!function_exists('pdm_criticality_class')) {
    function pdm_criticality_class(string $criticality): string
    {
        $classes = [
            'low'      => 'pdm-criticality-low',
            'medium'   => 'pdm-criticality-medium',
            'high'     => 'pdm-criticality-high',
            'critical' => 'pdm-criticality-critical',
        ];
        return $classes[$criticality] ?? 'pdm-criticality-medium';
    }
}

if (!function_exists('pdm_wo_status_class')) {
    function pdm_wo_status_class(string $status): string
    {
        $classes = [
            'open'        => 'pdm-wo-status-open',
            'in_progress' => 'pdm-wo-status-in_progress',
            'completed'   => 'pdm-wo-status-completed',
            'cancelled'   => 'pdm-wo-status-cancelled',
        ];
        return $classes[$status] ?? 'pdm-wo-status-open';
    }
}

if (!function_exists('pdm_wo_priority_label')) {
    /**
     * برچسب فارسی اولویت دستورکار
     */
    function pdm_wo_priority_label(string $priority): string
    {
        $labels = [
            'low'    => 'پایین',
            'normal' => 'عادی',
            'high'   => 'بالا',
            'urgent' => 'فوری',
        ];
        return $labels[$priority] ?? $priority;
    }
}

if (!function_exists('pdm_wo_priority_class')) {
    /**
     * کلاس CSS اولویت دستورکار
     */
    function pdm_wo_priority_class(string $priority): string
    {
        $classes = [
            'low'    => 'pdm-criticality-low',
            'normal' => 'pdm-criticality-medium',
            'high'   => 'pdm-criticality-high',
            'urgent' => 'pdm-criticality-critical',
        ];
        return $classes[$priority] ?? 'pdm-criticality-medium';
    }
}

if (!function_exists('pdm_status_class')) {
    function pdm_status_class(string $status): string
    {
        $classes = [
            'active'      => 'pdm-status-active',
            'inactive'    => 'pdm-status-inactive',
            'maintenance' => 'pdm-status-maintenance',
            'retired'     => 'pdm-status-retired',
        ];
        return $classes[$status] ?? 'pdm-status-inactive';
    }
}

// ============================================
// 🔢 اعداد فارسی
// ============================================

if (!function_exists('pdm_num')) {
    function pdm_num($number): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en, $fa, (string) $number);
    }
}

// ============================================
// 🛠️ ابزارهای عمومی
// ============================================

if (!function_exists('pdm_active_system_id')) {
    function pdm_active_system_id(): ?int
    {
        return !empty($_SESSION['pdm_active_system']) ? (int) $_SESSION['pdm_active_system'] : null;
    }
}

if (!function_exists('pdm_truncate')) {
    function pdm_truncate(string $text, int $length = 80, string $suffix = '...'): string
    {
        $text = trim(strip_tags($text));
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
    }
}

if (!function_exists('pdm_wo_priority_label')) {
    /**
     * برچسب فارسی اولویت دستورکار
     */
    function pdm_wo_priority_label(string $priority): string
    {
        $labels = [
            'low'    => 'پایین',
            'normal' => 'عادی',
            'high'   => 'بالا',
            'urgent' => 'فوری',
        ];
        return $labels[$priority] ?? $priority;
    }
}

if (!function_exists('pdm_wo_priority_class')) {
    /**
     * کلاس CSS اولویت دستورکار
     */
    function pdm_wo_priority_class(string $priority): string
    {
        $classes = [
            'low'    => 'pdm-criticality-low',
            'normal' => 'pdm-criticality-medium',
            'high'   => 'pdm-criticality-high',
            'urgent' => 'pdm-criticality-critical',
        ];
        return $classes[$priority] ?? 'pdm-criticality-medium';
    }
}