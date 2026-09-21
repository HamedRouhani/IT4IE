<?php
/**
 * ============================================================
 * IT4IE - DateHelper
 * ============================================================
 * تبدیل تاریخ شمسی ↔ میلادی — کاملاً مستقل از jdf.php و functions.php
 * 
 * مسیر: app/helpers/DateHelper.php
 * ============================================================
 */

namespace App\Helpers;

class DateHelper
{
    /**
     * تبدیل تاریخ میلادی دیتابیس به شمسی
     */
    public static function toJalali($gregorianDate, $format = 'Y/m/d')
    {
        if (empty($gregorianDate) 
            || $gregorianDate === '0000-00-00' 
            || $gregorianDate === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($gregorianDate);
        if ($timestamp === false) {
            return '-';
        }

        $gy = (int) date('Y', $timestamp);
        $gm = (int) date('m', $timestamp);
        $gd = (int) date('d', $timestamp);

        $H = date('H', $timestamp);
        $i = date('i', $timestamp);
        $s = date('s', $timestamp);

        // استفاده از تابع داخلی (اگر در Functions.php ماژول PdM لود شده باشد)
        if (function_exists('pdm_gregorian_to_jalali')) {
            $jDate = pdm_gregorian_to_jalali($gy, $gm, $gd);
        } else {
            // فال‌بک: تابع محلی
            $jDate = self::gregorianToJalali($gy, $gm, $gd);
        }

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

    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    public static function toGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        $jalaliDate = trim($jalaliDate);
        $normalized = str_replace('/', '-', $jalaliDate);
        $parts = explode('-', $normalized);

        if (count($parts) !== 3) {
            return null;
        }

        // تبدیل اعداد فارسی به انگلیسی
        $parts = array_map(function ($p) {
            $p = trim($p);
            $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            return (int) str_replace($fa, $en, $p);
        }, $parts);

        list($jy, $jm, $jd) = $parts;

        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        // استفاده از تابع داخلی (اگر موجود باشد)
        if (function_exists('pdm_jalali_to_gregorian')) {
            $g = pdm_jalali_to_gregorian($jy, $jm, $jd);
        } else {
            $g = self::jalaliToGregorian($jy, $jm, $jd);
        }

        return sprintf('%04d-%02d-%02d', $g[0], $g[1], $g[2]);
    }

    /**
     * تبدیل شمسی به میلادی با فرمت datetime
     */
    public static function toGregorianDateTime($jalaliDate, $time = '00:00:00')
    {
        $gDate = self::toGregorian($jalaliDate);
        if ($gDate === null) {
            return null;
        }
        return $gDate . ' ' . $time;
    }

    /**
     * تاریخ و ساعت جاری به شمسی
     */
    public static function nowJalali($format = 'Y/m/d H:i:s')
    {
        return self::toJalali(date('Y-m-d H:i:s'), $format);
    }

    /**
     * تاریخ جاری به میلادی
     */
    public static function nowGregorian($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }

    // ═══════════════════════════════════════════════════════
    // توابع داخلی تبدیل (فال‌بک)
    // ═══════════════════════════════════════════════════════

    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
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

    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
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