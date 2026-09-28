<?php
namespace App\Software\Statlab\Helpers;

use App\Software\Statlab\Helpers\DistributionLibrary as DL;

/**
 * HypothesisTester - موتور آزمون‌های فرض پارامتری و ناپارامتری
 */
class HypothesisTester
{
    // ═══ فهرست آزمون‌ها برای UI ═══
    public static function list(): array
    {
        return [
            'one_sample_t' => ['name_fa' => 'آزمون t میانگین یک نمونه', 'group' => 'parametric'],
            'two_sample_t' => ['name_fa' => 'آزمون t دو نمونه مستقل (Welch)', 'group' => 'parametric'],
            'paired_t'     => ['name_fa' => 'آزمون t نمونه‌های جفتی (Paired)', 'group' => 'parametric'],
            'one_prop_z'   => ['name_fa' => 'آزمون Z نسبت یک جامعه', 'group' => 'parametric'],
            'f_var'        => ['name_fa' => 'آزمون F برابری دو واریانس', 'group' => 'parametric'],
            'chi2_gof'     => ['name_fa' => 'کای‌دو نیکویی برازش', 'group' => 'nonparametric'],
            'chi2_indep'   => ['name_fa' => 'کای‌دو استقلال (جدول توافقی)', 'group' => 'nonparametric'],
            'mann_whitney' => ['name_fa' => 'آزمون U من-ویتنی (ناپارامتری)', 'group' => 'nonparametric'],
        ];
    }

    // ═══ اجرای آزمون ═══
    public static function run(string $test, array $in, float $alpha): array
    {
        if (!is_finite($alpha) || $alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('سطح معناداری باید بین صفر و یک باشد.');
        }
        $alt = $in['alternative'] ?? 'two';
        if (!in_array($alt, ['two', 'less', 'greater'], true)) {
            throw new \InvalidArgumentException('جهت آزمون معتبر نیست.');
        }
        switch ($test) {
            case 'one_sample_t': return self::oneSampleT($in['data1'], (float)$in['mu0'], $alt, $alpha);
            case 'two_sample_t': return self::twoSampleT($in['data1'], $in['data2'], $alt, $alpha);
            case 'paired_t':     return self::pairedT($in['data1'], $in['data2'], $alt, $alpha);
            case 'one_prop_z':   return self::onePropZ((int)$in['x'], (int)$in['n'], (float)$in['p0'], $alt, $alpha);
            case 'f_var':        return self::fTest($in['data1'], $in['data2'], $alpha);
            case 'chi2_gof':     return self::chi2Gof($in['observed'], $in['expected'] ?? [], $alpha);
            case 'chi2_indep':   return self::chi2Indep($in['matrix'], $alpha);
            case 'mann_whitney': return self::mannWhitney($in['data1'], $in['data2'], $alt, $alpha);
        }
        throw new \Exception('آزمون نامعتبر است.');
    }

    // ─────────── t یک نمونه ───────────
    private static function oneSampleT(array $x, float $mu0, string $alt, float $alpha): array
    {
        $n = count($x);
        if ($n < 2) throw new \InvalidArgumentException('برای آزمون t حداقل دو مشاهده لازم است.');
        $m = StatEngine::mean($x);
        $s = StatEngine::std($x);
        $se = $s / sqrt($n);
        if ($se <= 0) throw new \InvalidArgumentException('با واریانس صفر، آزمون t تعریف نشده است.');
        $t = ($m - $mu0) / $se;
        $df = $n - 1;
        $p = self::tailP(DL::cdf('t', ['df' => $df], $t), $alt);
        $tc = DL::quantile('t', ['df' => $df], 1 - $alpha / 2);

        return self::wrap('t', $t, $df, $p, $alpha, ['±' . round($tc, 4)], [
            'n' => $n, 'mean' => round($m, 4), 'std' => round($s, 4),
            'ci' => [round($m - $tc * $se, 4), round($m + $tc * $se, 4)],
            'effect_size' => round(($m - $mu0) / $s, 4), // Cohen's d
        ], [self::normalityWarning($x, 'داده‌های نمونه')],
        "میانگین نمونه " . round($m, 3) . " با مقدار فرضی $mu0 مقایسه شد.");
    }

    // ─────────── t دو نمونه مستقل (Welch) ───────────
    private static function twoSampleT(array $a, array $b, string $alt, float $alpha): array
    {
        $n1 = count($a); $n2 = count($b);
        $m1 = StatEngine::mean($a); $m2 = StatEngine::mean($b);
        $v1 = StatEngine::variance($a); $v2 = StatEngine::variance($b);
        $se = sqrt($v1 / $n1 + $v2 / $n2);
        if ($se <= 0) throw new \InvalidArgumentException('با واریانس صفر در هر دو گروه، آزمون Welch تعریف نشده است.');
        $t = ($m1 - $m2) / $se;
        $df = (($v1 / $n1 + $v2 / $n2) ** 2) /
             ((($v1 / $n1) ** 2) / ($n1 - 1) + (($v2 / $n2) ** 2) / ($n2 - 1));
        if (!is_finite($df) || $df <= 0) throw new \InvalidArgumentException('درجهٔ آزادی آزمون Welch معتبر نیست.');
        $p = self::tailP(DL::cdf('t', ['df' => $df], $t), $alt);
        $tc = DL::quantile('t', ['df' => $df], 1 - $alpha / 2);
        $sp = sqrt((($n1 - 1) * $v1 + ($n2 - 1) * $v2) / ($n1 + $n2 - 2));

        return self::wrap('t', $t, $df, $p, $alpha, ['±' . round($tc, 4)], [
            'n1' => $n1, 'n2' => $n2, 'mean1' => round($m1, 4), 'mean2' => round($m2, 4),
            'ci' => [round($m1 - $m2 - $tc * $se, 4), round($m1 - $m2 + $tc * $se, 4)],
            'effect_size' => $sp > 0 ? round(($m1 - $m2) / $sp, 4) : null,
        ], [self::normalityWarning($a, 'نمونه ۱'), self::normalityWarning($b, 'نمونه ۲')],
        "میانگین دو گروه مستقل (" . round($m1, 3) . " در برابر " . round($m2, 3) . ") مقایسه شد.");
    }

    // ─────────── t جفتی ───────────
    private static function pairedT(array $a, array $b, string $alt, float $alpha): array
    {
        $n = count($a);
        if ($n < 2 || $n !== count($b)) throw new \InvalidArgumentException('داده‌های آزمون جفتی باید هم‌اندازه و دست‌کم دو مشاهده باشند.');
        $d = [];
        for ($i = 0; $i < $n; $i++) $d[] = $a[$i] - $b[$i];
        $md = StatEngine::mean($d);
        $sd = StatEngine::std($d);
        $se = $sd / sqrt($n);
        if ($se <= 0) throw new \InvalidArgumentException('اختلاف‌های جفتی واریانس صفر دارند؛ آزمون t تعریف نشده است.');
        $t = $md / $se;
        $df = $n - 1;
        $p = self::tailP(DL::cdf('t', ['df' => $df], $t), $alt);
        $tc = DL::quantile('t', ['df' => $df], 1 - $alpha / 2);

        return self::wrap('t', $t, $df, $p, $alpha, ['±' . round($tc, 4)], [
            'n' => $n, 'mean_diff' => round($md, 4), 'std_diff' => round($sd, 4),
            'ci' => [round($md - $tc * $se, 4), round($md + $tc * $se, 4)],
        ], [self::normalityWarning($d, 'اختلاف‌های جفتی')],
        "اختلاف میانگین جفت‌ها " . round($md, 3) . " است.");
    }

    // ─────────── Z نسبت ───────────
    private static function onePropZ(int $x, int $n, float $p0, string $alt, float $alpha): array
    {
        if ($n <= 0 || $x < 0 || $x > $n || !is_finite($p0) || $p0 <= 0 || $p0 >= 1) {
            throw new \InvalidArgumentException('برای آزمون نسبت، ۰≤x≤n و ۰<p₀<۱ لازم است.');
        }
        $ph = $x / $n;
        $z = ($ph - $p0) / sqrt($p0 * (1 - $p0) / $n);
        $p = self::tailP(DL::cdf('normal', ['mu' => 0, 'sigma' => 1], $z), $alt);
        $zc = DL::quantile('normal', ['mu' => 0, 'sigma' => 1], 1 - $alpha / 2);

        $warnings = [];
        if ($n * $p0 < 5 || $n * (1 - $p0) < 5) {
            $warnings[] = '⚠️ شرط تقریب نرمال (np₀ ≥ 5 و n(1-p₀) ≥ 5) برقرار نیست؛ از آزمون دقیق دوجمله‌ای استفاده کنید.';
        }

        return self::wrap('z', $z, null, $p, $alpha, ['±' . round($zc, 4)], [
            'n' => $n, 'x' => $x, 'p_hat' => round($ph, 4),
        ], $warnings, "نسبت مشاهده‌شده " . round($ph, 3) . " با نسبت فرضی $p0 مقایسه شد.");
    }

    // ─────────── F برابری واریانس‌ها ───────────
    private static function fTest(array $a, array $b, float $alpha): array
    {
        $n1 = count($a); $n2 = count($b);
        $v1 = StatEngine::variance($a); $v2 = StatEngine::variance($b);
        if ($v1 <= 0 || $v2 <= 0) throw new \InvalidArgumentException('برای آزمون F، واریانس هر دو نمونه باید مثبت باشد.');
        $F = $v1 / $v2;
        $d1 = $n1 - 1; $d2 = $n2 - 1;
        $cdf = DL::cdf('f', ['d1' => $d1, 'd2' => $d2], $F);
        $p = 2 * min($cdf, 1 - $cdf);
        $lo = DL::quantile('f', ['d1' => $d1, 'd2' => $d2], $alpha / 2);
        $hi = DL::quantile('f', ['d1' => $d1, 'd2' => $d2], 1 - $alpha / 2);

        return self::wrap('F', $F, [$d1, $d2], $p, $alpha,
            [round($lo, 4), round($hi, 4)],
            ['var1' => round($v1, 4), 'var2' => round($v2, 4)],
            [self::normalityWarning($a, 'نمونه ۱'), self::normalityWarning($b, 'نمونه ۲')],
            'واریانس دو گروه مقایسه شد (H0: برابری واریانس‌ها).');
    }

    // ─────────── کای‌دو نیکویی برازش ───────────
    private static function chi2Gof(array $obs, array $exp, float $alpha): array
    {
        $k = count($obs);
        if ($k < 2) throw new \InvalidArgumentException('حداقل دو دسته برای آزمون کای‌دو لازم است.');
        $total = array_sum($obs);
        if (empty($exp)) $exp = array_fill(0, $k, $total / $k);
        if (count($exp) !== $k || $total <= 0 || array_sum($exp) <= 0) throw new \InvalidArgumentException('فراوانی‌های مشاهده‌شده و موردانتظار سازگار نیستند.');
        if (abs(array_sum($exp) - $total) > max(1e-8, $total * 1e-8)) throw new \InvalidArgumentException('مجموع فراوانی‌های موردانتظار باید با مجموع مشاهده‌ها برابر باشد.');
        foreach ($exp as $expected) if (!is_finite((float)$expected) || (float)$expected <= 0) throw new \InvalidArgumentException('فراوانی‌های موردانتظار باید مثبت و متناهی باشند.');

        $chi2 = 0;
        for ($i = 0; $i < $k; $i++) {
            $chi2 += (($obs[$i] - $exp[$i]) ** 2) / $exp[$i];
        }
        $df = $k - 1;
        $p = 1 - DL::cdf('chi2', ['df' => $df], $chi2);
        $crit = DL::quantile('chi2', ['df' => $df], 1 - $alpha);

        $warnings = [];
        foreach ($exp as $e) {
            if ($e < 5) { $warnings[] = '⚠️ برخی فراوانی‌های موردانتظار کمتر از ۵ است؛ نتایج با احتیاط تفسیر شود.'; break; }
        }

        return self::wrap('χ²', $chi2, $df, $p, $alpha, [round($crit, 4)],
            ['k' => $k, 'total' => $total], $warnings,
            'فراوانی‌های مشاهده‌شده با توزیع موردانتظار مقایسه شد.');
    }

    // ─────────── کای‌دو استقلال ───────────
    private static function chi2Indep(array $matrix, float $alpha): array
    {
        $r = count($matrix);
        if ($r < 2 || !is_array($matrix[0] ?? null)) throw new \InvalidArgumentException('جدول توافقی باید حداقل ۲×۲ باشد.');
        $c = count($matrix[0]);
        if ($c < 2) throw new \InvalidArgumentException('جدول توافقی باید حداقل ۲×۲ باشد.');
        $total = 0; $rowS = array_fill(0, $r, 0); $colS = array_fill(0, $c, 0);
        for ($i = 0; $i < $r; $i++) {
            if (!is_array($matrix[$i]) || count($matrix[$i]) !== $c) throw new \InvalidArgumentException('ردیف‌های جدول توافقی باید هم‌اندازه باشند.');
            for ($j = 0; $j < $c; $j++) {
                if (!is_numeric($matrix[$i][$j]) || !is_finite((float)$matrix[$i][$j]) || (float)$matrix[$i][$j] < 0) throw new \InvalidArgumentException('فراوانی‌های جدول توافقی باید نامنفی و متناهی باشند.');
            $total += $matrix[$i][$j]; $rowS[$i] += $matrix[$i][$j]; $colS[$j] += $matrix[$i][$j];
            }
        }
        if ($total <= 0 || in_array(0, $rowS, true) || in_array(0, $colS, true)) throw new \InvalidArgumentException('حاشیه‌های جدول توافقی باید مثبت باشند.');

        $chi2 = 0; $minE = PHP_FLOAT_MAX;
        for ($i = 0; $i < $r; $i++) for ($j = 0; $j < $c; $j++) {
            $E = $rowS[$i] * $colS[$j] / $total;
            $minE = min($minE, $E);
            $chi2 += (($matrix[$i][$j] - $E) ** 2) / $E;
        }
        $df = ($r - 1) * ($c - 1);
        $p = 1 - DL::cdf('chi2', ['df' => $df], $chi2);
        $crit = DL::quantile('chi2', ['df' => $df], 1 - $alpha);

        $warnings = $minE < 5 ? ['⚠️ حداقل یک فراوانی موردانتظار کمتر از ۵ است.'] : [];

        return self::wrap('χ²', $chi2, $df, $p, $alpha, [round($crit, 4)],
            ['rows' => $r, 'cols' => $c, 'total' => $total], $warnings,
            'استقلال دو متغیر کیفی در جدول توافقی بررسی شد (H0: استقلال).');
    }

    // ─────────── من-ویتنی ───────────
    private static function mannWhitney(array $a, array $b, string $alt, float $alpha): array
    {
        $n1 = count($a); $n2 = count($b);
        $all = [];
        foreach ($a as $v) $all[] = ['v' => $v, 'g' => 1];
        foreach ($b as $v) $all[] = ['v' => $v, 'g' => 2];
        usort($all, fn($x, $y) => $x['v'] <=> $y['v']);

        $N = $n1 + $n2;
        $ranks = array_fill(0, $N, 0);
        $tieSum = 0; $i = 0;
        while ($i < $N) {
            $j = $i;
            while ($j + 1 < $N && $all[$j + 1]['v'] == $all[$i]['v']) $j++;
            $avg = ($i + $j + 2) / 2;
            for ($k = $i; $k <= $j; $k++) $ranks[$k] = $avg;
            $t = $j - $i + 1;
            if ($t > 1) $tieSum += $t ** 3 - $t;
            $i = $j + 1;
        }

        $R1 = 0;
        foreach ($ranks as $idx => $rk) if ($all[$idx]['g'] === 1) $R1 += $rk;
        $U1 = $R1 - $n1 * ($n1 + 1) / 2;
        $U2 = $n1 * $n2 - $U1;
        $U = min($U1, $U2);

        $muU = $n1 * $n2 / 2;
        // Var(U) with tie correction: n1*n2/12 * [N+1 - Σ(t³-t)/(N(N-1))].
        $varianceU = ($n1 * $n2 / 12) * (($N + 1) - $tieSum / ($N * ($N - 1)));
        $sig = sqrt(max(0.0, $varianceU));
        $allTied = $sig <= 0;
        $z = $allTied ? 0.0 : ($U1 - $muU) / $sig;
        $p = $allTied ? 1.0 : self::tailP(DL::cdf('normal', ['mu' => 0, 'sigma' => 1], $z), $alt);
        $zc = DL::quantile('normal', ['mu' => 0, 'sigma' => 1], 1 - $alpha / 2);

        $warnings = ($n1 < 10 || $n2 < 10)
            ? ['⚠️ حجم نمونه‌ها کوچک است؛ تقریب نرمال ممکن است دقیق نباشد.'] : [];
        if ($allTied) $warnings[] = 'همهٔ مقادیر برابرند؛ آمارهٔ U تمایزی میان دو گروه نشان نمی‌دهد.';

        return self::wrap('z', $z, null, $p, $alpha, ['±' . round($zc, 4)],
            ['U' => round($U, 1), 'R1' => round($R1, 1), 'n1' => $n1, 'n2' => $n2], $warnings,
            'توزیع دو گروه مستقل بدون فرض نرمال بودن مقایسه شد.');
    }

    // ═══ توابع کمکی ═══
    private static function tailP(float $cdf, string $alt): float
    {
        if ($alt === 'less') return $cdf;
        if ($alt === 'greater') return 1 - $cdf;
        return 2 * min($cdf, 1 - $cdf);
    }

    /** آزمون نرمال بودن Jarque-Bera */
    private static function normalityWarning(array $data, string $label): ?string
    {
        $n = count($data);
        if ($n < 8) return null;
        $s = StatEngine::skewness($data);
        $k = StatEngine::kurtosis($data);
        $jb = $n / 6 * ($s * $s + $k * $k / 4);
        $p = 1 - DL::cdf('chi2', ['df' => 2], $jb);
        if ($p < 0.05) {
            return "⚠️ فرض نرمال بودن برای $label تأیید نشد (Jarque-Bera p=" . round($p, 4) . "). در صورت نیاز از آزمون ناپارامتری استفاده کنید.";
        }
        return null;
    }

    private static function wrap(string $statName, float $stat, $df, float $p, float $alpha, array $crit, array $extra, array $warnings, string $context): array
    {
        $warnings = array_values(array_filter($warnings));
        $concl = $p < $alpha ? 'reject' : 'fail';
        $interp = $context . "\n";
        $interp .= ($concl === 'reject')
            ? "✅ چون p-value (" . round($p, 5) . ") کمتر از α ($alpha) است، H0 رد می‌شود؛ نتیجه از نظر آماری معنادار است."
            : "❌ چون p-value (" . round($p, 5) . ") بزرگ‌تر از α ($alpha) است، شواهد کافی برای رد H0 وجود ندارد.";
        return [
            'statistic_name' => $statName,
            'statistic' => round($stat, 6),
            'df' => $df,
            'p_value' => round($p, 6),
            'critical' => $crit,
            'alpha' => $alpha,
            'conclusion' => $concl,
            'extra' => $extra,
            'warnings' => $warnings,
            'interpretation_fa' => $interp,
        ];
    }
}
