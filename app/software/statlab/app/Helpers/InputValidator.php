<?php
namespace App\Software\Statlab\Helpers;

/** Strict parsing and shared domain checks for StatLab requests. */
class InputValidator
{
    public static function parseNumbers(string $text): array
    {
        $text = self::normalizeDigits($text);
        if (trim($text) === '') {
            return [];
        }

        $tokens = preg_split('/[\s,;|]+/u', trim($text));
        $values = [];
        foreach ($tokens as $token) {
            if ($token === '' || !is_numeric($token)) {
                throw new \InvalidArgumentException('ورودی شامل مقدار غیرعددی یا جداکننده نامعتبر است: ' . $token);
            }
            $value = (float) $token;
            if (!is_finite($value)) {
                throw new \InvalidArgumentException('همه مقادیر باید عدد متناهی باشند.');
            }
            $values[] = $value;
        }
        return $values;
    }

    public static function parseMatrix(string $text): array
    {
        $text = self::normalizeDigits($text);
        $rows = [];
        foreach (preg_split('/\R/u', trim($text)) as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = self::parseNumbers($line);
        }
        if (!$rows) {
            return [];
        }

        $width = count($rows[0]);
        if ($width === 0) {
            throw new \InvalidArgumentException('ماتریس نباید سطر خالی داشته باشد.');
        }
        foreach ($rows as $row) {
            if (count($row) !== $width) {
                throw new \InvalidArgumentException('همه سطرهای ماتریس باید تعداد ستون یکسان داشته باشند.');
            }
        }
        return $rows;
    }

    public static function finiteNumber($value, string $label): float
    {
        if ((!is_int($value) && !is_float($value) && !is_string($value)) || !is_numeric($value)) {
            throw new \InvalidArgumentException($label . ' باید عددی معتبر باشد.');
        }
        $number = (float) $value;
        if (!is_finite($number)) {
            throw new \InvalidArgumentException($label . ' باید عدد متناهی باشد.');
        }
        return $number;
    }

    public static function alpha($value): float
    {
        $alpha = self::finiteNumber($value, 'سطح معناداری');
        if ($alpha <= 0.0 || $alpha >= 1.0) {
            throw new \InvalidArgumentException('سطح معناداری باید بزرگ‌تر از صفر و کوچک‌تر از یک باشد.');
        }
        return $alpha;
    }

    public static function normalizeDigits(string $text): string
    {
        $text = strtr($text, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        return str_replace(['٫', '٬'], ['.', ''], $text);
    }
}
