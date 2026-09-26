<?php
/**
 * ============================================================
 * Quality Analyzer - Helper Functions
 * ============================================================
 * مسیر: app/software/quality/app/Helpers/Functions.php
 *
 * این فایل کاملاً مستقل است و توابع تبدیل تاریخ شمسی را
 * در خود دارد. هیچ وابستگی به jdf.php یا functions.php
 * پروژه اصلی ندارد.
 * ============================================================
 */

// ============================================
// 🌐 URL و Escape
// ============================================

if (!function_exists('qc_e')) {
    /**
     * Escape HTML برای جلوگیری از XSS
     */
    function qc_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('qc_url')) {
    /**
     * ساخت URL ماژول Quality
     *
     * @param string $controller نام کنترلر
     * @param string $action نام اکشن
     * @param array  $params پارامترهای اضافی
     * @return string
     */
    function qc_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        $base = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/quality-analyzer/';
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

if (!function_exists('qc_flash_set')) {
    function qc_flash_set(string $type, string $message): void
    {
        $_SESSION['qc_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('qc_flash_get')) {
    function qc_flash_get(): ?array
    {
        if (isset($_SESSION['qc_flash'])) {
            $flash = $_SESSION['qc_flash'];
            unset($_SESSION['qc_flash']);
            return $flash;
        }
        return null;
    }
}

// ============================================
// 📅 توابع ریاضی تبدیل تاریخ شمسی ↔ میلادی
// (مستقل از jdf.php و functions.php پروژه اصلی)
// ============================================

if (!function_exists('qc_gregorian_to_jalali')) {
    function qc_gregorian_to_jalali(int $gy, int $gm, int $gd): array
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

if (!function_exists('qc_jalali_to_gregorian')) {
    function qc_jalali_to_gregorian(int $jy, int $jm, int $jd): array
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
            0, 31,
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

if (!function_exists('qc_date')) {
    function qc_date(?string $datetime, string $format = 'Y/m/d'): string
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

        $gy = (int) date('Y', $timestamp);
        $gm = (int) date('m', $timestamp);
        $gd = (int) date('d', $timestamp);

        $H = date('H', $timestamp);
        $i = date('i', $timestamp);
        $s = date('s', $timestamp);

        list($jy, $jm, $jd) = qc_gregorian_to_jalali($gy, $gm, $gd);

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

if (!function_exists('qc_date_time')) {
    function qc_date_time(?string $datetime): string
    {
        return qc_date($datetime, 'Y/m/d H:i');
    }
}

if (!function_exists('qc_today')) {
    function qc_today(string $format = 'Y/m/d'): string
    {
        return qc_date(date('Y-m-d'), $format);
    }
}

// ============================================
// 🔢 اعداد فارسی
// ============================================

if (!function_exists('qc_num')) {
    function qc_num($number): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en, $fa, (string) $number);
    }
}

if (!function_exists('qc_round')) {
    /**
     * گرد کردن هوشمند (برای نمایش اعداد آماری)
     */
    function qc_round($value, int $decimals = 4): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $rounded = round((float) $value, $decimals);
        // حذف صفرهای انتهایی
        $str = rtrim(rtrim(number_format($rounded, $decimals, '.', ''), '0'), '.');
        return $str === '' ? '0' : $str;
    }
}

// ============================================
// 🏷️ برچسب‌های فارسی - انواع نمودار
// ============================================

if (!function_exists('qc_chart_type_label')) {
    function qc_chart_type_label(string $type): string
    {
        $labels = [
            'xbar_r' => 'X̄-R (میانگین و دامنه)',
            'xbar_s' => 'X̄-S (میانگین و انحراف معیار)',
            'i_mr'   => 'I-MR (مقدار فردی و دامنه متحرک)',
            'p'      => 'p (نسبت معیوب)',
            'np'     => 'np (تعداد معیوب)',
            'c'      => 'c (تعداد عیب)',
            'u'      => 'u (عیب در واحد)',
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('qc_chart_short_label')) {
    function qc_chart_short_label(string $type): string
    {
        $labels = [
            'xbar_r' => 'X̄-R',
            'xbar_s' => 'X̄-S',
            'i_mr'   => 'I-MR',
            'p'      => 'p',
            'np'     => 'np',
            'c'      => 'c',
            'u'      => 'u',
        ];
        return $labels[$type] ?? $type;
    }
}

// ============================================
// 🏷️ برچسب‌های فارسی - وضعیت‌ها
// ============================================

if (!function_exists('qc_status_label')) {
    function qc_status_label(string $status): string
    {
        $labels = [
            'active'    => 'فعال',
            'archived'  => 'بایگانی‌شده',
            'draft'     => 'پیش‌نویس',
            'completed' => 'تکمیل شده',
            'in_control'    => 'تحت کنترل',
            'out_of_control'=> 'خارج از کنترل',
            'acceptable'    => 'قابل قبول',
            'marginal'      => 'مرزی',
            'unacceptable'  => 'غیرقابل قبول',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('qc_status_class')) {
    function qc_status_class(string $status): string
    {
        $classes = [
            'active'         => 'qc-status-active',
            'archived'       => 'qc-status-inactive',
            'draft'          => 'qc-status-inactive',
            'completed'      => 'qc-status-active',
            'in_control'     => 'qc-status-active',
            'out_of_control' => 'qc-status-danger',
            'acceptable'     => 'qc-status-active',
            'marginal'       => 'qc-status-warning',
            'unacceptable'   => 'qc-status-danger',
        ];
        return $classes[$status] ?? 'qc-status-info';
    }
}

if (!function_exists('qc_capability_verdict')) {
    /**
     * ارزیابی قابلیت فرآیند بر اساس Cpk
     * مرجع: AIAG SPC Manual
     */
    function qc_capability_verdict(float $cpk): array
    {
        if ($cpk >= 1.67) {
            return ['label' => 'عالی', 'class' => 'qc-status-active', 'color' => '#059669'];
        }
        if ($cpk >= 1.33) {
            return ['label' => 'قابل قبول', 'class' => 'qc-status-active', 'color' => '#10B981'];
        }
        if ($cpk >= 1.00) {
            return ['label' => 'مرزی', 'class' => 'qc-status-warning', 'color' => '#F59E0B'];
        }
        if ($cpk >= 0.67) {
            return ['label' => 'ضعیف', 'class' => 'qc-status-warning', 'color' => '#EA580C'];
        }
        return ['label' => 'غیرقابل قبول', 'class' => 'qc-status-danger', 'color' => '#DC2626'];
    }
}

// ============================================
// 🛠️ ابزارهای عمومی
// ============================================

if (!function_exists('qc_active_system_id')) {
    function qc_active_system_id(): ?int
    {
        return !empty($_SESSION['qc_active_system']) ? (int) $_SESSION['qc_active_system'] : null;
    }
}

if (!function_exists('qc_truncate')) {
    function qc_truncate(string $text, int $length = 80, string $suffix = '...'): string
    {
        $text = trim(strip_tags($text));

        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') <= $length) {
                return $text;
            }
            return rtrim(mb_substr($text, 0, $length, 'UTF-8')) . $suffix;
        }

        if (strlen($text) <= $length) {
            return $text;
        }
        return rtrim(substr($text, 0, $length)) . $suffix;
    }
}

if (!function_exists('qc_format_number')) {
    /**
     * فرمت اعداد با جداکننده هزارگان + فارسی
     */
    function qc_format_number($number, int $decimals = 0): string
    {
        if ($number === null || $number === '') {
            return '—';
        }
        return qc_num(number_format((float) $number, $decimals));
    }
}

if (!function_exists('qc_random_color')) {
    /**
     * رنگ تصادفی از پالت Emerald (برای نمودارها)
     */
    function qc_random_color(int $index = 0): string
    {
        $palette = [
            '#059669', '#10B981', '#34D399', '#6EE7B7',
            '#047857', '#065F46', '#14B8A6', '#0D9488',
            '#0891B2', '#0E7490',
        ];
        return $palette[$index % count($palette)];
    }
}