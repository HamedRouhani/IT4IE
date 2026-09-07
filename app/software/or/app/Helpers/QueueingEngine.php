<?php
namespace App\Software\Or\Helpers;

/**
 * QueueingEngine - موتور محاسبات نظریه صف
 * پشتیبانی از M/M/1, M/M/c, M/M/1/K, M/M/c/K, M/G/1, M/D/1
 */
class QueueingEngine
{
    const MODELS = [
        'MM1'  => ['name' => 'M/M/1',   'desc' => 'یک سرور، ورودی پواسون، خدمت نمایی'],
        'MMc'  => ['name' => 'M/M/c',   'desc' => 'چند سرور، ورودی پواسون، خدمت نمایی'],
        'MM1K' => ['name' => 'M/M/1/K', 'desc' => 'یک سرور با ظرفیت محدود'],
        'MMcK' => ['name' => 'M/M/c/K', 'desc' => 'چند سرور با ظرفیت محدود'],
        'MG1'  => ['name' => 'M/G/1',   'desc' => 'یک سرور با توزیع خدمت عمومی (Pollaczek-Khinchine)'],
        'MD1'  => ['name' => 'M/D/1',   'desc' => 'یک سرور با خدمت قطعی (زمان ثابت)'],
    ];

    public static function list(): array { return self::MODELS; }

    public static function solve(string $code, array $p): array
    {
        if (!isset(self::MODELS[$code])) throw new \Exception('مدل صف نامعتبر است.');
        $lambda = (float)$p['lambda'];
        $mu     = (float)$p['mu'];
        if ($lambda <= 0) throw new \Exception('نرخ ورود (λ) باید مثبت باشد.');
        if ($mu <= 0)     throw new \Exception('نرخ خدمت (μ) باید مثبت باشد.');

        switch ($code) {
            case 'MM1':  return self::mm1($lambda, $mu);
            case 'MMc':  return self::mmc($lambda, $mu, (int)$p['servers']);
            case 'MM1K': return self::mm1k($lambda, $mu, (int)$p['capacity']);
            case 'MMcK': return self::mmck($lambda, $mu, (int)$p['servers'], (int)$p['capacity']);
            case 'MG1':  return self::mg1($lambda, $mu, (float)($p['service_std'] ?? 0));
            case 'MD1':  return self::md1($lambda, $mu);
        }
    }

    private static function mm1(float $lambda, float $mu): array
    {
        $rho = $lambda / $mu;
        if ($rho >= 1) return self::unstable($rho, 'M/M/1');
        $P0 = 1 - $rho;
        $L  = $rho / (1 - $rho);
        $Lq = $rho * $rho / (1 - $rho);
        $W  = 1 / ($mu - $lambda);
        $Wq = $rho / ($mu - $lambda);
        $Pw = $rho;
        return self::wrap('M/M/1', $lambda, $mu, 1, null, $rho, $P0, $L, $Lq, $W, $Wq, $Pw, [
            'توضیح' => 'سیستم با یک سرور و صف نامحدود. شرط پایداری ρ<1 برقرار است.',
            'فرمول کلیدی' => 'L = ρ/(1-ρ) , W = 1/(μ-λ)',
        ]);
    }

    private static function mmc(float $lambda, float $mu, int $c): array
    {
        if ($c < 1) throw new \Exception('تعداد سرورها باید حداقل ۱ باشد.');
        $rho = $lambda / ($c * $mu);
        if ($rho >= 1) return self::unstable($rho, "M/M/$c");
        $a = $lambda / $mu;
        $sum = 0;
        for ($n = 0; $n < $c; $n++) $sum += pow($a, $n) / self::fact($n);
        $termC = (pow($a, $c) / self::fact($c)) * (1 / (1 - $rho));
        $P0 = 1 / ($sum + $termC);
        $Pw = (pow($a, $c) / self::fact($c)) * (1 / (1 - $rho)) * $P0;
        $Lq = $Pw * $rho / (1 - $rho);
        $L  = $Lq + $a;
        $Wq = $Lq / $lambda;
        $W  = $Wq + 1 / $mu;
        return self::wrap("M/M/$c", $lambda, $mu, $c, null, $rho, $P0, $L, $Lq, $W, $Wq, $Pw, [
            'توضیح' => 'سیستم با ' . $c . ' سرور موازی و صف مشترک (Erlang-C).',
            'ترافیک کل (a)' => round($a, 4),
            'Erlang-C' => round($Pw, 6),
        ]);
    }

    private static function mm1k(float $lambda, float $mu, int $K): array
    {
        if ($K < 1) throw new \Exception('ظرفیت باید حداقل ۱ باشد.');
        $rho = $lambda / $mu;
        if (abs($rho - 1) < 1e-10) { $P0 = 1 / ($K + 1); $L = $K / 2; }
        else {
            $P0 = (1 - $rho) / (1 - pow($rho, $K + 1));
            $L  = $rho / (1 - $rho) - ($K + 1) * pow($rho, $K + 1) / (1 - pow($rho, $K + 1));
        }
        $Pn = [];
        for ($n = 0; $n <= $K; $n++) $Pn[$n] = pow($rho, $n) * $P0;
        $PK = $Pn[$K];
        $lambdaEff = $lambda * (1 - $PK);
        $Lq = $L - (1 - $P0);
        $W  = $L / $lambdaEff;
        $Wq = $Lq / $lambdaEff;
        $Pw = 1 - $P0;
        return self::wrap('M/M/1/K', $lambda, $mu, 1, $K, $rho, $P0, $L, $Lq, $W, $Wq, $Pw, [
            'توضیح' => 'سیستم با یک سرور و ظرفیت محدود K=' . $K . ' مشتری.',
            'احتمال رد (P_K)' => round($PK, 6),
            'نرخ ورود مؤثر' => round($lambdaEff, 4),
        ], $Pn);
    }

    private static function mmck(float $lambda, float $mu, int $c, int $K): array
    {
        if ($K < $c) throw new \Exception('ظرفیت K باید بزرگ‌تر یا مساوی تعداد سرورها باشد.');
        $a = $lambda / $mu;
        $sum = 0;
        for ($n = 0; $n <= $K; $n++) {
            $sum += ($n < $c)
                ? pow($a, $n) / self::fact($n)
                : pow($a, $n) / (self::fact($c) * pow($c, $n - $c));
        }
        $P0 = 1 / $sum;

        $Pn = []; $L = 0; $Ls = 0;
        for ($n = 0; $n <= $K; $n++) {
            $p = ($n < $c)
                ? pow($a, $n) / self::fact($n) * $P0
                : pow($a, $n) / (self::fact($c) * pow($c, $n - $c)) * $P0;
            $Pn[$n] = $p;
            $L  += $n * $p;
            $Ls += min($n, $c) * $p;
        }
        $Lq = $L - $Ls;
        $PK = $Pn[$K];
        $lambdaEff = $lambda * (1 - $PK);
        $W  = $L / $lambdaEff;
        $Wq = $Lq / $lambdaEff;
        $rho = $lambda / ($c * $mu);
        $Pw = 1 - $P0;
        return self::wrap("M/M/$c/K", $lambda, $mu, $c, $K, $rho, $P0, $L, $Lq, $W, $Wq, $Pw, [
            'توضیح' => "سیستم $c سروره با ظرفیت محدود $K.",
            'احتمال رد (P_K)' => round($PK, 6),
            'نرخ ورود مؤثر' => round($lambdaEff, 4),
        ], $Pn);
    }

    private static function mg1(float $lambda, float $mu, float $sigma): array
    {
        if ($sigma < 0) throw new \Exception('انحراف معیار زمان خدمت نمی‌تواند منفی باشد.');
        $rho = $lambda / $mu;
        if ($rho >= 1) return self::unstable($rho, 'M/G/1');
        $Es  = 1 / $mu;
        $Es2 = $sigma * $sigma + $Es * $Es;
        $Lq  = ($lambda * $lambda * $Es2) / (2 * (1 - $rho));
        $L   = $Lq + $rho;
        $Wq  = $Lq / $lambda;
        $W   = $Wq + $Es;
        $P0  = 1 - $rho;
        $Pw  = $rho;
        return self::wrap('M/G/1', $lambda, $mu, 1, null, $rho, $P0, $L, $Lq, $W, $Wq, $Pw, [
            'توضیح' => 'فرمول Pollaczek-Khinchine برای خدمت با توزیع عمومی.',
            'E[S]' => round($Es, 4),
            'E[S²]' => round($Es2, 4),
            'σ(S)' => round($sigma, 4),
        ]);
    }

    private static function md1(float $lambda, float $mu): array
    {
        $rho = $lambda / $mu;
        if ($rho >= 1) return self::unstable($rho, 'M/D/1');
        $Lq = ($rho * $rho) / (2 * (1 - $rho));
        $L  = $Lq + $rho;
        $Wq = $Lq / $lambda;
        $W  = $Wq + 1 / $mu;
        $P0 = 1 - $rho;
        return self::wrap('M/D/1', $lambda, $mu, 1, null, $rho, $P0, $L, $Lq, $W, $Wq, $rho, [
            'توضیح' => 'خدمت قطعی (زمان ثابت = 1/μ). کارآمدترین حالت یک سروره.',
            'نکته' => 'Lq نصف حالت M/M/1 است.',
        ]);
    }

    private static function unstable(float $rho, string $model): array
    {
        return [
            'status' => 'unstable', 'model' => $model, 'rho' => round($rho, 6),
            'message' => "⚠️ سیستم ناپایدار است (ρ=" . round($rho, 3) . " ≥ 1). صف بی‌نهایت رشد می‌کند.",
            'suggestion' => 'برای پایداری، یا نرخ ورود کاهش یا نرخ خدمت/تعداد سرورها افزایش یابد.',
        ];
    }

    private static function wrap(string $model, float $lambda, float $mu, int $c, ?int $K, float $rho, float $P0, float $L, float $Lq, float $W, float $Wq, float $Pw, array $extra, ?array $pn = null): array
    {
        if ($pn === null) {
            $pn = [];
            $maxN = 30;
            if ($model === 'M/M/1') {
                for ($n = 0; $n <= $maxN; $n++) $pn[$n] = (1 - $rho) * pow($rho, $n);
            } elseif (preg_match('/^M\/M\/(\d+)$/', $model, $m)) {
                $cc = (int)$m[1]; $a = $lambda / $mu;
                for ($n = 0; $n <= $maxN; $n++) {
                    $pn[$n] = ($n < $cc)
                        ? pow($a, $n) / self::fact($n) * $P0
                        : pow($a, $n) / (self::fact($cc) * pow($cc, $n - $cc)) * $P0;
                }
            }
        }
        return [
            'status' => 'ok', 'model' => $model,
            'lambda' => $lambda, 'mu' => $mu, 'servers' => $c, 'capacity' => $K,
            'rho' => round($rho, 6), 'P0' => round($P0, 6), 'Pw' => round($Pw, 6),
            'L' => round($L, 4), 'Lq' => round($Lq, 4), 'W' => round($W, 4), 'Wq' => round($Wq, 4),
            'Pn' => $pn, 'extra' => $extra,
            'interpretation' => self::generateInterpretation($model, $rho, $L, $Lq, $W, $Wq, $Pw),
        ];
    }

    private static function generateInterpretation(string $m, float $rho, float $L, float $Lq, float $W, float $Wq, float $Pw): string
    {
        $out = [];
        $out[] = "📊 مدل $m با بهره‌وری ρ=" . round($rho, 3) . " پایدار است.";
        if ($rho > 0.85) $out[] = "⚠️ بهره‌وری بالاست (ρ>" . round($rho, 2) . ")؛ صف‌ها طولانی و ناپایداری محتمل است.";
        elseif ($rho < 0.4) $out[] = "💡 بهره‌وری پایین است؛ سرورها اغلب بیکارند.";
        $out[] = "⏱ به‌طور متوسط $Lq مشتری در صف منتظرند و $L مشتری در سیستم هستند.";
        $out[] = "⏰ هر مشتری به‌طور متوسط " . round($Wq, 3) . " واحد در صف و " . round($W, 3) . " واحد در سیستم می‌ماند.";
        $out[] = "🟢 احتمال مشغول بودن سرور " . round($Pw * 100, 1) . "% است.";
        return implode("\n", $out);
    }

    public static function sensitivity(string $code, array $p, float $start = 0.3, float $end = 0.95, int $steps = 15): array
    {
        $points = [];
        $mu = (float)$p['mu'];
        $c  = (int)($p['servers'] ?? 1);
        for ($i = 0; $i <= $steps; $i++) {
            $rho = $start + ($end - $start) * $i / $steps;
            $lambda = $rho * $mu * $c;
            $p['lambda'] = $lambda;
            try {
                $r = self::solve($code, $p);
                if ($r['status'] === 'ok') {
                    $points[] = ['rho' => round($rho, 4), 'L' => $r['L'], 'Lq' => $r['Lq'], 'W' => $r['W'], 'Wq' => $r['Wq']];
                }
            } catch (\Throwable $e) {}
        }
        return $points;
    }

    private static function fact(int $n): float
    {
        static $cache = [1.0];
        if ($n < 0) return 1.0;
        if (isset($cache[$n])) return $cache[$n];
        $r = 1.0;
        for ($i = count($cache); $i <= $n; $i++) { $r *= $i; $cache[$i] = $r; }
        return $cache[$n];
    }
}