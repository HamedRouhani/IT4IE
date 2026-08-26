<?php
namespace App\Software\Or\Helpers;

class SensitivityAnalyzer
{
    const EPSILON = 1e-9;

    public static function analyze(array $solverResult, array $c, array $b, array $types, string $objType): array
    {
        if ($solverResult['status'] !== 'optimal') {
            return ['status' => 'error', 'message' => 'تحلیل حساسیت فقط برای جواب‌های بهینه امکان‌پذیر است.'];
        }

        $tableau = $solverResult['final_tableau'];
        $basicVars = $solverResult['basic_vars'];
        $m = count($types);
        $n = count($c);
        $totalVars = count($tableau[0]) - 1;
        $objMultiplier = ($objType === 'maximize') ? 1.0 : -1.0;

        // ۱. محدوده تغییرات سمت راست (RHS Ranges)
        $rhsRanges = [];
        for ($i = 0; $i < $m; $i++) {
            $slackIdx = $n + $i;
            $allowableIncrease = INF;
            $allowableDecrease = INF;

            for ($row = 0; $row < $m; $row++) {
                $y = $tableau[$row][$slackIdx];
                $currentB = $tableau[$row][$totalVars];

                if (abs($y) > self::EPSILON) {
                    $ratio = $currentB / abs($y);
                    if ($y > 0) {
                        $allowableDecrease = min($allowableDecrease, $ratio);
                    } else {
                        $allowableIncrease = min($allowableIncrease, $ratio);
                    }
                }
            }

            $rhsRanges[] = [
                'constraint' => $i + 1,
                'current_rhs' => $b[$i],
                'shadow_price' => round($solverResult['shadow_prices'][$i] * $objMultiplier, 4),
                'allowable_increase' => ($allowableIncrease === INF) ? 'نامحدود' : round($allowableIncrease, 4),
                'allowable_decrease' => ($allowableDecrease === INF) ? 'نامحدود' : round($allowableDecrease, 4),
            ];
        }

        // ۲. محدوده تغییرات ضرایب تابع هدف (Objective Coefficient Ranges)
        $objRanges = [];
        for ($j = 0; $j < $n; $j++) {
            $isBasic = false;
            $basicRow = -1;
            foreach ($basicVars as $row => $varIdx) {
                if ($varIdx === $j) {
                    $isBasic = true;
                    $basicRow = $row;
                    break;
                }
            }

            $allowableIncrease = INF;
            $allowableDecrease = INF;

            if (!$isBasic) {
                $reducedCost = -$tableau[$m][$j] * $objMultiplier;
                if ($objType === 'maximize') {
                    $allowableIncrease = $reducedCost;
                } else {
                    $allowableDecrease = $reducedCost;
                }
            } else {
                for ($col = 0; $col < $totalVars; $col++) {
                    if (in_array($col, $basicVars) || $col >= $n) continue;

                    $y = $tableau[$basicRow][$col];
                    $currentObjRow = -$tableau[$m][$col] * $objMultiplier;

                    if ($objType === 'maximize') {
                        if ($y > self::EPSILON) $allowableIncrease = min($allowableIncrease, $currentObjRow / $y);
                        elseif ($y < -self::EPSILON) $allowableDecrease = min($allowableDecrease, -$currentObjRow / $y);
                    } else {
                        if ($y < -self::EPSILON) $allowableIncrease = min($allowableIncrease, -$currentObjRow / $y);
                        elseif ($y > self::EPSILON) $allowableDecrease = min($allowableDecrease, $currentObjRow / $y);
                    }
                }
            }

            $objRanges[] = [
                'variable' => 'x' . ($j + 1),
                'current_coeff' => $c[$j],
                'is_basic' => $isBasic,
                'allowable_increase' => ($allowableIncrease === INF) ? 'نامحدود' : round($allowableIncrease, 4),
                'allowable_decrease' => ($allowableDecrease === INF) ? 'نامحدود' : round($allowableDecrease, 4),
            ];
        }

        return [
            'status' => 'success',
            'shadow_prices' => $solverResult['shadow_prices'],
            'rhs_ranges' => $rhsRanges,
            'objective_ranges' => $objRanges,
        ];
    }
}