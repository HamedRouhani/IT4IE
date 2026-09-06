<?php
namespace App\Software\Statlab\Helpers;

/**
 * DistributionLibrary - موتور محاسبات توزیع‌های احتمال
 * شامل توابع ویژهٔ عددی (Gamma, Beta) برای محاسبهٔ دقیق CDF و Quantile
 */
class DistributionLibrary
{
    const EPS = 1e-10;

    // ═══════════════ توابع ویژهٔ عددی ═══════════════

    public static function logGamma(float $x): float
    {
        $c = [76.18009172947146, -86.50532032941677, 24.01409824083091,
              -1.231739572450155, 0.1208650973866179e-2, -0.5395239384953e-5];
        $y = $x;
        $tmp = $x + 5.5;
        $tmp -= ($x + 0.5) * log($tmp);
        $ser = 1.000000000190015;
        for ($j = 0; $j < 6; $j++) $ser += $c[$j] / ++$y;
        return -$tmp + log(2.5066282746310005 * $ser / $x);
    }

    /** P(a,x) گامای پایین منظم‌شده */
    public static function gammaP(float $a, float $x): float
    {
        if ($x <= 0 || $a <= 0) return 0.0;
        if ($x < $a + 1) {
            $sum = 1.0 / $a; $del = $sum;
            for ($n = 1; $n < 300; $n++) {
                $del *= $x / ($a + $n);
                $sum += $del;
                if (abs($del) < abs($sum) * self::EPS) break;
            }
            return $sum * exp(-$x + $a * log($x) - self::logGamma($a));
        }
        return 1.0 - self::gammaQ($a, $x);
    }

    /** Q(a,x) گامای بالای منظم‌شده */
    public static function gammaQ(float $a, float $x): float
    {
        if ($x < $a + 1) return 1.0 - self::gammaP($a, $x);
        $tiny = 1e-300;
        $b = $x + 1 - $a; $c = 1 / $tiny; $d = 1 / $b; $h = $d;
        for ($i = 1; $i < 300; $i++) {
            $an = -$i * ($i - $a); $b += 2;
            $d = $an * $d + $b; if (abs($d) < $tiny) $d = $tiny;
            $c = $b + $an / $c; if (abs($c) < $tiny) $c = $tiny;
            $d = 1 / $d; $del = $d * $c; $h *= $del;
            if (abs($del - 1) < self::EPS) break;
        }
        return exp(-$x + $a * log($x) - self::logGamma($a)) * $h;
    }

    /** I_x(a,b) بتای ناقص منظم‌شده */
    public static function ibeta(float $a, float $b, float $x): float
    {
        if ($x <= 0) return 0.0;
        if ($x >= 1) return 1.0;
        $bt = exp(self::logGamma($a + $b) - self::logGamma($a) - self::logGamma($b)
              + $a * log($x) + $b * log(1 - $x));
        if ($x < ($a + 1) / ($a + $b + 2)) return $bt * self::betacf($a, $b, $x) / $a;
        return 1 - $bt * self::betacf($b, $a, 1 - $x) / $b;
    }

    private static function betacf(float $a, float $b, float $x): float
    {
        $tiny = 1e-300;
        $qab = $a + $b; $qap = $a + 1; $qam = $a - 1;
        $c = 1; $d = 1 - $qab * $x / $qap;
        if (abs($d) < $tiny) $d = $tiny;
        $d = 1 / $d; $h = $d;
        for ($m = 1; $m <= 300; $m++) {
            $m2 = 2 * $m;
            $aa = $m * ($b - $m) * $x / (($qam + $m2) * ($a + $m2));
            $d = 1 + $aa * $d; if (abs($d) < $tiny) $d = $tiny;
            $c = 1 + $aa / $c; if (abs($c) < $tiny) $c = $tiny;
            $d = 1 / $d; $h *= $d * $c;
            $aa = -($a + $m) * ($qab + $m) * $x / (($a + $m2) * ($qap + $m2));
            $d = 1 + $aa * $d; if (abs($d) < $tiny) $d = $tiny;
            $c = 1 + $aa / $c; if (abs($c) < $tiny) $c = $tiny;
            $d = 1 / $d; $del = $d * $c; $h *= $del;
            if (abs($del - 1) < self::EPS) break;
        }
        return $h;
    }

    public static function erf(float $x): float
    {
        $sign = $x < 0 ? -1 : 1;
        $ax = abs($x);
        $t = 1 / (1 + 0.3275911 * $ax);
        $poly = ((((1.061405429 * $t - 1.453152027) * $t + 1.421413741) * $t - 0.284496736) * $t + 0.254829592) * $t;
        return $sign * (1 - $poly * exp(-$ax * $ax));
    }

    // ═══════════════ متادیتای توزیع‌ها (برای UI) ═══════════════

    public static function list(): array
    {
        return [
            'normal' => ['name_fa' => 'نرمال (Normal)', 'family' => 'continuous', 'params' => [
                ['key' => 'mu', 'label' => 'میانگین μ', 'default' => 0, 'step' => 'any'],
                ['key' => 'sigma', 'label' => 'انحراف معیار σ', 'default' => 1, 'step' => 'any', 'min' => 0.0001],
            ]],
            't' => ['name_fa' => 'استودنت t', 'family' => 'continuous', 'params' => [
                ['key' => 'df', 'label' => 'درجه آزادی ν', 'default' => 10, 'step' => 1, 'min' => 1],
            ]],
            'chi2' => ['name_fa' => 'کای‌دو (χ²)', 'family' => 'continuous', 'params' => [
                ['key' => 'df', 'label' => 'درجه آزادی k', 'default' => 5, 'step' => 1, 'min' => 1],
            ]],
            'f' => ['name_fa' => 'اف (Fisher)', 'family' => 'continuous', 'params' => [
                ['key' => 'd1', 'label' => 'درجه آزادی صورت d₁', 'default' => 5, 'step' => 1, 'min' => 1],
                ['key' => 'd2', 'label' => 'درجه آزادی مخرج d₂', 'default' => 10, 'step' => 1, 'min' => 1],
            ]],
            'exponential' => ['name_fa' => 'نمایی', 'family' => 'continuous', 'params' => [
                ['key' => 'lambda', 'label' => 'نرخ λ', 'default' => 1, 'step' => 'any', 'min' => 0.0001],
            ]],
            'uniform' => ['name_fa' => 'یکنواخت پیوسته', 'family' => 'continuous', 'params' => [
                ['key' => 'a', 'label' => 'کران پایین a', 'default' => 0, 'step' => 'any'],
                ['key' => 'b', 'label' => 'کران بالای b', 'default' => 1, 'step' => 'any'],
            ]],
            'lognormal' => ['name_fa' => 'لگ‌نرمال', 'family' => 'continuous', 'params' => [
                ['key' => 'mu', 'label' => 'μ (لگاریتم)', 'default' => 0, 'step' => 'any'],
                ['key' => 'sigma', 'label' => 'σ (لگاریتم)', 'default' => 0.5, 'step' => 'any', 'min' => 0.0001],
            ]],
            'weibull' => ['name_fa' => 'ویبول', 'family' => 'continuous', 'params' => [
                ['key' => 'k', 'label' => 'شکل k', 'default' => 1.5, 'step' => 'any', 'min' => 0.0001],
                ['key' => 'lambda', 'label' => 'مقیاس λ', 'default' => 1, 'step' => 'any', 'min' => 0.0001],
            ]],
            'binomial' => ['name_fa' => 'دوجمله‌ای', 'family' => 'discrete', 'params' => [
                ['key' => 'n', 'label' => 'تعداد آزمایش n', 'default' => 20, 'step' => 1, 'min' => 1],
                ['key' => 'p', 'label' => 'احتمال موفقیت p', 'default' => 0.5, 'step' => 'any', 'min' => 0, 'max' => 1],
            ]],
            'poisson' => ['name_fa' => 'پواسون', 'family' => 'discrete', 'params' => [
                ['key' => 'lambda', 'label' => 'نرخ λ', 'default' => 4, 'step' => 'any', 'min' => 0.0001],
            ]],
        ];
    }

    public static function support(string $d, array $p): array
    {
        switch ($d) {
            case 'normal':    return [$p['mu'] - 4 * $p['sigma'], $p['mu'] + 4 * $p['sigma']];
            case 't':         return [-6, 6];
            case 'chi2':      return [0, max(12, $p['df'] * 3)];
            case 'f':         return [0, 5];
            case 'exponential': return [0, 6 / max($p['lambda'], 1e-9)];
            case 'uniform':   return [$p['a'], $p['b']];
            case 'lognormal': return [0, exp($p['mu'] + 3.5 * $p['sigma'])];
            case 'weibull':   return [0, $p['lambda'] * 3.5];
            case 'binomial':  return [0, $p['n']];
            case 'poisson':   return [0, max(10, (int)ceil($p['lambda'] * 3))];
        }
        return [0, 1];
    }

    // ═══════════════ PDF / PMF ═══════════════

    public static function pdf(string $d, array $p, float $x): float
    {
        switch ($d) {
            case 'normal':
                $z = ($x - $p['mu']) / $p['sigma'];
                return exp(-0.5 * $z * $z) / ($p['sigma'] * sqrt(2 * M_PI));
            case 't':
                $v = $p['df'];
                return exp(self::logGamma(($v + 1) / 2) - self::logGamma($v / 2))
                     / sqrt($v * M_PI) * pow(1 + $x * $x / $v, -($v + 1) / 2);
            case 'chi2':
                if ($x < 0) return 0;
                $k = $p['df'];
                if ($x == 0) return $k > 2 ? 0 : ($k == 2 ? 0.5 : INF);
                return exp(($k / 2 - 1) * log($x) - $x / 2 - ($k / 2) * log(2) - self::logGamma($k / 2));
            case 'f':
                if ($x <= 0) return 0;
                $d1 = $p['d1']; $d2 = $p['d2'];
                return exp(($d1 / 2) * log($d1) + ($d2 / 2) * log($d2) + ($d1 / 2 - 1) * log($x)
                     - (($d1 + $d2) / 2) * log($d1 * $x + $d2)
                     + self::logGamma(($d1 + $d2) / 2) - self::logGamma($d1 / 2) - self::logGamma($d2 / 2));
            case 'exponential':
                return $x < 0 ? 0 : $p['lambda'] * exp(-$p['lambda'] * $x);
            case 'uniform':
                return ($x >= $p['a'] && $x <= $p['b']) ? 1 / ($p['b'] - $p['a']) : 0;
            case 'lognormal':
                if ($x <= 0) return 0;
                $z = (log($x) - $p['mu']) / $p['sigma'];
                return exp(-0.5 * $z * $z) / ($x * $p['sigma'] * sqrt(2 * M_PI));
            case 'weibull':
                if ($x < 0) return 0;
                $r = $x / $p['lambda'];
                return ($p['k'] / $p['lambda']) * pow($r, $p['k'] - 1) * exp(-pow($r, $p['k']));
            case 'binomial':
                $k = (int)round($x);
                if (abs($x - $k) > 1e-9 || $k < 0 || $k > $p['n']) return 0;
                return exp(self::logGamma($p['n'] + 1) - self::logGamma($k + 1) - self::logGamma($p['n'] - $k + 1)
                     + $k * log(max($p['p'], 1e-12)) + ($p['n'] - $k) * log(max(1 - $p['p'], 1e-12)));
            case 'poisson':
                $k = (int)round($x);
                if (abs($x - $k) > 1e-9 || $k < 0) return 0;
                return exp(-$p['lambda'] + $k * log($p['lambda']) - self::logGamma($k + 1));
        }
        return 0;
    }

    // ═══════════════ CDF ═══════════════

    public static function cdf(string $d, array $p, float $x): float
    {
        switch ($d) {
            case 'normal':    return 0.5 * (1 + self::erf(($x - $p['mu']) / ($p['sigma'] * sqrt(2))));
            case 't':
                $v = $p['df'];
                $q = 0.5 * self::ibeta($v / 2, 0.5, $v / ($v + $x * $x));
                return $x > 0 ? 1 - $q : $q;
            case 'chi2':      return $x <= 0 ? 0 : self::gammaP($p['df'] / 2, $x / 2);
            case 'f':
                if ($x <= 0) return 0;
                $d1 = $p['d1']; $d2 = $p['d2'];
                return self::ibeta($d1 / 2, $d2 / 2, $d1 * $x / ($d1 * $x + $d2));
            case 'exponential': return $x < 0 ? 0 : 1 - exp(-$p['lambda'] * $x);
            case 'uniform':
                if ($x < $p['a']) return 0;
                if ($x > $p['b']) return 1;
                return ($x - $p['a']) / ($p['b'] - $p['a']);
            case 'lognormal':
                if ($x <= 0) return 0;
                return 0.5 * (1 + self::erf((log($x) - $p['mu']) / ($p['sigma'] * sqrt(2))));
            case 'weibull':
                return $x < 0 ? 0 : 1 - exp(-pow($x / $p['lambda'], $p['k']));
            case 'binomial':
                $sum = 0; $K = (int)floor($x + 1e-9);
                for ($k = 0; $k <= min($K, $p['n']); $k++) $sum += self::pdf($d, $p, $k);
                return min(1, $sum);
            case 'poisson':
                return $x < 0 ? 0 : 1 - self::gammaP((int)floor($x + 1e-9) + 1, $p['lambda']);
        }
        return 0;
    }

    // ═══════════════ Quantile (معکوس CDF) ═══════════════

    public static function quantile(string $d, array $p, float $prob): float
    {
        $prob = max(1e-9, min(1 - 1e-9, $prob));
        [$lo, $hi] = self::support($d, $p);
        if (self::cdf($d, $p, $lo) >= $prob) return $lo;
        if (self::cdf($d, $p, $hi) < $prob) $hi *= 10; // دنبه‌های سنگین
        for ($i = 0; $i < 120; $i++) {
            $mid = ($lo + $hi) / 2;
            if (self::cdf($d, $p, $mid) < $prob) $lo = $mid; else $hi = $mid;
        }
        return ($lo + $hi) / 2;
    }

    public static function mean(string $d, array $p): ?float
    {
        switch ($d) {
            case 'normal': case 'lognormal': return $d === 'normal' ? $p['mu'] : exp($p['mu'] + $p['sigma'] ** 2 / 2);
            case 't': return $p['df'] > 1 ? 0 : null;
            case 'chi2': return $p['df'];
            case 'f': return $p['d2'] > 2 ? $p['d2'] / ($p['d2'] - 2) : null;
            case 'exponential': return 1 / $p['lambda'];
            case 'uniform': return ($p['a'] + $p['b']) / 2;
            case 'weibull': return $p['lambda'] * exp(self::logGamma(1 + 1 / $p['k']));
            case 'binomial': return $p['n'] * $p['p'];
            case 'poisson': return $p['lambda'];
        }
        return null;
    }

    public static function variance(string $d, array $p): ?float
    {
        switch ($d) {
            case 'normal': return $p['sigma'] ** 2;
            case 't': return $p['df'] > 2 ? $p['df'] / ($p['df'] - 2) : null;
            case 'chi2': return 2 * $p['df'];
            case 'f': return $p['d2'] > 4 ? 2 * $p['d2'] ** 2 * ($p['d1'] + $p['d2'] - 2) / ($p['d1'] * ($p['d2'] - 2) ** 2 * ($p['d2'] - 4)) : null;
            case 'exponential': return 1 / $p['lambda'] ** 2;
            case 'uniform': return ($p['b'] - $p['a']) ** 2 / 12;
            case 'lognormal': return (exp($p['sigma'] ** 2) - 1) * exp(2 * $p['mu'] + $p['sigma'] ** 2);
            case 'binomial': return $p['n'] * $p['p'] * (1 - $p['p']);
            case 'poisson': return $p['lambda'];
            case 'weibull':
                $g1 = exp(self::logGamma(1 + 1 / $p['k']));
                $g2 = exp(self::logGamma(1 + 2 / $p['k']));
                return $p['lambda'] ** 2 * ($g2 - $g1 ** 2);
        }
        return null;
    }
}