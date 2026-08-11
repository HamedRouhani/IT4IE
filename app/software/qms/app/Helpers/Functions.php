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