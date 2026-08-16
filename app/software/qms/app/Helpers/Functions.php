<?php
/**
 * توابع کمکی ماژول QMS
 */

if (!function_exists('qms_url')) {
    function qms_url($path = '')
    {
        $baseUrl = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/qms/';
        return $baseUrl . $path;
    }
}

if (!function_exists('qms_e')) {
    function qms_e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('qms_severity_color')) {
    function qms_severity_color($severity)
    {
        $colors = [
            'minor' => '#3B82F6',
            'major' => '#F59E0B',
            'critical' => '#EF4444',
            'low' => '#10B981',
            'medium' => '#F59E0B',
            'high' => '#EF4444'
        ];
        return $colors[$severity] ?? '#6B7280';
    }
}

if (!function_exists('qms_status_label')) {
    function qms_status_label($status)
    {
        $labels = [
            'draft' => 'پیش‌نویس',
            'scheduled' => 'زمان‌بندی شده',
            'in_progress' => 'در حال انجام',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'open' => 'باز',
            'under_review' => 'در بررسی',
            'car_issued' => 'CAR صادر شد',
            'closed' => 'بسته شده',
            'rejected' => 'رد شده',
            'conformity' => 'انطباق',
            'observation' => 'مشاهده',
            'minor_nc' => 'عدم انطباق جزئی',
            'major_nc' => 'عدم انطباق عمده',
            'ofI' => 'فرصت بهبود',
            'todo' => 'انجام نشده',
            'done' => 'انجام شده',
            'blocked' => 'مسدود شده',
            'delayed' => 'تأخیر دارد',
            'verified' => 'تأیید شده',
            'implemented' => 'پیاده‌سازی شده',
            'approved' => 'تأیید شده',
            'submitted' => 'ارسال شده',
            'finalized' => 'نهایی شده',
            'distributed' => 'توزیع شده',
            'archived' => 'بایگانی شده'
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('qms_clause_type_label')) {
    function qms_clause_type_label($type)
    {
        $labels = [
            'requirement' => 'الزامی',
            'guidance' => 'راهنما',
            'both' => 'هر دو'
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('qms_finding_type_label')) {
    function qms_finding_type_label($type)
    {
        $labels = [
            'conformity' => 'انطباق',
            'observation' => 'مشاهده',
            'minor_nc' => 'عدم انطباق جزئی',
            'major_nc' => 'عدم انطباق عمده',
            'ofI' => 'فرصت بهبود'
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('qms_audit_type_label')) {
    function qms_audit_type_label($type)
    {
        $labels = [
            'internal' => 'داخلی',
            'external' => 'خارجی',
            'surveillance' => 'نظارتی',
            'recertification' => 'تمدید گواهینامه',
            'special' => 'ویژه'
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('qms_date_fa')) {
    function qms_date_fa($date, $format = 'Y/m/d')
    {
        if (empty($date)) return '-';
        try {
            $dt = new DateTime($date);
            return $dt->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('qms_datetime_fa')) {
    function qms_datetime_fa($datetime)
    {
        if (empty($datetime)) return '-';
        try {
            $dt = new DateTime($datetime);
            return $dt->format('Y/m/d H:i');
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('qms_days_until')) {
    function qms_days_until($date)
    {
        if (empty($date)) return null;
        $today = new DateTime();
        $target = new DateTime($date);
        $diff = $today->diff($target);
        return $diff->days * ($diff->invert ? -1 : 1);
    }
}

if (!function_exists('qms_progress_color')) {
    function qms_progress_color($percentage)
    {
        if ($percentage >= 80) return '#10B981';
        if ($percentage >= 50) return '#F59E0B';
        if ($percentage >= 25) return '#F97316';
        return '#EF4444';
    }
}

/**
 * تبدیل اعداد فارسی/عربی به انگلیسی
 * برای استفاده در تاریخ‌ها و اعداد دیتابیس
 */
if (!function_exists('toEnglishDigits')) {
    function toEnglishDigits($str)
    {
        if (empty($str)) return $str;
        
        $persianNumbers = ['', '۱', '۲', '', '۴', '۵', '', '۷', '۸', ''];
        $arabicNumbers  = ['٠', '١', '', '٣', '٤', '', '٦', '٧', '', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        $str = str_replace($persianNumbers, $englishNumbers, $str);
        $str = str_replace($arabicNumbers, $englishNumbers, $str);
        
        return $str;
    }
}

/**
 * تبدیل تاریخ به فرمت استاندارد MySQL (Y-m-d)
 * اعداد فارسی را به انگلیسی تبدیل می‌کند
 */
if (!function_exists('toMysqlDate')) {
    function toMysqlDate($date)
    {
        if (empty($date)) return null;
        
        // تبدیل اعداد فارسی به انگلیسی
        $date = toEnglishDigits($date);
        
        // تبدیل جداکننده‌ها (مثل / به -)
        $date = str_replace('/', '-', $date);
        
        // اعتبارسنجی فرمت
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        return null;
    }
}

/* ---------------------------------------------------------------
 * توابع تاریخ شمسی (Jalali) - افزوده‌شده برای ماژول QMS
 * --------------------------------------------------------------- */

if (!function_exists('gregorian_to_jalali')) {
    function gregorian_to_jalali($gy, $gm, $gd)
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4))
              - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400))
              + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }
        return [$jy, $jm, $jd];
    }
}

if (!function_exists('fa_digits')) {
    /** تبدیل ارقام انگلیسی به فارسی */
    function fa_digits($value)
    {
        return str_replace(
            ['0','1','2','3','4','5','6','7','8','9'],
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
            (string)$value
        );
    }
}

if (!function_exists('fa_current_jyear')) {
    /** سال شمسی جاری */
    function fa_current_jyear()
    {
        $j = gregorian_to_jalali((int)date('Y'), (int)date('n'), (int)date('j'));
        return $j[0];
    }
}

if (!function_exists('fa_jdate')) {
    /**
     * نمایش شمسی یک تاریخ ذخیره‌شده
     * اگر سال شمسی باشد فقط فارسی‌سازی می‌کند، وگرنه از میلادی تبدیل می‌کند
     */
    function fa_jdate($date)
    {
        if (empty($date)) return '-';
        $normalized = toEnglishDigits((string)$date);
        $normalized = str_replace('/', '-', trim($normalized));
        $parts = explode('-', substr($normalized, 0, 10));
        if (count($parts) !== 3) return fa_digits($date);

        [$y, $m, $d] = array_map('intval', $parts);

        // تاریخ شمسی ذخیره‌شده → فقط فارسی‌سازی
        if ($y >= 1400 && $y <= 1499) {
            return fa_digits(sprintf('%04d/%02d/%02d', $y, $m, $d));
        }
        // تاریخ میلادی → تبدیل به شمسی
        $j = gregorian_to_jalali($y, $m, $d);
        return fa_digits(sprintf('%04d/%02d/%02d', $j[0], $j[1], $j[2]));
    }
}