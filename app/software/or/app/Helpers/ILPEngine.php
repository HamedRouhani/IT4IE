<?php
namespace App\Software\Or\Helpers;

class ILPEngine
{
    private static $maxNodes = 500;
    private static $tolerance = 1e-6;

    /**
     * حل مسئله ILP با الگوریتم Branch and Bound
     */
    public static function solve(
        array $c,
        array $A,
        array $b,
        array $constraints_types,
        array $integer_vars,
        string $sense = 'maximize'
    ): array {
        $n = count($c);
        $m = count($A);

        if (empty($integer_vars)) {
            return ['status' => 'error', 'message' => 'حداقل یک متغیر باید عدد صحیح باشد.'];
        }

        $bestSolution = null;
        $bestObjective = $sense === 'maximize' ? -INF : INF;
        $nodesExplored = 0;
        $branchingLog = [];

        // صف برای نگهداری زیرمسئله‌ها
        $queue = [];
        $queue[] = [
            'c' => $c,
            'A' => $A,
            'b' => $b,
            'constraints_types' => $constraints_types,
            'bounds' => array_fill(0, $n, [0, INF]), // [lower, upper] برای هر متغیر
            'depth' => 0,
            'path' => []
        ];

        while (!empty($queue) && $nodesExplored < self::$maxNodes) {
            $node = array_shift($queue);
            $nodesExplored++;

            // ✅ ساخت زیرمسئله LP با اعمال کران‌های شاخه‌وبکران
            $Ac = $node['A'];
            $bc = $node['b'];
            $tc = $node['constraints_types'];
            foreach ($node['bounds'] as $j => $bd) {
                if ($j >= $n) continue;
                [$lo, $hi] = $bd;
                if ($hi < INF) {          // کران بالا: x_j <= hi
                    $row = array_fill(0, $n, 0); $row[$j] = 1;
                    $Ac[] = $row; $bc[] = $hi; $tc[] = '<=';
                }
                if ($lo > 0) {            // کران پایین: x_j >= lo
                    $row = array_fill(0, $n, 0); $row[$j] = 1;
                    $Ac[] = $row; $bc[] = $lo; $tc[] = '>=';
                }
            }
            $lpResult = DualSimplexEngine::solve($node['c'], $Ac, $bc, $tc, $sense);

            if ($lpResult['status'] !== 'optimal') {
                $branchingLog[] = [
                    'node' => $nodesExplored,
                    'depth' => $node['depth'],
                    'status' => $lpResult['status'],
                    'message' => 'زیرمسئله ناموجه یا کران‌دار نیست - شاخه حذف شد (Pruned)'
                ];
                continue;
            }

            $lpObj = $lpResult['objective_value'];
            $lpSol = $lpResult['solution'];

            // بررسی شرط کران (Bounding)
            if ($sense === 'maximize' && $lpObj <= $bestObjective + self::$tolerance) {
                $branchingLog[] = [
                    'node' => $nodesExplored,
                    'depth' => $node['depth'],
                    'objective' => $lpObj,
                    'best_so_far' => $bestObjective,
                    'message' => 'کران ضعیف‌تر از جواب فعلی - شاخه حذف شد (Pruned by bound)'
                ];
                continue;
            }
            if ($sense === 'minimize' && $lpObj >= $bestObjective - self::$tolerance) {
                $branchingLog[] = [
                    'node' => $nodesExplored,
                    'depth' => $node['depth'],
                    'objective' => $lpObj,
                    'best_so_far' => $bestObjective,
                    'message' => 'کران ضعیف‌تر از جواب فعلی - شاخه حذف شد (Pruned by bound)'
                ];
                continue;
            }

            // بررسی عدد صحیح بودن
            $fractionalVar = -1;
            $fractionalValue = 0;
            $maxFractional = 0;

            foreach ($integer_vars as $varIdx) {
                if ($varIdx >= $n) continue;
                $val = $lpSol[$varIdx] ?? 0;
                $frac = abs($val - round($val));
                if ($frac > self::$tolerance && $frac > $maxFractional) {
                    $maxFractional = $frac;
                    $fractionalVar = $varIdx;
                    $fractionalValue = $val;
                }
            }

            if ($fractionalVar === -1) {
                // همه متغیرهای صحیح، عدد صحیح هستند - جواب بهینه ILP
                if (($sense === 'maximize' && $lpObj > $bestObjective) ||
                    ($sense === 'minimize' && $lpObj < $bestObjective)) {
                    $bestObjective = $lpObj;
                    $bestSolution = $lpSol;
                    $branchingLog[] = [
                        'node' => $nodesExplored,
                        'depth' => $node['depth'],
                        'objective' => $lpObj,
                        'message' => '✅ جواب صحیح جدید یافت شد (Updated best)'
                    ];
                }
                continue;
            }

            // شاخه‌سازی (Branching)
            $floorVal = floor($fractionalValue);
            $ceilVal = ceil($fractionalValue);

            // شاخه چپ: x <= floor
            $leftBounds = $node['bounds'];
            $leftBounds[$fractionalVar][1] = $floorVal;
            $queue[] = [
                'c' => $node['c'],
                'A' => $node['A'],
                'b' => $node['b'],
                'constraints_types' => $node['constraints_types'],
                'bounds' => $leftBounds,
                'depth' => $node['depth'] + 1,
                'path' => array_merge($node['path'], ["x{$fractionalVar} <= {$floorVal}"])
            ];

            // شاخه راست: x >= ceil
            $rightBounds = $node['bounds'];
            $rightBounds[$fractionalVar][0] = $ceilVal;
            $queue[] = [
                'c' => $node['c'],
                'A' => $node['A'],
                'b' => $node['b'],
                'constraints_types' => $node['constraints_types'],
                'bounds' => $rightBounds,
                'depth' => $node['depth'] + 1,
                'path' => array_merge($node['path'], ["x{$fractionalVar} >= {$ceilVal}"])
            ];

            $branchingLog[] = [
                'node' => $nodesExplored,
                'depth' => $node['depth'],
                'branch_var' => "x{$fractionalVar}",
                'branch_value' => $fractionalValue,
                'left' => "x{$fractionalVar} <= {$floorVal}",
                'right' => "x{$fractionalVar} >= {$ceilVal}",
                'message' => "شاخه‌سازی روی x{$fractionalVar} = {$fractionalValue}"
            ];
        }

        if ($bestSolution === null) {
            return [
                'status' => 'infeasible',
                'message' => 'مسئله ILP جواب موجه عدد صحیح ندارد (ناموجه یا به حداکثر گره‌ها رسید).',
                'nodes_explored' => $nodesExplored,
                'branching_log' => $branchingLog
            ];
        }

        // گرد کردن جواب نهایی
        $finalSolution = [];
        foreach ($bestSolution as $i => $val) {
            if (in_array($i, $integer_vars)) {
                $finalSolution[] = round($val);
            } else {
                $finalSolution[] = round($val, 4);
            }
        }

        return [
            'status' => 'optimal',
            'sense' => $sense,
            'objective_value' => round($bestObjective, 4),
            'solution' => $finalSolution,
            'lp_relaxation_value' => null,
            'nodes_explored' => $nodesExplored,
            'integer_vars' => $integer_vars,
            'branching_log' => $branchingLog,
            'interpretation' => self::generateInterpretation($finalSolution, $bestObjective, $nodesExplored, $integer_vars, $sense)
        ];
    }

    private static function generateInterpretation(array $sol, float $obj, int $nodes, array $intVars, string $sense): string
    {
        $out = [];
        $senseText = $sense === 'maximize' ? 'ماکزیمم' : 'مینیمم';
        $out[] = "✅ جواب بهینه عدد صحیح (ILP) با موفقیت یافت شد.";
        $out[] = "📊 مقدار تابع هدف ($senseText): " . number_format($obj, 4);
        $out[] = "🌳 تعداد گره‌های بررسی‌شده در درخت Branch & Bound: $nodes";

        $intDetails = [];
        foreach ($intVars as $idx) {
            $intDetails[] = "x" . ($idx + 1) . " = " . $sol[$idx];
        }
        $out[] = " مقادیر متغیرهای عدد صحیح: " . implode('، ', $intDetails);

        $out[] = "💡 تفاوت کلیدی با LP: در LP معمولی جواب‌ها کسری بودند، اما در ILP با اعمال قید عدد صحیح، جواب واقعی و قابل اجرا در دنیای واقعی (مثل تعداد محصول، تعداد کارگر) به‌دست آمد.";

        return implode("\n", $out);
    }
}