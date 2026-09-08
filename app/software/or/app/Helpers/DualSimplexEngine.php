<?php
namespace App\Software\Or\Helpers;

class DualSimplexEngine
{
    public static function solve(array $c, array $A, array $b, array $constraints_types = [], string $sense = 'maximize'): array
    {
        $m = count($A);
        $n = count($c);

        if ($m === 0 || $n === 0) {
            return ['status' => 'error', 'message' => 'ماتریس محدودیت‌ها یا ضرایب تابع هدف خالی است.'];
        }

        if (empty($constraints_types)) {
            $constraints_types = array_fill(0, $m, '<=');
        }

        $tableau = [];
        $num_slack = 0;
        $num_artificial = 0;
        
        for ($i = 0; $i < $m; $i++) {
            $row = $A[$i];
            $type = $constraints_types[$i] ?? '<=';
            
            if ($type === '<=') {
                $row[] = 1;
                $num_slack++;
            } elseif ($type === '>=') {
                $row[] = -1;
                $row[] = 1;
                $num_slack++;
                $num_artificial++;
            } elseif ($type === '=') {
                $row[] = 1;
                $num_artificial++;
            }
            $row[] = $b[$i];
            $tableau[] = $row;
        }

        $isMinimize = ($sense === 'minimize');
        $objCoeffs = array_map(fn($val) => -$val, $c);
        $zRow = array_merge($objCoeffs, array_fill(0, $num_slack + $num_artificial, 0), [0]);
        $tableau[] = $zRow;

        $numCols = $n + $num_slack + $num_artificial + 1;
        $maxIter = 100;
        $iter = 0;

        while ($iter < $maxIter) {
            $iter++;
            $enteringCol = -1;
            $minZ = 0;
            for ($j = 0; $j < $n + $num_slack + $num_artificial; $j++) {
                if ($tableau[$m][$j] < $minZ) {
                    $minZ = $tableau[$m][$j];
                    $enteringCol = $j;
                }
            }

            if ($enteringCol === -1) {
                return self::extractResults($tableau, $c, $n, $m, $sense, $isMinimize, $num_slack, $num_artificial);
            }

            $leavingRow = -1;
            $minRatio = INF;
            for ($i = 0; $i < $m; $i++) {
                if ($tableau[$i][$enteringCol] > 1e-9) {
                    $ratio = $tableau[$i][$numCols - 1] / $tableau[$i][$enteringCol];
                    if ($ratio < $minRatio) {
                        $minRatio = $ratio;
                        $leavingRow = $i;
                    }
                }
            }

            if ($leavingRow === -1) {
                return ['status' => 'unbounded', 'message' => 'مسئله کران‌دار نیست (جواب بی‌نهایت).'];
            }

            $pivot = $tableau[$leavingRow][$enteringCol];
            for ($j = 0; $j < $numCols; $j++) {
                $tableau[$leavingRow][$j] /= $pivot;
            }

            for ($i = 0; $i <= $m; $i++) {
                if ($i !== $leavingRow) {
                    $factor = $tableau[$i][$enteringCol];
                    for ($j = 0; $j < $numCols; $j++) {
                        $tableau[$i][$j] -= $factor * $tableau[$leavingRow][$j];
                    }
                }
            }
        }

        return ['status' => 'error', 'message' => 'به حداکثر تعداد تکرار مجاز رسید.'];
    }

    private static function extractResults(array $tableau, array $c, int $n, int $m, string $sense, bool $isMinimize, int $num_slack, int $num_artificial): array
    {
        $lastRow = $tableau[$m];
        $rawObj = $lastRow[count($lastRow) - 1];
        $objectiveValue = $isMinimize ? -$rawObj : $rawObj;

        $shadowPrices = [];
        for ($j = 0; $j < $m; $j++) {
            $colIndex = $n + $j;
            $shadowPrices[] = round(abs($lastRow[$colIndex] ?? 0), 4);
        }

        $reducedCosts = [];
        for ($j = 0; $j < $n; $j++) {
            $reducedCosts[] = round(abs($lastRow[$j]), 4);
        }

        $solution = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $m; $i++) {
            $basicCol = -1;
            for ($j = 0; $j < $n; $j++) {
                if (abs($tableau[$i][$j] - 1) < 1e-6) {
                    $isBasic = true;
                    for ($k = 0; $k < $m; $k++) {
                        if ($k !== $i && abs($tableau[$k][$j]) > 1e-6) {
                            $isBasic = false;
                            break;
                        }
                    }
                    if ($isBasic) {
                        $basicCol = $j;
                        break;
                    }
                }
            }
            if ($basicCol !== -1) {
                $solution[$basicCol] = round($tableau[$i][count($tableau[$i]) - 1], 4);
            }
        }

        return [
            'status' => 'optimal',
            'sense' => $sense,
            'objective_value' => round($objectiveValue, 4),
            'solution' => $solution,
            'shadow_prices' => $shadowPrices,
            'reduced_costs' => $reducedCosts,
            'iterations' => 0,
            'interpretation' => self::generateInterpretation($solution, $shadowPrices, $reducedCosts, $objectiveValue, $sense)
        ];
    }

    private static function generateInterpretation(array $sol, array $sp, array $rc, float $obj, string $sense): string
    {
        $out = [];
        $senseText = $sense === 'maximize' ? 'ماکزیمم' : 'مینیمم';
        $out[] = "✅ جواب بهینه با موفقیت یافت شد. مقدار تابع هدف ($senseText): " . number_format($obj, 4);
        
        $activeResources = [];
        foreach ($sp as $i => $price) {
            if ($price > 0.001) {
                $activeResources[] = "منبع " . ($i + 1) . " (قیمت سایه‌ای: " . number_format($price, 4) . ")";
            }
        }
        if (!empty($activeResources)) {
            $out[] = "💡 منابع محدودکننده (با قیمت سایه‌ای مثبت): " . implode('، ', $activeResources) . ". افزایش یک واحدی این منابع، تابع هدف را به این مقدار بهبود می‌بخشد.";
        } else {
            $out[] = "💡 هیچ منبعی کاملاً مصرف نشده است (قیمت‌های سایه‌ای صفر هستند).";
        }

        $bindingVars = [];
        foreach ($rc as $i => $cost) {
            if ($cost > 0.001 && abs($sol[$i]) < 1e-6) {
                $bindingVars[] = "متغیر " . ($i + 1) . " (هزینه کاهش‌یافته: " . number_format($cost, 4) . ")";
            }
        }
        if (!empty($bindingVars)) {
            $out[] = "⚠️ متغیرهای غیرپایه (تولید نشده): " . implode('، ', $bindingVars) . ". ضریب تابع هدف این متغیرها باید به این مقدار بهبود یابد تا وارد پایه شوند.";
        }

        return implode("\n", $out);
    }
}