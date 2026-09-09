<?php
namespace App\Software\Or\Helpers;

/**
 * DualSimplexEngine - موتور حل برنامه‌ریزی خطی (نسخه اصلاح‌شده ۲.۰)
 * سیمپلکس استاندارد با روش Big-M و ماتریس اولیه صحیح
 */
class DualSimplexEngine
{
    private const MAX_ITER = 200;
    private const EPS = 1e-9;
    private const BIG_M = 1e7;

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

        // ── ۱) نرمال‌سازی: سمت راست نامنفی ──
        $rows = []; $types = []; $rhs = [];
        for ($i = 0; $i < $m; $i++) {
            $row = array_values(array_map('floatval', (array)$A[$i]));
            while (count($row) < $n) $row[] = 0.0;
            $row = array_slice($row, 0, $n);
            $bi = (float)($b[$i] ?? 0);
            $t = $constraints_types[$i] ?? '<=';
            if ($bi < 0) {
                $row = array_map(fn($v) => -$v, $row);
                $bi = -$bi;
                $t = ($t === '<=') ? '>=' : (($t === '>=') ? '<=' : '=');
            }
            $rows[] = $row; $types[] = $t; $rhs[] = $bi;
        }

        // ── ۲) شمارش ستون‌های slack/surplus و مصنوعی ──
        $numSlack = 0; $numArt = 0;
        foreach ($types as $t) {
            if ($t === '<=') $numSlack++;
            elseif ($t === '>=') { $numSlack++; $numArt++; }
            else { $numArt++; }
        }
        $totalVars = $n + $numSlack + $numArt;

        // ── ۳) ساخت tableau با ماتریس همانی صحیح ──
        $tableau = []; $basis = []; $artCols = []; $slackColOf = [];
        $s = 0; $a = 0;
        for ($i = 0; $i < $m; $i++) {
            $row = $rows[$i];
            for ($k = 0; $k < $numSlack + $numArt; $k++) $row[] = 0.0;
            $t = $types[$i];
            if ($t === '<=') {
                $row[$n + $s] = 1.0; $basis[$i] = $n + $s; $slackColOf[$i] = $n + $s; $s++;
            } elseif ($t === '>=') {
                $row[$n + $s] = -1.0; $slackColOf[$i] = $n + $s; $s++;
                $col = $n + $numSlack + $a; $row[$col] = 1.0; $basis[$i] = $col; $artCols[] = $col; $a++;
            } else {
                $col = $n + $numSlack + $a; $row[$col] = 1.0; $basis[$i] = $col; $artCols[] = $col; $a++;
            }
            $row[] = $rhs[$i];
            $tableau[] = $row;
        }

        // ── ۴) سطر هدف (ماکزیمم داخلی) با جریمه Big-M ──
        $cc = array_map('floatval', $c);
        if ($sense === 'minimize') $cc = array_map(fn($v) => -$v, $cc);
        $z = array_fill(0, $totalVars, 0.0);
        for ($j = 0; $j < $n; $j++) $z[$j] = -$cc[$j];
        foreach ($artCols as $col) $z[$col] = self::BIG_M;
        $z[] = 0.0;
        // صفرکردن ستون‌های پایه در سطر هدف
        for ($i = 0; $i < $m; $i++) {
            $col = $basis[$i];
            if (abs($z[$col]) > self::EPS) {
                $f = $z[$col];
                for ($j = 0; $j <= $totalVars; $j++) $z[$j] -= $f * $tableau[$i][$j];
            }
        }
        $tableau[] = $z;

        // ── ۵) تکرارهای سیمپلکس ──
        $iter = 0;
        while ($iter < self::MAX_ITER) {
            $iter++;
            $enter = -1; $minV = -1e-9;
            for ($j = 0; $j < $totalVars; $j++) {
                if ($tableau[$m][$j] < $minV) { $minV = $tableau[$m][$j]; $enter = $j; }
            }
            if ($enter === -1) break; // بهینه
            $leave = -1; $minRatio = INF;
            for ($i = 0; $i < $m; $i++) {
                if ($tableau[$i][$enter] > self::EPS) {
                    $r = $tableau[$i][$totalVars] / $tableau[$i][$enter];
                    if ($r < $minRatio - self::EPS) { $minRatio = $r; $leave = $i; }
                }
            }
            if ($leave === -1) {
                return ['status' => 'unbounded', 'message' => 'مسئله کران‌دار نیست (جواب بی‌نهایت).'];
            }
            $piv = $tableau[$leave][$enter];
            for ($j = 0; $j <= $totalVars; $j++) $tableau[$leave][$j] /= $piv;
            for ($i = 0; $i <= $m; $i++) {
                if ($i !== $leave) {
                    $f = $tableau[$i][$enter];
                    if (abs($f) > self::EPS) {
                        for ($j = 0; $j <= $totalVars; $j++) $tableau[$i][$j] -= $f * $tableau[$leave][$j];
                    }
                }
            }
            $basis[$leave] = $enter;
        }

        // ── ) استخراج نتیجه ──
        $sol = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $m; $i++) {
            if ($basis[$i] < $n) $sol[$basis[$i]] = $tableau[$i][$totalVars];
        }
        foreach ($artCols as $col) {
            for ($i = 0; $i < $m; $i++) {
                if ($basis[$i] === $col && $tableau[$i][$totalVars] > 1e-6) {
                    return ['status' => 'infeasible', 'message' => 'مسئله جواب موجه ندارد (infeasible).'];
                }
            }
        }
        $zInternal = $tableau[$m][$totalVars];
        $objective = ($sense === 'minimize') ? -$zInternal : $zInternal;

        $shadow = [];
        for ($i = 0; $i < $m; $i++) {
            if (isset($slackColOf[$i])) {
                $y = $tableau[$m][$slackColOf[$i]];
                $shadow[] = round(($sense === 'minimize') ? -$y : $y, 4);
            } else {
                $shadow[] = 0.0;
            }
        }
        $reduced = [];
        for ($j = 0; $j < $n; $j++) $reduced[] = round($tableau[$m][$j], 4);

        return [
            'status' => 'optimal',
            'sense' => $sense,
            'objective_value' => round($objective, 4),
            'solution' => array_map(fn($v) => round($v, 4), $sol),
            'shadow_prices' => $shadow,
            'reduced_costs' => $reduced,
            'iterations' => $iter,
            'interpretation' => self::generateInterpretation(array_map(fn($v) => round($v, 4), $sol), $shadow, $reduced, $objective, $sense),
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