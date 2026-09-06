<?php
namespace App\Software\Statlab\Helpers;

use App\Software\Statlab\Helpers\DistributionLibrary as DL;

/**
 * RegressionEngine - همبستگی و رگرسیون خطی ساده/چندگانه
 */
class RegressionEngine
{
    // ═══ همبستگی پیرسون + اجزای کمکی ═══
    public static function pearsonParts(array $x, array $y): array
    {
        $n = count($x);
        $mx = StatEngine::mean($x);
        $my = StatEngine::mean($y);
        $sxy = 0; $sxx = 0; $syy = 0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $mx; $dy = $y[$i] - $my;
            $sxy += $dx * $dy; $sxx += $dx * $dx; $syy += $dy * $dy;
        }
        $r = ($sxx > 0 && $syy > 0) ? max(-1, min(1, $sxy / sqrt($sxx * $syy))) : 0;
        return ['r' => $r, 'sxy' => $sxy, 'sxx' => $sxx, 'syy' => $syy, 'n' => $n, 'mx' => $mx, 'my' => $my];
    }

    // ═══ رتبه‌ها (با میانگین برای تساوی‌ها) ═══
    public static function ranks(array $a): array
    {
        $n = count($a);
        $idx = range(0, $n - 1);
        usort($idx, fn($p, $q) => $a[$p] <=> $a[$q]);
        $ranks = array_fill(0, $n, 0);
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && $a[$idx[$j + 1]] == $a[$idx[$i]]) $j++;
            $avg = ($i + $j + 2) / 2;
            for ($k = $i; $k <= $j; $k++) $ranks[$idx[$k]] = $avg;
            $i = $j + 1;
        }
        return $ranks;
    }

    // ═══ آزمون همبستگی (پیرسون + اسپیرمن) ═══
    public static function correlation(array $x, array $y, float $alpha): array
    {
        $p = self::pearsonParts($x, $y);
        $n = $p['n']; $r = $p['r']; $df = $n - 2;

        $t = (1 - $r * $r) > 1e-12 ? $r * sqrt($df / (1 - $r * $r)) : ($r > 0 ? INF : -INF);
        $pval = is_finite($t) ? 2 * (1 - DL::cdf('t', ['df' => $df], abs($t))) : 0;

        $rho = self::pearsonParts(self::ranks($x), self::ranks($y))['r'];
        $ts = (1 - $rho * $rho) > 1e-12 ? $rho * sqrt($df / (1 - $rho * $rho)) : ($rho > 0 ? INF : -INF);
        $pvalS = is_finite($ts) ? 2 * (1 - DL::cdf('t', ['df' => $df], abs($ts))) : 0;

        $strength = self::strengthLabel($r);

        return [
            'n' => $n, 'df' => $df, 'alpha' => $alpha,
            'pearson_r' => round($r, 6), 'pearson_t' => round($t, 4), 'pearson_p' => round($pval, 6),
            'spearman_rho' => round($rho, 6), 'spearman_t' => round($ts, 4), 'spearman_p' => round($pvalS, 6),
            'strength' => $strength,
            'conclusion' => $pval < $alpha ? 'reject' : 'fail',
            'interpretation_fa' => "همبستگی پیرسون r=" . round($r, 3) . " ($strength) و اسپیرمن ρ=" . round($rho, 3) . ".\n" .
                ($pval < $alpha
                    ? "✅ چون p-value (" . round($pval, 5) . ") < α ($alpha) است، همبستگی معنادار است."
                    : "❌ چون p-value (" . round($pval, 5) . ") ≥ α ($alpha) است، همبستگی معنادار نیست."),
        ];
    }

    private static function strengthLabel(float $r): string
    {
        $a = abs($r);
        $dir = $r >= 0 ? 'مثبت' : 'منفی';
        if ($a >= 0.8) return "قوی $dir";
        if ($a >= 0.5) return "متوسط $dir";
        if ($a >= 0.3) return "ضعیف $dir";
        return 'ناچیز';
    }

    // ═══ رگرسیون خطی ساده ═══
    public static function simple(array $x, array $y, float $alpha): array
    {
        $p = self::pearsonParts($x, $y);
        $n = $p['n']; $df = $n - 2;
        $b1 = $p['sxx'] > 0 ? $p['sxy'] / $p['sxx'] : 0;
        $b0 = $p['my'] - $b1 * $p['mx'];

        $sse = 0;
        for ($i = 0; $i < $n; $i++) {
            $e = $y[$i] - ($b0 + $b1 * $x[$i]);
            $sse += $e * $e;
        }
        $sst = $p['syy'];
        $r2 = $sst > 0 ? max(0, 1 - $sse / $sst) : 0;
        $mse = $df > 0 ? $sse / $df : 0;
        $se = sqrt($mse);
        $seB1 = $p['sxx'] > 0 ? sqrt($mse / $p['sxx']) : 0;
        $seB0 = sqrt($mse * (1 / $n + ($p['mx'] ** 2) / max($p['sxx'], 1e-12)));
        $tB1 = $seB1 > 0 ? $b1 / $seB1 : 0;
        $tB0 = $seB0 > 0 ? $b0 / $seB0 : 0;
        $pB1 = 2 * (1 - DL::cdf('t', ['df' => $df], abs($tB1)));
        $pB0 = 2 * (1 - DL::cdf('t', ['df' => $df], abs($tB0)));
        $tc = DL::quantile('t', ['df' => $df], 1 - $alpha / 2);
        $F = $tB1 ** 2;
        $pF = 1 - DL::cdf('f', ['d1' => 1, 'd2' => $df], $F);
        $r = $p['r'];

        return [
            'n' => $n, 'df' => $df, 'alpha' => $alpha,
            'intercept' => round($b0, 6), 'slope' => round($b1, 6),
            'se_intercept' => round($seB0, 6), 'se_slope' => round($seB1, 6),
            't_intercept' => round($tB0, 4), 't_slope' => round($tB1, 4),
            'p_intercept' => round($pB0, 6), 'p_slope' => round($pB1, 6),
            'r' => round($r, 6), 'r2' => round($r2, 6),
            'se' => round($se, 6), 'mse' => round($mse, 6),
            'F' => round($F, 4), 'p_F' => round($pF, 6),
            'ci_slope' => [round($b1 - $tc * $seB1, 4), round($b1 + $tc * $seB1, 4)],
            'conclusion' => $pB1 < $alpha ? 'reject' : 'fail',
            'points' => array_map(fn($i) => ['x' => $x[$i], 'y' => $y[$i]], range(0, $n - 1)),
            'interpretation_fa' => "مدل: ŷ = " . round($b0, 3) . " + " . round($b1, 3) . "x\n" .
                "R²=" . round($r2 * 100, 1) . "% یعنی " . round($r2 * 100, 1) . "% از تغییرات Y توسط X توضیح داده می‌شود.\n" .
                ($pB1 < $alpha
                    ? "✅ شیب رگرسیون معنادار است (p=" . round($pB1, 5) . ")؛ رابطه خطی معنادار است."
                    : "❌ شیب رگرسیون معنادار نیست (p=" . round($pB1, 5) . ")."),
        ];
    }

    // ═══ رگرسیون چندگانه ═══
    public static function multiple(array $Y, array $Xs, float $alpha): array
    {
        $n = count($Y);
        $k = count($Xs);
        $p = $k + 1;

        $X = [];
        for ($i = 0; $i < $n; $i++) {
            $row = [1];
            for ($j = 0; $j < $k; $j++) $row[] = $Xs[$j][$i];
            $X[] = $row;
        }

        $XtX = array_fill(0, $p, array_fill(0, $p, 0));
        $XtY = array_fill(0, $p, 0);
        for ($i = 0; $i < $n; $i++) {
            for ($a = 0; $a < $p; $a++) {
                $XtY[$a] += $X[$i][$a] * $Y[$i];
                for ($b = 0; $b < $p; $b++) $XtX[$a][$b] += $X[$i][$a] * $X[$i][$b];
            }
        }

        $inv = self::invertMatrix($XtX);
        if ($inv === null) throw new \Exception('ماتریس X\'X معکوس‌پذیر نیست؛ احتمال هم‌خطی چندمتغیری وجود دارد.');

        $beta = [];
        for ($a = 0; $a < $p; $a++) {
            $s = 0;
            for ($b = 0; $b < $p; $b++) $s += $inv[$a][$b] * $XtY[$b];
            $beta[] = $s;
        }

        $my = StatEngine::mean($Y);
        $sse = 0; $sst = 0;
        for ($i = 0; $i < $n; $i++) {
            $yhat = 0;
            for ($a = 0; $a < $p; $a++) $yhat += $X[$i][$a] * $beta[$a];
            $sse += ($Y[$i] - $yhat) ** 2;
            $sst += ($Y[$i] - $my) ** 2;
        }
        $r2 = $sst > 0 ? max(0, 1 - $sse / $sst) : 0;
        $dfE = $n - $p;
        $mse = $dfE > 0 ? $sse / $dfE : 0;
        $adjR2 = $dfE > 0 ? 1 - (1 - $r2) * ($n - 1) / $dfE : 0;
        $F = ($mse > 0 && $k > 0) ? (($sst - $sse) / $k) / $mse : 0;
        $pF = 1 - DL::cdf('f', ['d1' => $k, 'd2' => max($dfE, 1)], $F);

        $coefs = [];
        for ($a = 0; $a < $p; $a++) {
            $seA = sqrt(max($inv[$a][$a] * $mse, 0));
            $tA = $seA > 0 ? $beta[$a] / $seA : 0;
            $pA = $dfE > 0 ? 2 * (1 - DL::cdf('t', ['df' => $dfE], abs($tA))) : 1;
            $coefs[] = [
                'name' => $a === 0 ? 'عرض از مبدأ' : 'X' . $a,
                'beta' => round($beta[$a], 6),
                'se' => round($seA, 6),
                't' => round($tA, 4),
                'p' => round($pA, 6),
                'sig' => $pA < $alpha,
            ];
        }

        return [
            'n' => $n, 'k' => $k, 'df' => $dfE, 'alpha' => $alpha,
            'r2' => round($r2, 6), 'adj_r2' => round($adjR2, 6),
            'F' => round($F, 4), 'p_F' => round($pF, 6),
            'se' => round(sqrt($mse), 6),
            'coefficients' => $coefs,
            'conclusion' => $pF < $alpha ? 'reject' : 'fail',
            'interpretation_fa' => "مدل چندگانه با $k متغیر پیشبین: R²=" . round($r2 * 100, 1) . "% و R² تعدیل‌شده=" . round($adjR2 * 100, 1) . "%.\n" .
                ($pF < $alpha
                    ? "✅ مدل به‌طور کلی معنادار است (F=" . round($F, 2) . ", p=" . round($pF, 5) . ")."
                    : "❌ مدل به‌طور کلی معنادار نیست (p=" . round($pF, 5) . ")."),
        ];
    }

    // ═══ معکوس ماتریس (Gauss-Jordan با pivot) ═══
    public static function invertMatrix(array $A): ?array
    {
        $n = count($A);
        $M = [];
        for ($i = 0; $i < $n; $i++) {
            $M[$i] = array_merge(array_values($A[$i]), array_fill(0, $n, 0));
            $M[$i][$n + $i] = 1;
        }
        for ($col = 0; $col < $n; $col++) {
            $pivot = $col;
            for ($r = $col + 1; $r < $n; $r++) if (abs($M[$r][$col]) > abs($M[$pivot][$col])) $pivot = $r;
            if (abs($M[$pivot][$col]) < 1e-12) return null;
            if ($pivot !== $col) { $tmp = $M[$col]; $M[$col] = $M[$pivot]; $M[$pivot] = $tmp; }
            $d = $M[$col][$col];
            for ($j = 0; $j < 2 * $n; $j++) $M[$col][$j] /= $d;
            for ($r = 0; $r < $n; $r++) {
                if ($r === $col) continue;
                $f = $M[$r][$col];
                if ($f != 0) for ($j = 0; $j < 2 * $n; $j++) $M[$r][$j] -= $f * $M[$col][$j];
            }
        }
        $inv = [];
        for ($i = 0; $i < $n; $i++) $inv[$i] = array_slice($M[$i], $n);
        return $inv;
    }
}