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
        for ($j = 0; $j < $n; $j++) {
            $weightedSum = 0;
            for ($i = 0; $i < $n; $i++) {
                $weightedSum += $matrix[$i][$j] * $weights[$i];
            }
            $lambdaMax += ($weights[$j] > 0) ? ($weightedSum / $weights[$j]) : 0;
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