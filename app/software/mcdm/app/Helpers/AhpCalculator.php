<?php

namespace App\Software\Mcdm\Helpers;

class AhpCalculator
{
    // شاخص تصادفی ساعتی (Random Index)
    private static $RI = [
        1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
        11 => 1.51, 12 => 1.48, 13 => 1.56, 14 => 1.57, 15 => 1.59
    ];

    public static function analyze(array $matrix): array
    {
        $n = count($matrix);

        if ($n < 2) {
            return ['status' => 'error', 'message' => 'حداقل دو معیار لازم است.'];
        }

        foreach ($matrix as $i => $row) {
            if (!is_array($row) || count($row) !== $n) {
                return ['status' => 'error', 'message' => 'ماتریس مقایسه باید مربعی باشد.'];
            }
            foreach ($row as $j => $value) {
                if (!is_numeric($value) || !is_finite((float)$value) || (float)$value <= 0) {
                    return ['status' => 'error', 'message' => 'همهٔ مقایسه‌ها باید عدد مثبت و متناهی باشند.'];
                }
                $matrix[$i][$j] = (float)$value;
            }
        }
        if ($n > 15) {
            return ['status' => 'error', 'message' => 'برای بیش از ۱۵ معیار، شاخص RI این نسخه تعریف نشده است.'];
        }
        for ($i = 0; $i < $n; $i++) {
            if (abs($matrix[$i][$i] - 1.0) > 1e-8) {
                return ['status' => 'error', 'message' => 'خانه‌های قطر اصلی ماتریس AHP باید برابر ۱ باشند.'];
            }
            for ($j = $i + 1; $j < $n; $j++) {
                if (abs($matrix[$i][$j] * $matrix[$j][$i] - 1.0) > 1e-6) {
                    return ['status' => 'error', 'message' => 'ماتریس AHP باید متقابل باشد؛ هر مقایسه باید معکوس خانهٔ متناظر باشد.'];
                }
            }
        }

        // ۱. نرمال‌سازی ستونی
        $normalized = array_fill(0, $n, array_fill(0, $n, 0));
        for ($j = 0; $j < $n; $j++) {
            $colSum = 0;
            for ($i = 0; $i < $n; $i++) {
                $colSum += $matrix[$i][$j];
            }
            $colSum = $colSum ?: 1;
            for ($i = 0; $i < $n; $i++) {
                $normalized[$i][$j] = $matrix[$i][$j] / $colSum;
            }
        }

        // ۲. بردار وزن (میانگین سطری)
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = array_sum($normalized[$i]) / $n;
        }

        // ۳. محاسبه λmax
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            $weightedSum = 0;
            for ($j = 0; $j < $n; $j++) {
                $weightedSum += $matrix[$i][$j] * $weights[$j];
            }
            $lambdaMax += ($weights[$i] > 0) ? ($weightedSum / $weights[$i]) : 0;
        }
        $lambdaMax /= $n;

        // ۴. شاخص‌های سازگاری
        $CI = ($n > 1) ? ($lambdaMax - $n) / ($n - 1) : 0;
        $RI = self::$RI[$n] ?? 1.59;
        $CR = ($RI > 0) ? $CI / $RI : 0;
        $isConsistent = $CR <= 0.10;

        $weights = array_map(fn($w) => round($w, 6), $weights);

        return [
            'status'  => 'success',
            'method'  => 'AHP',
            'weights' => $weights,
            'consistency_metrics' => [
                'lambda_max'    => round($lambdaMax, 4),
                'CI'            => round($CI, 4),
                'RI'            => round($RI, 4),
                'CR'            => round($CR, 4),
                'is_consistent' => $isConsistent
            ],
            'smart_feedback' => $isConsistent
                ? '✅ نرخ ناسازگاری قابل قبول است (CR ≤ 0.1). قضاوت‌ها سازگارند.'
                : '⚠️ نرخ ناسازگاری بالاست (CR > 0.1). لطفاً مقایسات زوجی را بازبینی کنید.'
        ];
    }
}
