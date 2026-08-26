<?php
namespace App\Software\Or\Helpers;

class SimplexSolver
{
    const EPSILON = 1e-9;

    /**
     * حل مسئله برنامه‌ریزی خطی با روش سیمپلکس دو مرحله‌ای
     * 
     * @param array $c ضرایب تابع هدف (به ازای متغیرهای اصلی)
     * @param array $A ماتریس ضرایب محدودیت‌ها
     * @param array $b بردار سمت راست محدودیت‌ها
     * @param array $types انواع محدودیت‌ها ('<=', '>=', '=')
     * @param string $objType نوع تابع هدف ('maximize' یا 'minimize')
     * @return array نتیجه حل شامل وضعیت، جواب بهینه، جدول نهایی و قیمت‌های سایه‌ای
     */
    public static function solve(array $c, array $A, array $b, array $types, string $objType = 'maximize'): array
    {
        $m = count($A); 
        $n = count($c); 

        if ($m === 0 || $n === 0) {
            return ['status' => 'error', 'message' => 'مدل وارد شده نامعتبر است.'];
        }

        $numSlack = $m;
        $numArtificial = 0;
        foreach ($types as $type) {
            if ($type === '>=' || $type === '=') $numArtificial++;
        }

        $totalVars = $n + $numSlack + $numArtificial;
        $tableau = array_fill(0, $m + 1, array_fill(0, $totalVars + 1, 0.0));
        $basicVars = [];
        $artificialIndices = [];
        $colIdx = $n;

        // ۱. ساختاردهی اولیه جدول (افزودن متغیرهای کمکی)
        foreach ($types as $i => $type) {
            if ($type === '<=') {
                $tableau[$i][$colIdx] = 1.0;
                $basicVars[$i] = $colIdx;
                $colIdx++;
            } elseif ($type === '>=') {
                $tableau[$i][$colIdx] = -1.0; // مازاد
                $colIdx++;
                $tableau[$i][$colIdx] = 1.0;  // مصنوعی
                $basicVars[$i] = $colIdx;
                $artificialIndices[] = $colIdx;
                $colIdx++;
            } elseif ($type === '=') {
                $tableau[$i][$colIdx] = 1.0;  // مصنوعی
                $basicVars[$i] = $colIdx;
                $artificialIndices[] = $colIdx;
                $colIdx++;
            }
        }

        // پر کردن ضرایب اصلی و RHS
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $tableau[$i][$j] = (float)$A[$i][$j];
            }
            $tableau[$i][$totalVars] = (float)$b[$i];
            
            // استانداردسازی: اگر RHS منفی است، کل سطر قرینه می‌شود
            if ($tableau[$i][$totalVars] < 0) {
                for ($j = 0; $j <= $totalVars; $j++) {
                    $tableau[$i][$j] *= -1.0;
                }
            }
        }

        // ۲. تنظیم سطر هدف فاز ۱: Minimize W = sum(artificial)
        // معادل Maximize -W = -sum(artificial)
        foreach ($artificialIndices as $artIdx) {
            $row = array_search($artIdx, $basicVars);
            if ($row !== false) {
                for ($j = 0; $j <= $totalVars; $j++) {
                    $tableau[$m][$j] -= $tableau[$row][$j];
                }
            }
        }

        // ۳. اجرای فاز ۱
        $phase1 = self::simplexIterations($tableau, $basicVars, $totalVars, $m, true, $artificialIndices);
        if ($phase1['status'] === 'infeasible') {
            return ['status' => 'infeasible', 'message' => 'مسئله هیچ جواب موجهی ندارد (ناهمخوانی محدودیت‌ها).'];
        }

        // ۴. تنظیم جدول برای فاز ۲ (تابع هدف اصلی)
        $tableau2 = array_fill(0, $m + 1, array_fill(0, $totalVars + 1, 0.0));
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j <= $totalVars; $j++) {
                $tableau2[$i][$j] = $phase1['tableau'][$i][$j];
            }
        }
        $basicVars2 = $phase1['basicVars'];
        $objMultiplier = ($objType === 'maximize') ? 1.0 : -1.0;

        for ($j = 0; $j < $n; $j++) {
            $tableau2[$m][$j] = -$objMultiplier * (float)$c[$j];
        }
        
        // صفر کردن ضرایب متغیرهای پایه در سطر هدف
        for ($i = 0; $i < $m; $i++) {
            $varIdx = $basicVars2[$i];
            if ($varIdx < $n) {
                $coeff = -$objMultiplier * (float)$c[$varIdx];
                for ($j = 0; $j <= $totalVars; $j++) {
                    $tableau2[$m][$j] -= $coeff * $tableau2[$i][$j];
                }
            }
        }

        // ۵. اجرای فاز ۲
        $phase2 = self::simplexIterations($tableau2, $basicVars2, $totalVars, $m, false, []);
        if ($phase2['status'] === 'unbounded') {
            return ['status' => 'unbounded', 'message' => 'مسئله نامحدود است (جواب بهینه به سمت بی‌نهایت میل می‌کند).'];
        }

        // ۶. استخراج نتایج
        $solution = array_fill(0, $n, 0.0);
        foreach ($basicVars2 as $i => $varIdx) {
            if ($varIdx < $n) {
                $solution[$varIdx] = $phase2['tableau'][$i][$totalVars];
            }
        }

        $optimalValue = $phase2['tableau'][$m][$totalVars] * $objMultiplier;

        // محاسبه قیمت‌های سایه‌ای (Shadow Prices)
        $shadowPrices = array_fill(0, $m, 0.0);
        for ($i = 0; $i < $m; $i++) {
            $slackIdx = $n + $i;
            $shadowPrices[$i] = $tableau2[$m][$slackIdx] * $objMultiplier;
        }

        return [
            'status' => 'optimal',
            'message' => 'جواب بهینه با موفقیت یافت شد.',
            'solution' => $solution,
            'optimal_value' => round($optimalValue, 4),
            'final_tableau' => $phase2['tableau'],
            'basic_vars' => $basicVars2,
            'shadow_prices' => $shadowPrices,
            'iterations' => $phase1['iterations'] + $phase2['iterations'],
        ];
    }

    private static function simplexIterations(array &$tableau, array &$basicVars, int $totalVars, int $m, bool $isPhase1, array $artificialIndices): array
    {
        $iterations = 0;
        $maxIter = 150;

        while ($iterations < $maxIter) {
            $iterations++;
            $enterCol = -1;
            $minVal = 0.0;

            // یافتن ستون ورودی (منفی‌ترین مقدار در سطر هدف برای Maximize)
            for ($j = 0; $j < $totalVars; $j++) {
                if ($isPhase1 && in_array($j, $artificialIndices)) continue;
                if ($tableau[$m][$j] < $minVal) {
                    $minVal = $tableau[$m][$j];
                    $enterCol = $j;
                }
            }

            if ($enterCol === -1) {
                if ($isPhase1 && abs($tableau[$m][$totalVars]) > self::EPSILON) {
                    return ['status' => 'infeasible', 'iterations' => $iterations, 'tableau' => $tableau, 'basicVars' => $basicVars];
                }
                return ['status' => 'optimal', 'iterations' => $iterations, 'tableau' => $tableau, 'basicVars' => $basicVars];
            }

            // یافتن سطر خروجی (حداقل نسبت مثبت)
            $leaveRow = -1;
            $minRatio = PHP_FLOAT_MAX;

            for ($i = 0; $i < $m; $i++) {
                if ($tableau[$i][$enterCol] > self::EPSILON) {
                    $ratio = $tableau[$i][$totalVars] / $tableau[$i][$enterCol];
                    if ($ratio < $minRatio) {
                        $minRatio = $ratio;
                        $leaveRow = $i;
                    }
                }
            }

            if ($leaveRow === -1) {
                return ['status' => 'unbounded', 'iterations' => $iterations, 'tableau' => $tableau, 'basicVars' => $basicVars];
            }

            // عملیات سطر (Pivot)
            $pivot = $tableau[$leaveRow][$enterCol];
            for ($j = 0; $j <= $totalVars; $j++) {
                $tableau[$leaveRow][$j] /= $pivot;
            }

            for ($i = 0; $i <= $m; $i++) {
                if ($i !== $leaveRow) {
                    $factor = $tableau[$i][$enterCol];
                    for ($j = 0; $j <= $totalVars; $j++) {
                        $tableau[$i][$j] -= $factor * $tableau[$leaveRow][$j];
                    }
                }
            }
            $basicVars[$leaveRow] = $enterCol;
        }

        return ['status' => 'max_iter_reached', 'iterations' => $iterations, 'tableau' => $tableau, 'basicVars' => $basicVars];
    }
}