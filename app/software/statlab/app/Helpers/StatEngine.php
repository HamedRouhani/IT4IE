<?php
namespace App\Software\Statlab\Helpers;

/**
 * StatEngine - موتور محاسبات آماری پایه
 */
class StatEngine
{
    public static function mean(array $data): float
    {
        $n = count($data);
        return $n ? array_sum($data) / $n : 0.0;
    }

    public static function median(array $data): float
    {
        $n = count($data);
        if (!$n) return 0.0;
        $sorted = $data;
        sort($sorted);
        $mid = intdiv($n, 2);
        return $n % 2 ? (float)$sorted[$mid] : ($sorted[$mid - 1] + $sorted[$mid]) / 2;
    }

    public static function mode(array $data)
    {
        if (empty($data)) return null;
        $counts = array_count_values(array_map('strval', $data));
        arsort($counts);
        return array_key_first($counts);
    }

    public static function variance(array $data, bool $sample = true): float
    {
        $n = count($data);
        if ($n < 2) return 0.0;
        $mean = self::mean($data);
        $ss = 0.0;
        foreach ($data as $x) $ss += ($x - $mean) ** 2;
        return $ss / ($sample ? $n - 1 : $n);
    }

    public static function std(array $data, bool $sample = true): float
    {
        return sqrt(self::variance($data, $sample));
    }

    public static function min(array $data): float { return $data ? (float)min($data) : 0.0; }
    public static function max(array $data): float { return $data ? (float)max($data) : 0.0; }
    public static function range(array $data): float { return self::max($data) - self::min($data); }

    public static function quartiles(array $data): array
    {
        $sorted = $data;
        sort($sorted);
        $n = count($sorted);
        if (!$n) return ['q1' => 0, 'q2' => 0, 'q3' => 0];
        $q = function ($p) use ($sorted, $n) {
            $pos = ($n - 1) * $p;
            $lo = (int)floor($pos);
            $hi = (int)ceil($pos);
            if ($lo === $hi) return $sorted[$lo];
            return $sorted[$lo] + ($sorted[$hi] - $sorted[$lo]) * ($pos - $lo);
        };
        return ['q1' => $q(0.25), 'q2' => $q(0.5), 'q3' => $q(0.75)];
    }

    public static function skewness(array $data): float
    {
        $n = count($data);
        if ($n < 3) return 0.0;
        $m = self::mean($data);
        $s = self::std($data, false);
        if ($s == 0.0) return 0.0;
        $sum = 0.0;
        foreach ($data as $x) $sum += (($x - $m) / $s) ** 3;
        return ($n / (($n - 1) * ($n - 2))) * $sum;
    }

    public static function kurtosis(array $data): float
    {
        $n = count($data);
        if ($n < 4) return 0.0;
        $m = self::mean($data);
        $s = self::std($data, false);
        if ($s == 0.0) return 0.0;
        $sum = 0.0;
        foreach ($data as $x) $sum += (($x - $m) / $s) ** 4;
        return $sum / $n - 3;
    }

    public static function outliers(array $data): array
    {
        $q = self::quartiles($data);
        $iqr = $q['q3'] - $q['q1'];
        $lo = $q['q1'] - 1.5 * $iqr;
        $hi = $q['q3'] + 1.5 * $iqr;
        return array_values(array_filter($data, fn($x) => $x < $lo || $x > $hi));
    }

    /**
     * خلاصه آماری کامل (Describe)
     */
    public static function describe(array $data): array
    {
        return [
            'count'      => count($data),
            'mean'       => round(self::mean($data), 6),
            'median'     => round(self::median($data), 6),
            'mode'       => self::mode($data),
            'std'        => round(self::std($data), 6),
            'variance'   => round(self::variance($data), 6),
            'min'        => self::min($data),
            'max'        => self::max($data),
            'range'      => self::range($data),
            'quartiles'  => self::quartiles($data),
            'skewness'   => round(self::skewness($data), 6),
            'kurtosis'   => round(self::kurtosis($data), 6),
            'outliers'   => self::outliers($data),
        ];
    }
}