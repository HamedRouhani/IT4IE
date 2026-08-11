<?php
/*
                    In the name of Allah
          JDF (Jalali Date Functions) for PHP
           Version 2.73 - 1399/11/07
        http://jdf.scr.ir - info@scr.ir
                  By: Mohammad Reza Jebelli
             Modified for IT4IE Project
*/

function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
{
    $T_sec = 0; /* <= رفع خطاي زمان سرور ، با اعداد '+' و '-' بر حسب ثانيه */

    if ($time_zone != 'local') {
        date_default_timezone_set($time_zone);
    }

    if ($timestamp === '') {
        $timestamp = time() + $T_sec;
    }

    $T_sec = 0;
    $temp = $format;
    $str = '';
    $date = array();
    $tr_num = $tr_num == 'fa' ? array('۰', '۱', '', '۳', '۴', '', '۶', '۷', '', '۹') : array('', '۱', '۲', '', '۴', '۵', '', '۷', '۸', '');

    $j_d = gregorian_to_jalali(date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
    $j_y = $j_d[0];
    $j_m = $j_d[1];
    $j_d = $j_d[2];

    $format = str_replace(
        array('L', 'F', 'o', 'U', 'c', 'r'),
        array('l', 'f', 'Y', 'U', 'Y-m-d\TH:i:sP', 'D, d M Y H:i:s O'),
        $format
    );

    $date = array(
        'Y' => $j_y,
        'y' => substr($j_y, 2),
        'm' => ($j_m > 9) ? $j_m : '0' . $j_m,
        'n' => $j_m,
        'd' => ($j_d > 9) ? $j_d : '0' . $j_d,
        'j' => $j_d,
        'H' => date('H', $timestamp),
        'h' => date('h', $timestamp),
        'G' => date('G', $timestamp),
        'g' => date('g', $timestamp),
        'i' => date('i', $timestamp),
        's' => date('s', $timestamp),
        'A' => date('A', $timestamp),
        'a' => date('a', $timestamp),
        'l' => date('l', $timestamp),
        'D' => date('D', $timestamp),
        'w' => date('w', $timestamp),
        'W' => date('W', $timestamp),
        'z' => date('z', $timestamp),
        'S' => date('S', $timestamp),
        'M' => date('M', $timestamp),
        'f' => j_month_fa($j_m),
        'F' => j_month_fa($j_m),
    );

    $date['L'] = j_is_leap_year($j_y) ? 1 : 0;
    $date['o'] = $j_y;

    $str = $format;
    foreach ($date as $key => $val) {
        $str = str_replace($key, $val, $str);
    }

    if ($tr_num == 'fa') {
        $str = str_replace(range(0, 9), $tr_num, $str);
    }

    return $str;
}

function j_month_fa($month)
{
    $months = array(
        '', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    );
    return $months[(int)$month];
}

function j_is_leap_year($year)
{
    $r = $year % 33;
    if ($r == 1 || $r == 5 || $r == 9 || $r == 13 || $r == 17 || $r == 22 || $r == 26 || $r == 30) {
        return true;
    }
    return false;
}

function gregorian_to_jalali($gy, $gm, $gd, $mod = '')
{
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    $gy = $gy - 1600;
    $gm = $gm - 1;
    $gy2 = ($gm > 1) ? 1 : 0;
    $days = 355666 + (365 * $gy) + ((int)(($gy + 3) / 4)) - ((int)(($gy + 99) / 100)) + ((int)(($gy + 399) / 400)) - 80 + $gd + (($gy2 + $gm > 2) ? 1 : 0) + ($gy2 && ($gm == 1 || ($gm == 2 && $gd == 29)) ? -1 : 0);
    $jy = -1595 + (33 * (int)($days / 12053));
    $days %= 12053;
    $jy += 4 * (int)($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    return ($mod == '') ? array($jy, $jm, $jd) : $jy + $mod + $jm + $mod + $jd;
}

function jalali_to_gregorian($jy, $jm, $jd, $mod = '')
{
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    $jy = $jy - 979;
    $jm = $jm - 1;
    $jd = $jd - 1;
    $j_day_no = 365 * $jy + ((int)($jy / 33)) * 8 + ((int)(($jy % 33 + 3) / 4));
    for ($i = 0; $i < $jm; ++$i) {
        $j_day_no += $j_days_in_month[$i];
    }
    $j_day_no += $jd;
    $g_day_no = $j_day_no + 79;
    $gy = 1600 + 400 * ((int)($g_day_no / 146097));
    $g_day_no %= 146097;
    if ($g_day_no > 36524) {
        $gy += 100 * ((int)(--$g_day_no / 36524));
        $g_day_no %= 36524;
        if ($g_day_no >= 36524) {
            ++$g_day_no;
        }
    }
    $gy += 4 * ((int)($g_day_no / 1461));
    $g_day_no %= 1461;
    if ($g_day_no > 365) {
        $gy += (int)(($g_day_no - 1) / 365);
        $g_day_no = ($g_day_no - 1) % 365;
    }
    $gd = $g_day_no + 1;
    for ($gm = 0; $gm < 12 && $gd > $g_days_in_month[$gm]; ++$gm) {
        $gd -= $g_days_in_month[$gm];
    }
    if ($gm == 1 && $gd > 28 && (($gy % 4 == 0 && $gy % 100 != 0) || $gy % 400 == 0)) {
        --$gd;
    }
    return ($mod == '') ? array($gy + 1600, $gm + 1, $gd) : $gy + 1600 + $mod + $gm + 1 + $mod + $gd;
}

function jgdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
{
    return jdate($format, $timestamp, $none, $time_zone, $tr_num);
}

function jmktime($hour = '', $minute = '', $second = '', $jmonth = '', $jday = '', $jyear = '', $is_dst = -1)
{
    $hour = ($hour == '') ? date('H') : $hour;
    $minute = ($minute == '') ? date('i') : $minute;
    $second = ($second == '') ? date('s') : $second;
    $jmonth = ($jmonth == '') ? date('n') : $jmonth;
    $jday = ($jday == '') ? date('j') : $jday;
    $jyear = ($jyear == '') ? date('Y') : $jyear;
    
    $g_date = jalali_to_gregorian($jyear, $jmonth, $jday);
    return mktime($hour, $minute, $second, $g_date[1], $g_date[2], $g_date[0], $is_dst);
}

function jgetdate($timestamp = '', $time_zone = 'Asia/Tehran')
{
    $timestamp = ($timestamp == '') ? time() : $timestamp;
    $date = jdate('Y n j w H i s', $timestamp, '', $time_zone, 'en');
    $date = explode(' ', $date);
    return array(
        'seconds' => (int)$date[6],
        'minutes' => (int)$date[5],
        'hours' => (int)$date[4],
        'mday' => (int)$date[2],
        'wday' => (int)$date[3],
        'mon' => (int)$date[1],
        'year' => (int)$date[0],
        'yday' => jdate('z', $timestamp, '', $time_zone, 'en'),
        'weekday' => jdate('l', $timestamp, '', $time_zone, 'fa'),
        'month' => jdate('F', $timestamp, '', $time_zone, 'fa'),
        0 => $timestamp
    );
}

function jcheckdate($jmonth, $jday, $jyear)
{
    if ($jmonth < 1 || $jmonth > 12) {
        return false;
    }
    if ($jday < 1) {
        return false;
    }
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, j_is_leap_year($jyear) ? 30 : 29);
    if ($jday > $j_days_in_month[$jmonth - 1]) {
        return false;
    }
    return true;
}

function jstrftime($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
{
    return jdate($format, $timestamp, $none, $time_zone, $tr_num);
}
?>