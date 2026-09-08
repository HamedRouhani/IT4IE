<?php
namespace App\Software\Or\Helpers;

class MarkovEngine
{
    /** اعتبارسنجی ماتریس انتقال */
    public static function validateMatrix(array $matrix): array
    {
        $n = count($matrix);
        $errors = [];
        foreach ($matrix as $i => $row) {
            if (count($row) !== $n) {
                $errors[] = "سطر " . ($i + 1) . " باید دقیقاً $n عنصر داشته باشد.";
            }
            $sum = array_sum($row);
            if (abs($sum - 1.0) > 1e-5) {
                $errors[] = "مجموع احتمالات سطر " . ($i + 1) . " باید دقیقاً ۱ باشد (مجموع فعلی: " . round($sum, 4) . ").";
            }
            foreach ($row as $j => $val) {
                if ($val < 0 || $val > 1) {
                    $errors[] = "احتمال در سطر " . ($i + 1) . " و ستون " . ($j + 1) . " نامعتبر است (باید بین ۰ و ۱ باشد).";
                }
            }
        }
        return $errors;
    }

    /** ضرب بردار در ماتریس */
    private static function vectorMatrixMultiply(array $v, array $M): array
    {
        $n = count($M);
        $res = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $res[$j] += $v[$i] * $M[$i][$j];
            }
        }
        return $res;
    }

    /** ضرب دو ماتریس */
    private static function matrixMultiply(array $A, array $B): array
    {
        $n = count($A);
        $res = array_fill(0, $n, array_fill(0, $n, 0.0));
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                for ($k = 0; $k < $n; $k++) {
                    $res[$i][$j] += $A[$i][$k] * $B[$k][$j];
                }
            }
        }
        return $res;
    }

    /** حل اصلی */
    public static function solve(array $states, array $matrix, array $initial, int $steps): array
    {
        $n = count($states);
        $errors = self::validateMatrix($matrix);
        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        // پیش‌بینی n گامی
        $current = $initial;
        $history = [0 => $initial];
        for ($step = 1; $step <= $steps; $step++) {
            $current = self::vectorMatrixMultiply($current, $matrix);
            $history[$step] = $current;
        }

        // محاسبه حالت پایدار (Steady State) با روش توانی (Power Method)
        $steady = array_fill(0, $n, 1 / $n);
        $maxIter = 2000;
        $tol = 1e-8;
        $converged = false;

        for ($iter = 0; $iter < $maxIter; $iter++) {
            $next = self::vectorMatrixMultiply($steady, $matrix);
            $diff = 0;
            for ($i = 0; $i < $n; $i++) $diff += abs($next[$i] - $steady[$i]);
            
            if ($diff < $tol) {
                $steady = $next;
                $converged = true;
                break;
            }
            $steady = $next;
        }

        // تفسیر خودکار
        $interpretation = self::interpret($states, $initial, $history[$steps], $steady, $converged, $steps);

        return [
            'status'       => 'ok',
            'states'       => $states,
            'matrix'       => $matrix,
            'initial'      => $initial,
            'steps'        => $steps,
            'final_state'  => $history[$steps],
            'steady_state' => $steady,
            'converged'    => $converged,
            'history'      => $history,
            'interpretation' => $interpretation,
        ];
    }

    private static function interpret(array $states, array $init, array $final, array $steady, bool $converged, int $steps): string
    {
        $maxFinalIdx = array_keys($final, max($final))[0];
        $maxSteadyIdx = array_keys($steady, max($steady))[0];
        
        $out = [];
        $out[] = "📊 سیستم دارای " . count($states) . " حالت است.";
        $out[] = "🔮 پس از $steps گام، محتمل‌ترین حالت، «{$states[$maxFinalIdx]}» با احتمال " . round($final[$maxFinalIdx] * 100, 2) . "% است.";
        
        if ($converged) {
            $out[] = "⚖️ سیستم به توزیع حالت پایدار (Steady State) همگرا شده است.";
            $out[] = "در بلندمدت، سیستم بیشترین زمان را در حالت «{$states[$maxSteadyIdx]}» (" . round($steady[$maxSteadyIdx] * 100, 2) . "%) سپری خواهد کرد.";
            
            // بررسی حالت جذب (Absorbing) برای قابلیت اطمینان
            $absorbing = [];
            foreach ($steady as $i => $p) {
                if ($p > 0.999) $absorbing[] = $states[$i];
            }
            if (!empty($absorbing)) {
                $out[] = "⚠️ هشدار قابلیت اطمینان: حالت(های) «" . implode('، ', $absorbing) . "» جاذب هستند. سیستم در بلندمدت به طور قطعی به این حالت(ها) خواهد رفت (مانند خرابی کامل).";
            }
        } else {
            $out[] = "⚠️ سیستم پس از $steps گام به همگرایی کامل نرسیده است (ممکن است چرخه‌ای یا دارای چند زیرمجموعه ارگودیک باشد).";
        }
        
        return implode("\n", $out);
    }
}