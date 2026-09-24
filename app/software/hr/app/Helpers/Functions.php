<?php
/**
 * ============================================================
 * HR Analyzer - Helper Functions
 * ============================================================
 * مسیر: app/software/hr/app/Helpers/Functions.php
 * 
 * این فایل کاملاً مستقل است و توابع تبدیل تاریخ شمسی را
 * در خود دارد. هیچ وابستگی به jdf.php یا functions.php 
 * پروژه اصلی ندارد.
 * ============================================================
 */

// ============================================
// 🌐 URL و Escape
// ============================================

if (!function_exists('hr_e')) {
    /**
     * Escape HTML برای جلوگیری از XSS
     */
    function hr_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('hr_url')) {
    /**
     * ساخت URL ماژول HR
     *
     * @param string $controller نام کنترلر
     * @param string $action نام اکشن
     * @param array  $params پارامترهای اضافی
     * @return string
     */
    function hr_url(string $controller = 'dashboard', string $action = 'index', array $params = []): string
    {
        $base = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/hr-analyzer/';
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

if (!function_exists('hr_flash_set')) {
    /**
     * ثبت پیام Flash
     */
    function hr_flash_set(string $type, string $message): void
    {
        $_SESSION['hr_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('hr_flash_get')) {
    /**
     * دریافت و پاک کردن پیام Flash
     */
    function hr_flash_get(): ?array
    {
        if (isset($_SESSION['hr_flash'])) {
            $flash = $_SESSION['hr_flash'];
            unset($_SESSION['hr_flash']);
            return $flash;
        }
        return null;
    }
}

// ============================================
// 📅 توابع ریاضی تبدیل تاریخ شمسی ↔ میلادی
// (مستقل از jdf.php و functions.php پروژه اصلی)
// ============================================

if (!function_exists('hr_gregorian_to_jalali')) {
    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    function hr_gregorian_to_jalali(int $gy, int $gm, int $gd): array
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

if (!function_exists('hr_jalali_to_gregorian')) {
    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    function hr_jalali_to_gregorian(int $jy, int $jm, int $jd): array
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

if (!function_exists('hr_date')) {
    /**
     * تبدیل تاریخ میلادی به شمسی برای نمایش
     */
    function hr_date(?string $datetime, string $format = 'Y/m/d'): string
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

        $jDate = hr_gregorian_to_jalali($gy, $gm, $gd);
        list($jy, $jm, $jd) = $jDate;

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

if (!function_exists('hr_date_time')) {
    function hr_date_time(?string $datetime): string
    {
        return hr_date($datetime, 'Y/m/d H:i');
    }
}

if (!function_exists('hr_today')) {
    function hr_today(string $format = 'Y/m/d'): string
    {
        return hr_date(date('Y-m-d'), $format);
    }
}

// ============================================
// 🏷️ برچسب‌های فارسی
// ============================================

if (!function_exists('hr_gender_label')) {
    function hr_gender_label(string $gender): string
    {
        $labels = [
            'male'   => 'مرد',
            'female' => 'زن',
            'other'  => 'سایر',
        ];
        return $labels[$gender] ?? $gender;
    }
}

if (!function_exists('hr_marital_label')) {
    function hr_marital_label(string $status): string
    {
        $labels = [
            'single'   => 'مجرد',
            'married'  => 'متأهل',
            'divorced' => 'مطلقه',
            'widowed'  => 'بیوه',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_employment_status_label')) {
    function hr_employment_status_label(string $status): string
    {
        $labels = [
            'active'     => 'شاغل',
            'on_leave'   => 'مرخصی',
            'suspended'  => 'تعلیق',
            'terminated' => 'خاتمه همکاری',
            'retired'    => 'بازنشسته',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_contract_type_label')) {
    function hr_contract_type_label(string $type): string
    {
        $labels = [
            'permanent'   => 'دائمی',
            'fixed_term'  => 'مدت معین',
            'project'     => 'پروژه‌ای',
            'intern'      => 'کارآموز',
            'consultant'  => 'مشاور',
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('hr_education_level_label')) {
    function hr_education_level_label(string $level): string
    {
        $labels = [
            'below_diploma' => 'زیر دیپلم',
            'diploma'       => 'دیپلم',
            'associate'     => 'کاردانی',
            'bachelor'      => 'کارشناسی',
            'master'        => 'کارشناسی ارشد',
            'phd'           => 'دکتری',
            'postdoc'       => 'پسا دکتری',
        ];
        return $labels[$level] ?? $level;
    }
}

if (!function_exists('hr_employment_type_label')) {
    function hr_employment_type_label(string $type): string
    {
        $labels = [
            'full_time' => 'تمام‌وقت',
            'part_time' => 'پاره‌وقت',
            'remote'    => 'دورکاری',
            'hybrid'    => 'ترکیبی',
        ];
        return $labels[$type] ?? $type;
    }
}

if (!function_exists('hr_company_size_label')) {
    function hr_company_size_label(string $size): string
    {
        $labels = [
            'micro'      => 'خرد (۱-۹)',
            'small'      => 'کوچک (۱۰-۴۹)',
            'medium'     => 'متوسط (۵۰-۲۴۹)',
            'large'      => 'بزرگ (۲۵۰-۹۹۹)',
            'enterprise' => 'سازمانی (۱۰۰۰+)',
        ];
        return $labels[$size] ?? $size;
    }
}

if (!function_exists('hr_recruitment_status_label')) {
    function hr_recruitment_status_label(string $status): string
    {
        $labels = [
            'draft'       => 'پیش‌نویس',
            'open'        => 'باز',
            'in_progress' => 'در جریان',
            'closed'      => 'بسته شده',
            'cancelled'   => 'لغو شده',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_candidate_status_label')) {
    function hr_candidate_status_label(string $status): string
    {
        $labels = [
            'new'             => 'جدید',
            'screening'       => 'بررسی اولیه',
            'interview'       => 'مصاحبه',
            'technical_test'  => 'آزمون فنی',
            'offer'           => 'پیشنهاد',
            'hired'           => 'استخدام شده',
            'rejected'        => 'رد شده',
            'withdrawn'       => 'انصراف',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_review_rating_label')) {
    function hr_review_rating_label(string $rating): string
    {
        $labels = [
            'excellent'         => 'عالی',
            'very_good'         => 'خیلی خوب',
            'good'              => 'خوب',
            'satisfactory'      => 'قابل قبول',
            'needs_improvement' => 'نیاز به بهبود',
            'unsatisfactory'    => 'غیرقابل قبول',
        ];
        return $labels[$rating] ?? $rating;
    }
}

if (!function_exists('hr_goal_status_label')) {
    function hr_goal_status_label(string $status): string
    {
        $labels = [
            'draft'     => 'پیش‌نویس',
            'active'    => 'فعال',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'on_hold'   => 'متوقف',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_training_status_label')) {
    function hr_training_status_label(string $status): string
    {
        $labels = [
            'draft'       => 'پیش‌نویس',
            'planned'     => 'برنامه‌ریزی شده',
            'open'        => 'ثبت‌نام باز',
            'in_progress' => 'در حال اجرا',
            'completed'   => 'تکمیل شده',
            'cancelled'   => 'لغو شده',
        ];
        return $labels[$status] ?? $status;
    }
}

if (!function_exists('hr_priority_label')) {
    function hr_priority_label(string $priority): string
    {
        $labels = [
            'low'      => 'پایین',
            'normal'   => 'عادی',
            'high'     => 'بالا',
            'urgent'   => 'فوری',
            'critical' => 'بحرانی',
        ];
        return $labels[$priority] ?? $priority;
    }
}

// ============================================
// 🎨 CSS کلاس‌های وضعیت
// ============================================

if (!function_exists('hr_status_class')) {
    function hr_status_class(string $status): string
    {
        $classes = [
            // Employment status
            'active'     => 'hr-status-active',
            'on_leave'   => 'hr-status-warning',
            'suspended'  => 'hr-status-warning',
            'terminated' => 'hr-status-danger',
            'retired'    => 'hr-status-inactive',
            // General
            'inactive'   => 'hr-status-inactive',
            'draft'      => 'hr-status-inactive',
            'open'       => 'hr-status-info',
            'in_progress'=> 'hr-status-warning',
            'completed'  => 'hr-status-active',
            'cancelled'  => 'hr-status-danger',
            'closed'     => 'hr-status-inactive',
            'planned'    => 'hr-status-info',
            'on_hold'    => 'hr-status-warning',
        ];
        return $classes[$status] ?? 'hr-status-info';
    }
}

if (!function_exists('hr_priority_class')) {
    function hr_priority_class(string $priority): string
    {
        $classes = [
            'low'      => 'hr-priority-low',
            'normal'   => 'hr-priority-normal',
            'high'     => 'hr-priority-high',
            'urgent'   => 'hr-priority-urgent',
            'critical' => 'hr-priority-critical',
        ];
        return $classes[$priority] ?? 'hr-priority-normal';
    }
}

if (!function_exists('hr_rating_class')) {
    function hr_rating_class(string $rating): string
    {
        $classes = [
            'excellent'         => 'hr-rating-excellent',
            'very_good'         => 'hr-rating-very-good',
            'good'              => 'hr-rating-good',
            'satisfactory'      => 'hr-rating-satisfactory',
            'needs_improvement' => 'hr-rating-needs-improvement',
            'unsatisfactory'    => 'hr-rating-unsatisfactory',
        ];
        return $classes[$rating] ?? 'hr-rating-good';
    }
}

// ============================================
// 🔢 اعداد فارسی
// ============================================

if (!function_exists('hr_num')) {
    /**
     * تبدیل اعداد به فارسی
     */
    function hr_num($number): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en, $fa, (string) $number);
    }
}

if (!function_exists('hr_money')) {
    /**
     * فرمت مبلغ به صورت سه‌رقمی با جداکننده
     */
    function hr_money($amount, string $currency = 'ریال'): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }
        $formatted = number_format((float) $amount);
        return hr_num($formatted) . ' ' . $currency;
    }
}

// ============================================
// 🛠️ ابزارهای عمومی
// ============================================

if (!function_exists('hr_active_system_id')) {
    /**
     * دریافت شناسه سیستم فعال کاربر
     */
    function hr_active_system_id(): ?int
    {
        return !empty($_SESSION['hr_active_system']) ? (int) $_SESSION['hr_active_system'] : null;
    }
}

if (!function_exists('hr_full_name')) {
    /**
     * ساخت نام کامل کارمند
     */
    function hr_full_name(array $employee): string
    {
        $first = $employee['first_name'] ?? '';
        $last  = $employee['last_name'] ?? '';
        return trim($first . ' ' . $last) ?: '—';
    }
}

if (!function_exists('hr_calculate_age')) {
    /**
     * محاسبه سن بر اساس تاریخ تولد (میلادی)
     */
    function hr_calculate_age(?string $birthDate): ?int
    {
        if (empty($birthDate)) {
            return null;
        }
        try {
            $birth = new \DateTime($birthDate);
            $now = new \DateTime();
            return $now->diff($birth)->y;
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('hr_calculate_tenure')) {
    /**
     * محاسبه سابقه کار (به سال) بر اساس تاریخ استخدام
     */
    function hr_calculate_tenure(?string $hireDate): ?float
    {
        if (empty($hireDate)) {
            return null;
        }
        try {
            $hire = new \DateTime($hireDate);
            $now = new \DateTime();
            $diff = $now->diff($hire);
            $years = $diff->y + ($diff->m / 12);
            return round($years, 1);
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('hr_truncate')) {
    /**
     * کوتاه کردن متن با پشتیبانی UTF-8
     *
     * @param string $text
     * @param int    $length
     * @param string $suffix
     * @return string
     */
    function hr_truncate(string $text, int $length = 80, string $suffix = '...'): string
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