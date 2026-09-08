<?php
namespace App\Software\Or\Helpers;

/**
 * MonteCarloEngine - شبیه‌سازی مونت‌کارلو برای مدل‌سازی عدم قطعیت
 */
class MonteCarloEngine
{
    const DISTRIBUTIONS = [
        'uniform'    => ['name' => 'یکنواخت',     'params' => ['min', 'max']],
        'normal'     => ['name' => 'نرمال',        'params' => ['mean', 'std']],
        'triangular' => ['name' => 'مثلثی',        'params' => ['min', 'mode', 'max']],
        'exponential'=> ['name' => 'نمایی',        'params' => ['lambda']],
        'poisson'    => ['name' => 'پواسون',       'params' => ['lambda']],
        'binomial'   => ['name' => 'دوجمله‌ای',    'params' => ['n', 'p']],
        'lognormal'  => ['name' => 'لگنرمال',      'params' => ['mean', 'std']],
    ];

    public static function listDistributions(): array { return self::DISTRIBUTIONS; }

    /**
     * اجرای شبیه‌سازی
     * @param array $variables آرایه متغیرها: [['name'=>'x1','dist'=>'normal','params'=>['mean'=>10,'std'=>2]], ...]
     * @param string $funcExpr عبارت تابع (مثلاً 'x1 * x2 + x3')
     * @param int $iterations تعداد تکرار
     * @param int|null $seed برای تکرارپذیری
     */
    public static function simulate(array $variables, string $funcExpr, int $iterations = 10000, ?int $seed = null): array
    {
        if ($iterations < 100) throw new \Exception('تعداد تکرار باید حداقل ۱۰۰ باشد.');
        if ($iterations > 1000000) throw new \Exception('تعداد تکرار نباید بیش از ۱,۰۰,۰۰۰ باشد.');
        if (empty($variables)) throw new \Exception('حداقل یک متغیر تصادفی لازم است.');

        if ($seed !== null) mt_srand($seed);

        // اعتبارسنجی متغیرها
        foreach ($variables as $v) {
            if (!isset(self::DISTRIBUTIONS[$v['dist']])) {
                throw new \Exception("توزیع نامعتبر: {$v['dist']}");
            }
            if (empty($v['params'])) {
                throw new \Exception("پارامترهای توزیع برای {$v['name']} مشخص نشده است.");
            }
        }

        $results = [];
        $varStats = [];
        foreach ($variables as $v) $varStats[$v['name']] = ['sum' => 0, 'sum2' => 0];

        $t0 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $values = [];
            foreach ($variables as $v) {
                $values[$v['name']] = self::sample($v['dist'], $v['params']);
                $varStats[$v['name']]['sum'] += $values[$v['name']];
                $varStats[$v['name']]['sum2'] += $values[$v['name']] ** 2;
            }
            try {
                $y = self::evaluate($funcExpr, $values);
                $results[] = $y;
            } catch (\Throwable $e) {
                // اگر ارزیابی ناموفق بود، رد می‌کنیم
            }
        }

        $elapsed = microtime(true) - $t0;

        if (empty($results)) {
            throw new \Exception('هیچ نتیجه معتبری تولید نشد. عبارت تابع را بررسی کنید.');
        }

        // آمار نتایج
        $stats = self::computeStats($results);
        $varFinalStats = [];
        foreach ($varStats as $name => $s) {
            $mean = $s['sum'] / $iterations;
            $varFinalStats[$name] = [
                'mean' => $mean,
                'std'  => sqrt(max(0, $s['sum2'] / $iterations - $mean ** 2)),
            ];
        }

        // هیستوگرام
        $histogram = self::histogram($results, 30);

        // تحلیل ریسک (احتمالات)
        $risk = self::riskAnalysis($results);

        return [
            'status'       => 'ok',
            'iterations'   => $iterations,
            'elapsed_ms'   => round($elapsed * 1000, 2),
            'function'     => $funcExpr,
            'variables'    => $variables,
            'stats'        => $stats,
            'var_stats'    => $varFinalStats,
            'histogram'    => $histogram,
            'risk'         => $risk,
            'interpretation' => self::interpret($stats, $risk, $funcExpr),
        ];
    }

    /** نمونه‌برداری از توزیع */
    private static function sample(string $dist, array $params): float
    {
        switch ($dist) {
            case 'uniform':
                $min = (float)$params['min'];
                $max = (float)$params['max'];
                return $min + mt_rand() / mt_getrandmax() * ($max - $min);

            case 'normal':
                $mean = (float)$params['mean'];
                $std  = (float)$params['std'];
                // Box-Muller
                $u1 = mt_rand() / mt_getrandmax();
                $u2 = mt_rand() / mt_getrandmax();
                return $mean + $std * sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);

            case 'triangular':
                $min = (float)$params['min'];
                $mode = (float)$params['mode'];
                $max = (float)$params['max'];
                $u = mt_rand() / mt_getrandmax();
                $fc = ($mode - $min) / ($max - $min);
                if ($u < $fc) return $min + sqrt($u * ($mode - $min) * ($max - $min));
                return $max - sqrt((1 - $u) * ($max - $mode) * ($max - $min));

            case 'exponential':
                $lambda = (float)$params['lambda'];
                return -log(1 - mt_rand() / mt_getrandmax()) / $lambda;

            case 'poisson':
                $lambda = (float)$params['lambda'];
                $L = exp(-$lambda);
                $k = 0; $p = 1;
                do { $k++; $p *= mt_rand() / mt_getrandmax(); } while ($p > $L);
                return (float)($k - 1);

            case 'binomial':
                $n = (int)$params['n'];
                $p = (float)$params['p'];
                $success = 0;
                for ($i = 0; $i < $n; $i++) {
                    if (mt_rand() / mt_getrandmax() < $p) $success++;
                }
                return (float)$success;

            case 'lognormal':
                $mean = (float)$params['mean'];
                $std  = (float)$params['std'];
                $u1 = mt_rand() / mt_getrandmax();
                $u2 = mt_rand() / mt_getrandmax();
                $z = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
                return exp($mean + $std * $z);

            default:
                throw new \Exception("توزیع پشتیبانی‌نشده: $dist");
        }
    }

    /** ارزیابی عبارت ریاضی با متغیرها */
    private static function evaluate(string $expr, array $values): float
    {
        // جایگزینی متغیرها با مقادیر
        $safeExpr = $expr;
        foreach ($values as $name => $val) {
            $safeExpr = preg_replace('/\b' . preg_quote($name, '/') . '\b/', (string)$val, $safeExpr);
        }
        // فقط اجازه عملگرهای ریاضی
        $safeExpr = preg_replace('/[^0-9\.\+\-\*\/\(\)\s\^]/', '', $safeExpr);
        $safeExpr = str_replace('^', '**', $safeExpr);

        // ارزیابی امن
        $result = @eval("return ($safeExpr);");
        if ($result === false || !is_numeric($result)) {
            throw new \Exception("عبارت نامعتبر: $expr");
        }
        return (float)$result;
    }

    /** محاسبه آمار توصیفی */
    private static function computeStats(array $data): array
    {
        $n = count($data);
        sort($data);
        $mean = array_sum($data) / $n;
        $variance = 0;
        foreach ($data as $v) $variance += ($v - $mean) ** 2;
        $variance /= ($n - 1);
        $std = sqrt($variance);

        $median = $n % 2 ? $data[intdiv($n, 2)] : ($data[intdiv($n, 2) - 1] + $data[intdiv($n, 2)]) / 2;
        $q1 = $data[intdiv($n, 4)];
        $q3 = $data[intdiv($n * 3, 4)];
        $min = $data[0];
        $max = $data[$n - 1];

        // بازه اطمینان ۹۵٪ برای میانگین
        $ci95 = 1.96 * $std / sqrt($n);

        return [
            'mean'   => round($mean, 6),
            'median' => round($median, 6),
            'std'    => round($std, 6),
            'variance' => round($variance, 6),
            'min'    => round($min, 6),
            'max'    => round($max, 6),
            'q1'     => round($q1, 6),
            'q3'     => round($q3, 6),
            'ci95_lower' => round($mean - $ci95, 6),
            'ci95_upper' => round($mean + $ci95, 6),
            'skewness' => round(self::skewness($data, $mean, $std), 4),
            'kurtosis' => round(self::kurtosis($data, $mean, $std), 4),
        ];
    }

    private static function skewness(array $data, float $mean, float $std): float
    {
        $n = count($data);
        $sum = 0;
        foreach ($data as $v) $sum += (($v - $mean) / $std) ** 3;
        return ($n / (($n - 1) * ($n - 2))) * $sum;
    }

    private static function kurtosis(array $data, float $mean, float $std): float
    {
        $n = count($data);
        $sum = 0;
        foreach ($data as $v) $sum += (($v - $mean) / $std) ** 4;
        return (($n * ($n + 1)) / (($n - 1) * ($n - 2) * ($n - 3))) * $sum - (3 * ($n - 1) ** 2) / (($n - 2) * ($n - 3));
    }

    /** هیستوگرام */
    private static function histogram(array $data, int $bins): array
    {
        $min = min($data);
        $max = max($data);
        $range = $max - $min ?: 1;
        $binWidth = $range / $bins;
        $hist = array_fill(0, $bins, 0);
        foreach ($data as $v) {
            $idx = min($bins - 1, (int)(($v - $min) / $binWidth));
            $hist[$idx]++;
        }
        $labels = [];
        for ($i = 0; $i < $bins; $i++) {
            $labels[] = round($min + $i * $binWidth, 2);
        }
        return ['bins' => $bins, 'counts' => $hist, 'labels' => $labels, 'bin_width' => round($binWidth, 4)];
    }

    /** تحلیل ریسک */
    private static function riskAnalysis(array $data): array
    {
        $n = count($data);
        $mean = array_sum($data) / $n;
        $negative = count(array_filter($data, fn($v) => $v < 0));
        $zero = count(array_filter($data, fn($v) => abs($v) < 1e-9));
        $positive = $n - $negative - $zero;

        return [
            'p_negative' => round($negative / $n * 100, 2),
            'p_zero'     => round($zero / $n * 100, 2),
            'p_positive' => round($positive / $n * 100, 2),
            'p_below_mean' => round(count(array_filter($data, fn($v) => $v < $mean)) / $n * 100, 2),
        ];
    }

    /** تفسیر خودکار */
    private static function interpret(array $stats, array $risk, string $func): string
    {
        $out = [];
        $out[] = "📊 تابع هدف: $func";
        $out[] = " میانگین خروجی: {$stats['mean']} ± {$stats['std']} (انحراف معیار)";
        $out[] = " بازه اطمینان ۹۵٪ برای میانگین: [{$stats['ci95_lower']}, {$stats['ci95_upper']}]";
        $out[] = " میانه: {$stats['median']} | حداقل: {$stats['min']} | حداکثر: {$stats['max']}";

        if ($stats['skewness'] > 0.5) $out[] = "⚠️ توزیع خروجی چولگی مثبت دارد (دنباله راست).";
        elseif ($stats['skewness'] < -0.5) $out[] = "⚠️ توزیع خروجی چولگی منفی دارد (دنباله چپ).";

        if ($risk['p_negative'] > 10) $out[] = " احتمال " . round($risk['p_negative'], 1) . "% برای خروجی منفی (ریسک بالا).";
        elseif ($risk['p_negative'] > 0) $out[] = "🟡 احتمال " . round($risk['p_negative'], 1) . "% برای خروجی منفی.";
        else $out[] = "🟢 خروجی همیشه مثبت است (بدون ریسک منفی).";

        return implode("\n", $out);
    }
}