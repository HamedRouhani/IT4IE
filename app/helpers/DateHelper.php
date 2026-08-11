<?php
namespace App\Helpers;

class DateHelper
{
    /**
     * تبدیل تاریخ میلادی دیتابیس به شمسی برای نمایش
     * @param string $gregorianDate فرمت: Y-m-d یا Y-m-d H:i:s
     * @param string $format فرمت خروجی (پیش‌فرض: Y/m/d)
     * @return string
     */
    public static function toJalali($gregorianDate, $format = 'Y/m/d')
    {
        if (empty($gregorianDate) || $gregorianDate === '0000-00-00 00:00:00') {
            return '-';
        }
        // jdate فرمت PHP را می‌پذیرد. Y=m سال، m=ماه، d=روز، H=ساعت، i=دقیقه، s=ثانیه
        return jdate($format, strtotime($gregorianDate));
    }

    /**
     * تبدیل تاریخ شمسی ورودی فرم به میلادی برای ذخیره در دیتابیس
     * @param string $jalaliDate فرمت: Y/m/d
     * @return string|false فرمت: Y-m-d
     */
    public static function toGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }
        // تبدیل ۱۴۰۵/۰۵/۲۰ به ۱۴۰۵-۰۵-۲۰ برای تابع jgdate
        $normalized = str_replace('/', '-', $jalaliDate);
        
        // jgdate خروجی را به صورت آرایه [سال, ماه, روز] برمی‌گرداند
        $gDate = jgdate('Y-m-d', strtotime(str_replace('-', '/', $normalized))); 
        
        // اگر jgdate به درستی کار نکرد، از روش جایگزین استفاده می‌کنیم:
        if (!$gDate) {
            $parts = explode('-', $normalized);
            if (count($parts) === 3) {
                $gDate = jdate('Y-m-d', jgdate('U', strtotime($normalized))); // روش ساده‌شده
            }
        }
        
        // روش مطمئن‌تر با استفاده از توابع داخلی jdf:
        $g = jgdate('Y-m-d', strtotime(str_replace('/', '-', $jalaliDate)));
        return $g ? $g : date('Y-m-d'); // فال‌بک
    }
    
    /**
     * دریافت تاریخ و ساعت جاری به فرمت شمسی
     */
    public static function nowJalali($format = 'Y/m/d H:i:s')
    {
        return jdate($format);
    }
}