<?php
/**
 * ============================================================
 * Quality Analyzer — NelsonRulesService
 * ============================================================
 * مسیر: app/software/quality/app/Services/NelsonRulesService.php
 * تشخیص ۸ قانون نلسون
 * مرجع: Nelson, L. S. (1984). Journal of Quality Technology, 16(4)
 * ============================================================
 */

namespace App\Software\Quality\Services;

class NelsonRulesService
{
    /**
     * تشخیص همه‌ی قوانین نقض‌شده
     *
     * @param array $series  سری داده‌ها
     * @param float $cl      خط مرکزی
     * @param float $sigma   انحراف معیار تخمینی
     * @return array
     */
    public function detect(array $series, float $cl, float $sigma): array
    {
        $violations = [];
        $n = count($series);

        if ($sigma <= 0 || $n < 2) {
            return $violations;
        }

        // z-score
        $z = [];
        foreach ($series as $v) {
            $z[] = ((float) $v - $cl) / $sigma;
        }

        // قانون ۱: یک نقطه خارج از 3σ
        $r1 = [];
        foreach ($z as $i => $zi) {
            if (abs($zi) > 3.0) {
                $r1[] = $i;
            }
        }
        if (!empty($r1)) {
            $violations[] = [
                'rule'   => 1,
                'points' => $r1,
                'desc'   => 'یک نقطه خارج از ۳ سیگما',
            ];
        }

        // قانون ۲: ۹ نقطه پشت‌سرهم در یک سمت CL
        $r2a = $this->runLength($z, fn($zi) => $zi > 0, 9);
        $r2b = $this->runLength($z, fn($zi) => $zi < 0, 9);
        $r2  = array_values(array_unique(array_merge($r2a, $r2b)));
        sort($r2);
        if (!empty($r2)) {
            $violations[] = [
                'rule'   => 2,
                'points' => $r2,
                'desc'   => '۹ نقطه پشت‌سرهم در یک سمت خط مرکزی',
            ];
        }

        // قانون ۳: ۶ نقطه متوالی صعودی یا نزولی
        $r3 = $this->trend($z, 6);
        if (!empty($r3)) {
            $violations[] = [
                'rule'   => 3,
                'points' => $r3,
                'desc'   => '۶ نقطه متوالی صعودی یا نزولی',
            ];
        }

        // قانون ۴: ۱۴ نقطه متناوب بالا/پایین
        $r4 = $this->alternating($z, 14);
        if (!empty($r4)) {
            $violations[] = [
                'rule'   => 4,
                'points' => $r4,
                'desc'   => '۱۴ نقطه متناوب بالا/پایین',
            ];
        }

        // قانون ۵: ۲ از ۳ نقطه متوالی خارج از 2σ در یک سمت
        $r5 = $this->twoOfThreeBeyond($z, 2.0);
        if (!empty($r5)) {
            $violations[] = [
                'rule'   => 5,
                'points' => $r5,
                'desc'   => '۲ از ۳ نقطه متوالی خارج از ۲ سیگما در یک سمت',
            ];
        }

        // قانون ۶: ۴ از ۵ نقطه متوالی خارج از 1σ در یک سمت
        $r6 = $this->fourOfFiveBeyond($z, 1.0);
        if (!empty($r6)) {
            $violations[] = [
                'rule'   => 6,
                'points' => $r6,
                'desc'   => '۴ از ۵ نقطه متوالی خارج از ۱ سیگما در یک سمت',
            ];
        }

        // قانون ۷: ۱۵ نقطه متوالی داخل 1σ (هر دو طرف)
        $r7 = $this->runWithin($z, 1.0, 15);
        if (!empty($r7)) {
            $violations[] = [
                'rule'   => 7,
                'points' => $r7,
                'desc'   => '۱۵ نقطه متوالی داخل ۱ سیگما (کاهش تنوع)',
            ];
        }

        // قانون ۸: ۸ نقطه متوالی خارج از 1σ (هر دو طرف)
        $r8 = $this->runOutside($z, 1.0, 8);
        if (!empty($r8)) {
            $violations[] = [
                'rule'   => 8,
                'points' => $r8,
                'desc'   => '۸ نقطه متوالی خارج از ۱ سیگما (هر دو طرف)',
            ];
        }

        return $violations;
    }

    /**
     * خلاصه‌ی تشخیص برای ذخیره در DB
     */
    public function detectSummary(array $series, float $cl, float $sigma): array
    {
        $violations = $this->detect($series, $cl, $sigma);
        return [
            'total_violations' => count($violations),
            'has_violation'    => !empty($violations),
            'rules_violated'   => array_map(fn($v) => $v['rule'], $violations),
            'details'          => $violations,
        ];
    }

    // ═════════════════════════════════════════════
    // متدهای کمکی
    // ═════════════════════════════════════════════

    /**
     * پیدا کردن دنباله‌های با طول مشخص که شرط را برآورده می‌کنند
     */
    private function runLength(array $z, callable $predicate, int $minLength): array
    {
        $points = [];
        $run = [];
        foreach ($z as $i => $zi) {
            if ($predicate($zi)) {
                $run[] = $i;
                if (count($run) >= $minLength) {
                    foreach ($run as $p) $points[$p] = true;
                }
            } else {
                $run = [];
            }
        }
        return array_keys($points);
    }

    /**
     * تشخیص روند صعودی/نزولی
     */
    private function trend(array $z, int $length): array
    {
        $points = [];
        $n = count($z);
        if ($n < $length) return $points;

        for ($i = 0; $i <= $n - $length; $i++) {
            $up   = true;
            $down = true;

            for ($j = $i; $j < $i + $length - 1; $j++) {
                if ($z[$j] >= $z[$j + 1]) $up   = false;
                if ($z[$j] <= $z[$j + 1]) $down = false;
            }

            if ($up || $down) {
                for ($j = $i; $j < $i + $length; $j++) {
                    $points[$j] = true;
                }
            }
        }
        return array_keys($points);
    }

    /**
     * تشخیص تناوب
     */
    private function alternating(array $z, int $length): array
    {
        $points = [];
        $n = count($z);
        if ($n < $length) return $points;

        for ($i = 0; $i <= $n - $length; $i++) {
            $ok = true;
            for ($j = $i; $j < $i + $length - 1; $j++) {
                $sign1 = $z[$j]     > 0 ? 1 : -1;
                $sign2 = $z[$j + 1] > 0 ? 1 : -1;
                if ($sign1 === $sign2) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                for ($j = $i; $j < $i + $length; $j++) {
                    $points[$j] = true;
                }
            }
        }
        return array_keys($points);
    }

    /**
     * قانون ۵: ۲ از ۳ نقطه متوالی خارج از threshold در یک سمت
     */
    private function twoOfThreeBeyond(array $z, float $threshold): array
    {
        $points = [];
        $n = count($z);

        for ($i = 0; $i <= $n - 3; $i++) {
            $above = 0;
            $below = 0;
            for ($j = $i; $j < $i + 3; $j++) {
                if ($z[$j] >  $threshold) $above++;
                if ($z[$j] < -$threshold) $below++;
            }
            if ($above >= 2 || $below >= 2) {
                for ($j = $i; $j < $i + 3; $j++) {
                    $points[$j] = true;
                }
            }
        }
        return array_keys($points);
    }

    /**
     * قانون ۶: ۴ از ۵ نقطه متوالی خارج از threshold در یک سمت
     */
    private function fourOfFiveBeyond(array $z, float $threshold): array
    {
        $points = [];
        $n = count($z);

        for ($i = 0; $i <= $n - 5; $i++) {
            $above = 0;
            $below = 0;
            for ($j = $i; $j < $i + 5; $j++) {
                if ($z[$j] >  $threshold) $above++;
                if ($z[$j] < -$threshold) $below++;
            }
            if ($above >= 4 || $below >= 4) {
                for ($j = $i; $j < $i + 5; $j++) {
                    $points[$j] = true;
                }
            }
        }
        return array_keys($points);
    }

    /**
     * قانون ۷: N نقطه متوالی داخل threshold (هر دو طرف)
     */
    private function runWithin(array $z, float $threshold, int $minLength): array
    {
        $points = [];
        $run = [];
        foreach ($z as $i => $zi) {
            if (abs($zi) < $threshold) {
                $run[] = $i;
                if (count($run) >= $minLength) {
                    foreach ($run as $p) $points[$p] = true;
                }
            } else {
                $run = [];
            }
        }
        return array_keys($points);
    }

    /**
     * قانون ۸: N نقطه متوالی خارج threshold (هر دو طرف)
     */
    private function runOutside(array $z, float $threshold, int $minLength): array
    {
        $points = [];
        $run = [];
        foreach ($z as $i => $zi) {
            if (abs($zi) > $threshold) {
                $run[] = $i;
                if (count($run) >= $minLength) {
                    foreach ($run as $p) $points[$p] = true;
                }
            } else {
                $run = [];
            }
        }
        return array_keys($points);
    }
}