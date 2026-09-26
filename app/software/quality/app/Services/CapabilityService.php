<?php
/**
 * ============================================================
 * Quality Analyzer — CapabilityService
 * ============================================================
 * مسیر: app/software/quality/app/Services/CapabilityService.php
 * محاسبه Cp, Cpk, Pp, Ppk, Cpm, Cpu, Cpl
 * مرجع: AIAG SPC Manual + ISO 22514
 * ============================================================
 */

namespace App\Software\Quality\Services;

class CapabilityService
{
    /**
     * محاسبه‌ی کامل شاخص‌های قابلیت
     *
     * @param array      $subgroups  [[v1, v2, ...], ...]
     * @param float      $lsl
     * @param float      $usl
     * @param float|null $target
     * @return array
     */
    public function compute(
        array $subgroups,
        float $lsl,
        float $usl,
        ?float $target = null
    ): array {
        if ($usl <= $lsl) {
            throw new \InvalidArgumentException('USL باید بزرگ‌تر از LSL باشد');
        }

        // جمع‌آوری مقادیر
        $allValues      = [];
        $subgroupRanges = [];
        $subgroupStds   = [];

        foreach ($subgroups as $sg) {
            $sg = array_map('floatval', $sg);
            $n  = count($sg);
            if ($n === 0) continue;

            $allValues = array_merge($allValues, $sg);

            if ($n > 1) {
                $mean = array_sum($sg) / $n;
                $subgroupRanges[] = max($sg) - min($sg);

                $var = 0.0;
                foreach ($sg as $v) {
                    $var += ($v - $mean) ** 2;
                }
                $var /= ($n - 1);
                $subgroupStds[] = sqrt($var);
            }
        }

        $totalN = count($allValues);
        if ($totalN < 2) {
            throw new \RuntimeException('حداقل ۲ مقدار لازم است');
        }

        $n = count($subgroups[0]);

        // میانگین کل
        $grandMean = array_sum($allValues) / $totalN;

        // انحراف معیار کلی (Overall / Long-term)
        $overallVar = 0.0;
        foreach ($allValues as $v) {
            $overallVar += ($v - $grandMean) ** 2;
        }
        $overallVar /= ($totalN - 1);
        $stdOverall = sqrt($overallVar);

        // انحراف معیار درون (Within / Short-term)
        $stdWithin = 0.0;
        if (!empty($subgroupRanges) && isset($this->d2()[$n])) {
            $rBar = array_sum($subgroupRanges) / count($subgroupRanges);
            $stdWithin = $rBar / $this->d2()[$n];
        } elseif (!empty($subgroupStds) && isset($this->c4()[$n])) {
            $sBar = array_sum($subgroupStds) / count($subgroupStds);
            $stdWithin = $sBar / $this->c4()[$n];
        } else {
            $stdWithin = $stdOverall;
        }

        // شاخص‌های قابلیت (Short-term)
        $cp  = null;
        $cpk = null;
        $cpu = null;
        $cpl = null;
        $cpm = null;

        if ($stdWithin > 0) {
            $cp  = ($usl - $lsl) / (6.0 * $stdWithin);
            $cpu = ($usl - $grandMean) / (3.0 * $stdWithin);
            $cpl = ($grandMean - $lsl) / (3.0 * $stdWithin);
            $cpk = min($cpu, $cpl);

            if ($target !== null) {
                $denom = 6.0 * sqrt($stdWithin ** 2 + ($grandMean - $target) ** 2);
                $cpm = $denom > 0 ? ($usl - $lsl) / $denom : null;
            }
        }

        // شاخص‌های عملکرد (Long-term)
        $pp  = null;
        $ppk = null;

        if ($stdOverall > 0) {
            $pp  = ($usl - $lsl) / (6.0 * $stdOverall);
            $ppu = ($usl - $grandMean) / (3.0 * $stdOverall);
            $ppl = ($grandMean - $lsl) / (3.0 * $stdOverall);
            $ppk = min($ppu, $ppl);
        }

        return [
            'mean'         => round($grandMean, 6),
            'std_within'   => round($stdWithin, 6),
            'std_overall'  => round($stdOverall, 6),
            'lsl'          => round($lsl, 6),
            'usl'          => round($usl, 6),
            'target'       => $target !== null ? round($target, 6) : null,
            'cp'           => $cp  !== null ? round($cp,  4) : null,
            'cpk'          => $cpk !== null ? round($cpk, 4) : null,
            'pp'           => $pp  !== null ? round($pp,  4) : null,
            'ppk'          => $ppk !== null ? round($ppk, 4) : null,
            'cpm'          => $cpm !== null ? round($cpm, 4) : null,
            'cpu'          => $cpu !== null ? round($cpu, 4) : null,
            'cpl'          => $cpl !== null ? round($cpl, 4) : null,
        ];
    }

    /**
     * ارزیابی متنی بر اساس Cpk
     */
    public function evaluate(?float $cpk): array
    {
        if ($cpk === null) {
            return ['verdict' => 'unknown', 'label' => 'نامشخص', 'color' => 'secondary'];
        }
        if ($cpk >= 1.67) {
            return ['verdict' => 'excellent',   'label' => 'عالی',      'color' => 'success'];
        }
        if ($cpk >= 1.33) {
            return ['verdict' => 'capable',     'label' => 'قابل قبول', 'color' => 'success'];
        }
        if ($cpk >= 1.00) {
            return ['verdict' => 'marginal',    'label' => 'مرزی',      'color' => 'warning'];
        }
        return     ['verdict' => 'not_capable', 'label' => 'ناتوان',    'color' => 'danger'];
    }

    /**
     * تخمین PPM خارج از مشخصات (توزیع نرمال)
     */
    public function estimatePpm(float $mean, float $std, float $lsl, float $usl): array
    {
        if ($std <= 0) {
            return ['ppm_below' => 0.0, 'ppm_above' => 0.0, 'ppm_total' => 0.0];
        }
        $zLsl = ($lsl - $mean) / $std;
        $zUsl = ($usl - $mean) / $std;

        $pBelow = $this->normalCdf($zLsl);
        $pAbove = 1.0 - $this->normalCdf($zUsl);

        return [
            'ppm_below' => round($pBelow * 1_000_000, 2),
            'ppm_above' => round($pAbove * 1_000_000, 2),
            'ppm_total' => round(($pBelow + $pAbove) * 1_000_000, 2),
        ];
    }

    // ═════════════════════════════════════════════
    // کمکی
    // ═════════════════════════════════════════════

    /**
     * CDF نرمال استاندارد (تقریب Abramowitz & Stegun)
     */
    private function normalCdf(float $z): float
    {
        $t = 1.0 / (1.0 + 0.2316419 * abs($z));
        $d = 0.3989423 * exp(-$z * $z / 2.0);
        $p = $d * $t * (
            0.3193815 +
            $t * (-0.3565638 +
            $t * (1.781478 +
            $t * (-1.821256 +
            $t * 1.330274)))
        );
        return $z > 0 ? 1.0 - $p : $p;
    }

    private function d2(): array
    {
        return [
            2 => 1.128, 3 => 1.693, 4 => 2.059, 5 => 2.326,
            6 => 2.534, 7 => 2.704, 8 => 2.847, 9 => 2.970, 10 => 3.078,
        ];
    }

    private function c4(): array
    {
        return [
            2 => 0.7979, 3 => 0.8862, 4 => 0.9213, 5 => 0.9400,
            6 => 0.9515, 7 => 0.9594, 8 => 0.9650, 9 => 0.9693, 10 => 0.9727,
        ];
    }
}