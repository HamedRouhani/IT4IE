<?php
namespace App\Software\Or\Helpers;

class SimplexSolver
{
    private const EPSILON = 1e-9;

    public static function solve(array $c, array $A, array $b, array $types, string $objType): array
    {
        $m = count($b);
        $n = count($c);

        if ($m === 0 || $n === 0) {
            return ['status' => 'error', 'message' => 'داده‌های ورودی نامعتبر هستند.'];
        }

        // برای ماکزیمم کردن، ضرایب تابع هدف را منفی می‌کنیم (چون در سطر هدف Z - CX = 0 داریم)
        // برای مینیمم کردن، ابتدا مسئله را به ماکزیمم تبدیل می‌کنیم (ضرب در -1)
        $isMinimize = ($objType === 'minimize');
        $objCoeffs = $isMinimize ? array_map(fn($val) => -$val, $c) : $c;

        $numCols = $n + 2 * $m + 1;
        $tableau = array_fill(0, $m + 1, array_fill(0, $numCols, 0.0));
        $artificialVars = [];

        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $tableau[$i][$j] = (float)$A[$i][$j];
            }

            $rhs = (float)$b[$i];
            $type = $types[$i];

            if ($rhs < 0) {
                for ($j = 0; $j < $n; $j++) $tableau[$i][$j] *= -1;
                $rhs *= -1;
                $type = ($type === '<=') ? '>=' : (($type === '>=') ? '<=' : '=');
            }

            $tableau[$i][$numCols - 1] = $rhs;

            if ($type === '<=') {
                $tableau[$i][$n + $i] = 1.0;
            } elseif ($type === '>=') {
                $tableau[$i][$n + $i] = -1.0;
                $tableau[$i][$n + $m + $i] = 1.0;
                $artificialVars[] = $n + $m + $i;
            } elseif ($type === '=') {
                $tableau[$i][$n + $m + $i] = 1.0;
                $artificialVars[] = $n + $m + $i;
            }
        }

        // ================= فاز ۱: یافتن جواب موجه پایه =================
        if (!empty($artificialVars)) {
            $phase1Obj = array_fill(0, $numCols, 0.0);
            foreach ($artificialVars as $artIdx) {
                $phase1Obj[$artIdx] = 1.0;
            }
            
            for ($i = 0; $i < $m; $i++) {
                if (in_array($n + $m + $i, $artificialVars)) {
                    for ($j = 0; $j < $numCols; $j++) {
                        $phase1Obj[$j] -= $tableau[$i][$j];
                    }
                }
            }
            $tableau[$m] = $phase1Obj;

            $iterations = 0;
            $maxIter = 1000;
            while ($iterations < $maxIter) {
                $pivotCol = -1;
                $maxVal = self::EPSILON;
                for ($j = 0; $j < $numCols - 1; $j++) {
                    if ($tableau[$m][$j] > $maxVal) {
                        $maxVal = $tableau[$m][$j];
                        $pivotCol = $j;
                    }
                }

                if ($pivotCol === -1) break;

                $pivotRow = -1;
                $minRatio = PHP_FLOAT_MAX;
                for ($i = 0; $i < $m; $i++) {
                    if ($tableau[$i][$pivotCol] > self::EPSILON) {
                        $ratio = $tableau[$i][$numCols - 1] / $tableau[$i][$pivotCol];
                        if ($ratio < $minRatio) {
                            $minRatio = $ratio;
                            $pivotRow = $i;
                        }
                    }
                }

                if ($pivotRow === -1) {
                    return ['status' => 'unbounded', 'message' => 'مسئله در فاز ۱ کران‌دار نیست.'];
                }

                $pivotVal = $tableau[$pivotRow][$pivotCol];
                for ($j = 0; $j < $numCols; $j++) {
                    $tableau[$pivotRow][$j] /= $pivotVal;
                }
                for ($i = 0; $i <= $m; $i++) {
                    if ($i !== $pivotRow) {
                        $factor = $tableau[$i][$pivotCol];
                        for ($j = 0; $j < $numCols; $j++) {
                            $tableau[$i][$j] -= $factor * $tableau[$pivotRow][$j];
                        }
                    }
                }
                $iterations++;
            }

            if (abs($tableau[$m][$numCols - 1]) > 1e-6) {
                return ['status' => 'infeasible', 'message' => 'مسئله جواب موجه ندارد (ناحیه موجه تهی است).'];
            }
        }

        // ================= فاز ۲: بهینه‌سازی تابع هدف اصلی =================
        $tableau[$m] = array_fill(0, $numCols, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $tableau[$m][$j] = -$objCoeffs[$j]; 
        }

        $basicVars = [];
        for ($i = 0; $i < $m; $i++) {
            $basicCol = -1;
            $ones = 0;
            for ($j = 0; $j < $n + $m; $j++) {
                if (abs($tableau[$i][$j] - 1.0) < self::EPSILON) {
                    $ones++;
                    $basicCol = $j;
                } elseif (abs($tableau[$i][$j]) > self::EPSILON) {
                    $ones = -1;
                    break;
                }
            }
            if ($ones === 1 && $basicCol !== -1) {
                $basicVars[$basicCol] = $i;
                $factor = $tableau[$m][$basicCol];
                for ($j = 0; $j < $numCols; $j++) {
                    $tableau[$m][$j] -= $factor * $tableau[$i][$j];
                }
            }
        }

        $iterations = 0;
        $maxIter = 1000;
        while ($iterations < $maxIter) {
            // ✅ اصلاح مهم: یافتن منفی‌ترین مقدار در سطر هدف (چون ضرایب به صورت -c ذخیره شده‌اند)
            $pivotCol = -1;
            $minVal = -self::EPSILON;
            for ($j = 0; $j < $n + $m; $j++) {
                if ($tableau[$m][$j] < $minVal) {
                    $minVal = $tableau[$m][$j];
                    $pivotCol = $j;
                }
            }

            if ($pivotCol === -1) {
                $solution = array_fill(0, $n, 0.0);
                foreach ($basicVars as $col => $row) {
                    if ($col < $n) {
                        $solution[$col] = round($tableau[$row][$numCols - 1], 6);
                    }
                }

                $optimalValue = round($tableau[$m][$numCols - 1], 6);
                if ($isMinimize) {
                    $optimalValue = -$optimalValue;
                }

                $shadowPrices = [];
                for ($i = 0; $i < $m; $i++) {
                    $slackIdx = $n + $i;
                    $artIdx = $n + $m + $i;
                    $shadowPrices[] = round(abs($tableau[$m][$slackIdx] ?: $tableau[$m][$artIdx]), 6);
                }

                return [
                    'status' => 'optimal',
                    'optimal_value' => $optimalValue,
                    'solution' => $solution,
                    'shadow_prices' => $shadowPrices,
                    'iterations' => $iterations,
                    'message' => 'جواب بهینه با موفقیت یافت شد.'
                ];
            }

            $pivotRow = -1;
            $minRatio = PHP_FLOAT_MAX;
            for ($i = 0; $i < $m; $i++) {
                if ($tableau[$i][$pivotCol] > self::EPSILON) {
                    $ratio = $tableau[$i][$numCols - 1] / $tableau[$i][$pivotCol];
                    if ($ratio < $minRatio) {
                        $minRatio = $ratio;
                        $pivotRow = $i;
                    }
                }
            }

            if ($pivotRow === -1) {
                return ['status' => 'unbounded', 'message' => 'مسئله کران‌دار نیست (جواب بهینه بی‌نهایت است).'];
            }

            $pivotVal = $tableau[$pivotRow][$pivotCol];
            for ($j = 0; $j < $numCols; $j++) {
                $tableau[$pivotRow][$j] /= $pivotVal;
            }
            for ($i = 0; $i <= $m; $i++) {
                if ($i !== $pivotRow) {
                    $factor = $tableau[$i][$pivotCol];
                    for ($j = 0; $j < $numCols; $j++) {
                        $tableau[$i][$j] -= $factor * $tableau[$pivotRow][$j];
                    }
                }
            }
            
            unset($basicVars[array_search($pivotRow, $basicVars)]);
            $basicVars[$pivotCol] = $pivotRow;
            
            $iterations++;
        }

        return ['status' => 'error', 'message' => 'به حداکثر تعداد تکرار مجاز رسید.'];
    }
}