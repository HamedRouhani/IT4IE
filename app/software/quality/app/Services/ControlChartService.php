<?php
/**
 * ============================================================
 * Quality Analyzer — ControlChartService
 * ============================================================
 * مسیر: app/software/quality/app/Services/ControlChartService.php
 * محاسبه CL/UCL/LCL برای ۷ نوع نمودار کنترل
 * مرجع: AIAG SPC Manual (2nd Edition)
 * ============================================================
 */

namespace App\Software\Quality\Services;

class ControlChartService
{
    // ═════════════════════════════════════════════
    // ثابت‌های جدول AIAG (n = 2..10)
    // ═════════════════════════════════════════════

    private const A2 = [
        2 => 1.880, 3 => 1.023, 4 => 0.729, 5 => 0.577,
        6 => 0.483, 7 => 0.419, 8 => 0.373, 9 => 0.337, 10 => 0.308,
    ];

    private const D3 = [
        2 => 0.000, 3 => 0.000, 4 => 0.000, 5 => 0.000,
        6 => 0.000, 7 => 0.076, 8 => 0.136, 9 => 0.184, 10 => 0.223,
    ];

    private const D4 = [
        2 => 3.267, 3 => 2.574, 4 => 2.282, 5 => 2.114,
        6 => 2.004, 7 => 1.924, 8 => 1.864, 9 => 1.816, 10 => 1.777,
    ];

    private const B3 = [
        2 => 0.000, 3 => 0.000, 4 => 0.000, 5 => 0.000,
        6 => 0.030, 7 => 0.118, 8 => 0.185, 9 => 0.239, 10 => 0.284,
    ];

    private const B4 = [
        2 => 3.267, 3 => 2.568, 4 => 2.266, 5 => 2.089,
        6 => 1.970, 7 => 1.882, 8 => 1.815, 9 => 1.761, 10 => 1.716,
    ];

    private const C4 = [
        2 => 0.7979, 3 => 0.8862, 4 => 0.9213, 5 => 0.9400,
        6 => 0.9515, 7 => 0.9594, 8 => 0.9650, 9 => 0.9693, 10 => 0.9727,
    ];

    private const D2 = [
        2 => 1.128, 3 => 1.693, 4 => 2.059, 5 => 2.326,
        6 => 2.534, 7 => 2.704, 8 => 2.847, 9 => 2.970, 10 => 3.078,
    ];

    // ═════════════════════════════════════════════
    // نقطه ورود اصلی
    // ═════════════════════════════════════════════

    /**
     * محاسبه‌ی نمودار بر اساس نوع
     *
     * @param string $chartType  xbar_r | xbar_s | i_mr | p | np | c | u
     * @param array  $data       برای متغیر: [[v1,v2,...], ...]
     *                           برای صفتی: [['defectives'=>x,'defects'=>y,'sample_size'=>n], ...]
     * @return array
     */
    public function compute(string $chartType, array $data): array
    {
        return match ($chartType) {
            'xbar_r' => $this->computeXbarR($data),
            'xbar_s' => $this->computeXbarS($data),
            'i_mr'   => $this->computeIMR($data),
            'p'      => $this->computeP($data),
            'np'     => $this->computeNp($data),
            'c'      => $this->computeC($data),
            'u'      => $this->computeU($data),
            default  => throw new \InvalidArgumentException("نوع نمودار نامعتبر: $chartType"),
        };
    }

    // ═════════════════════════════════════════════
    // X̄-R
    // ═════════════════════════════════════════════

    private function computeXbarR(array $subgroups): array
    {
        $k = count($subgroups);
        if ($k < 2) {
            throw new \RuntimeException('حداقل ۲ زیرگروه لازم است');
        }

        $n = count($subgroups[0]);
        if ($n < 2 || $n > 10) {
            throw new \RuntimeException('اندازه زیرگروه باید بین ۲ و ۱۰ باشد');
        }

        $xbar = [];
        $r    = [];
        foreach ($subgroups as $sg) {
            $sg = array_map('floatval', $sg);
            $xbar[] = array_sum($sg) / count($sg);
            $r[]    = max($sg) - min($sg);
        }

        $xbarBar = array_sum($xbar) / $k;
        $rBar    = array_sum($r) / $k;

        $a2 = self::A2[$n];
        $d3 = self::D3[$n];
        $d4 = self::D4[$n];

        return [
            'center_line'   => round($xbarBar, 6),
            'ucl'           => round($xbarBar + $a2 * $rBar, 6),
            'lcl'           => round($xbarBar - $a2 * $rBar, 6),
            'r_bar'         => round($rBar, 6),
            'r_ucl'         => round($d4 * $rBar, 6),
            'r_lcl'         => round($d3 * $rBar, 6),
            'sigma_hat'     => round($rBar / self::D2[$n], 6),
            'xbar_series'   => array_map(fn($v) => round($v, 6), $xbar),
            'r_series'      => array_map(fn($v) => round($v, 6), $r),
            'subgroup_size' => $n,
            'num_subgroups' => $k,
        ];
    }

    // ═════════════════════════════════════════════
    // X̄-S
    // ═════════════════════════════════════════════

    private function computeXbarS(array $subgroups): array
    {
        $k = count($subgroups);
        if ($k < 2) {
            throw new \RuntimeException('حداقل ۲ زیرگروه لازم است');
        }

        $n = count($subgroups[0]);
        if ($n < 2 || $n > 10) {
            throw new \RuntimeException('اندازه زیرگروه باید بین ۲ و ۱۰ باشد');
        }

        $xbar = [];
        $s    = [];
        foreach ($subgroups as $sg) {
            $sg = array_map('floatval', $sg);
            $mean = array_sum($sg) / count($sg);
            $xbar[] = $mean;

            $variance = 0.0;
            foreach ($sg as $v) {
                $variance += ($v - $mean) ** 2;
            }
            $variance /= (count($sg) - 1);
            $s[] = sqrt($variance);
        }

        $xbarBar = array_sum($xbar) / $k;
        $sBar    = array_sum($s) / $k;
        $c4      = self::C4[$n];

        // UCL/LCL برای X̄: X̄̄ ± 3·S̄/(c4·√n)
        $a3 = 3.0 / ($c4 * sqrt($n));

        // UCL/LCL برای S: S̄ ± 3·S̄·√(1-c4²)/c4
        $b3 = max(0.0, 1.0 - 3.0 * sqrt(1.0 - $c4 ** 2) / $c4);
        $b4 = 1.0 + 3.0 * sqrt(1.0 - $c4 ** 2) / $c4;

        return [
            'center_line'   => round($xbarBar, 6),
            'ucl'           => round($xbarBar + $a3 * $sBar, 6),
            'lcl'           => round($xbarBar - $a3 * $sBar, 6),
            's_bar'         => round($sBar, 6),
            's_ucl'         => round($b4 * $sBar, 6),
            's_lcl'         => round($b3 * $sBar, 6),
            'sigma_hat'     => round($sBar / $c4, 6),
            'xbar_series'   => array_map(fn($v) => round($v, 6), $xbar),
            's_series'      => array_map(fn($v) => round($v, 6), $s),
            'subgroup_size' => $n,
            'num_subgroups' => $k,
        ];
    }

    // ═════════════════════════════════════════════
    // I-MR
    // ═════════════════════════════════════════════

    private function computeIMR(array $subgroups): array
    {
        // در I-MR هر مقدار یک نمونه است
        $values = [];
        foreach ($subgroups as $sg) {
            if (is_array($sg)) {
                $values[] = (float) $sg[0];
            } else {
                $values[] = (float) $sg;
            }
        }

        $k = count($values);
        if ($k < 2) {
            throw new \RuntimeException('حداقل ۲ مقدار لازم است');
        }

        $xbar = array_sum($values) / $k;

        // دامنه متحرک
        $mr = [];
        for ($i = 1; $i < $k; $i++) {
            $mr[] = abs($values[$i] - $values[$i - 1]);
        }
        $mrBar = array_sum($mr) / count($mr);

        $d2    = self::D2[2]; // n=2
        $sigma = $mrBar / $d2;

        return [
            'center_line' => round($xbar, 6),
            'ucl'         => round($xbar + 3.0 * $sigma, 6),
            'lcl'         => round($xbar - 3.0 * $sigma, 6),
            'mr_bar'      => round($mrBar, 6),
            'mr_ucl'      => round(self::D4[2] * $mrBar, 6),
            'mr_lcl'      => 0.0,
            'sigma_hat'   => round($sigma, 6),
            'xbar_series' => array_map(fn($v) => round($v, 6), $values),
            'mr_series'   => array_map(fn($v) => round($v, 6), $mr),
        ];
    }

    // ═════════════════════════════════════════════
    // p-chart (نسبت معیوب)
    // ═════════════════════════════════════════════

    private function computeP(array $data): array
    {
        if (empty($data)) {
            throw new \RuntimeException('داده خالی است');
        }

        $totalN = 0;
        $totalD = 0;
        foreach ($data as $row) {
            $totalN += (int) $row['sample_size'];
            $totalD += (int) $row['defectives'];
        }

        if ($totalN === 0) {
            throw new \RuntimeException('مجموع نمونه‌ها صفر است');
        }

        $pBar = $totalD / $totalN;

        $series  = [];
        $uclList = [];
        $lclList = [];

        foreach ($data as $row) {
            $n = (int) $row['sample_size'];
            $d = (int) $row['defectives'];

            $p = $n > 0 ? $d / $n : 0.0;
            $series[] = round($p, 6);

            if ($n > 0) {
                $sigma = sqrt($pBar * (1 - $pBar) / $n);
                $uclList[] = round($pBar + 3.0 * $sigma, 6);
                $lclList[] = round(max(0.0, $pBar - 3.0 * $sigma), 6);
            } else {
                $uclList[] = round($pBar, 6);
                $lclList[] = round($pBar, 6);
            }
        }

        // UCL/LCL کل (بر اساس میانگین n)
        $nAvg = $totalN / count($data);
        $sigmaAvg = sqrt($pBar * (1 - $pBar) / max(1, $nAvg));

        return [
            'center_line' => round($pBar, 6),
            'ucl'         => round($pBar + 3.0 * $sigmaAvg, 6),
            'lcl'         => round(max(0.0, $pBar - 3.0 * $sigmaAvg), 6),
            'p_bar'       => round($pBar, 6),
            'sigma_hat'   => round($sigmaAvg, 6),
            'series'      => $series,
            'ucl_series'  => $uclList,
            'lcl_series'  => $lclList,
        ];
    }

    // ═════════════════════════════════════════════
    // np-chart
    // ═════════════════════════════════════════════

    private function computeNp(array $data): array
    {
        if (empty($data)) {
            throw new \RuntimeException('داده خالی است');
        }

        $k = count($data);
        $totalN = 0;
        $totalD = 0;
        foreach ($data as $row) {
            $totalN += (int) $row['sample_size'];
            $totalD += (int) $row['defectives'];
        }

        $nBar  = $totalN / $k;
        $npBar = $totalD / $k;

        $sigma = sqrt($npBar * (1 - $npBar / max(1, $nBar)));

        return [
            'center_line' => round($npBar, 6),
            'ucl'         => round($npBar + 3.0 * $sigma, 6),
            'lcl'         => round(max(0.0, $npBar - 3.0 * $sigma), 6),
            'np_bar'      => round($npBar, 6),
            'sigma_hat'   => round($sigma, 6),
            'series'      => array_map(fn($r) => (int) $r['defectives'], $data),
        ];
    }

    // ═════════════════════════════════════════════
    // c-chart
    // ═════════════════════════════════════════════

    private function computeC(array $data): array
    {
        if (empty($data)) {
            throw new \RuntimeException('داده خالی است');
        }

        $k = count($data);
        $totalC = 0;
        foreach ($data as $row) {
            $totalC += (int) $row['defects'];
        }

        $cBar  = $totalC / $k;
        $sigma = sqrt($cBar);

        return [
            'center_line' => round($cBar, 6),
            'ucl'         => round($cBar + 3.0 * $sigma, 6),
            'lcl'         => round(max(0.0, $cBar - 3.0 * $sigma), 6),
            'c_bar'       => round($cBar, 6),
            'sigma_hat'   => round($sigma, 6),
            'series'      => array_map(fn($r) => (int) $r['defects'], $data),
        ];
    }

    // ═════════════════════════════════════════════
    // u-chart
    // ═════════════════════════════════════════════

    private function computeU(array $data): array
    {
        if (empty($data)) {
            throw new \RuntimeException('داده خالی است');
        }

        $totalC = 0;
        $totalN = 0;
        foreach ($data as $row) {
            $totalC += (int) $row['defects'];
            $totalN += (int) $row['sample_size'];
        }

        if ($totalN === 0) {
            throw new \RuntimeException('مجموع نمونه‌ها صفر است');
        }

        $uBar = $totalC / $totalN;

        $series  = [];
        $uclList = [];
        $lclList = [];

        foreach ($data as $row) {
            $n = (int) $row['sample_size'];
            $c = (int) $row['defects'];

            $u = $n > 0 ? $c / $n : 0.0;
            $series[] = round($u, 6);

            if ($n > 0) {
                $sigma = sqrt($uBar / $n);
                $uclList[] = round($uBar + 3.0 * $sigma, 6);
                $lclList[] = round(max(0.0, $uBar - 3.0 * $sigma), 6);
            } else {
                $uclList[] = round($uBar, 6);
                $lclList[] = round($uBar, 6);
            }
        }

        $nAvg = $totalN / count($data);
        $sigmaAvg = sqrt($uBar / max(1, $nAvg));

        return [
            'center_line' => round($uBar, 6),
            'ucl'         => round($uBar + 3.0 * $sigmaAvg, 6),
            'lcl'         => round(max(0.0, $uBar - 3.0 * $sigmaAvg), 6),
            'u_bar'       => round($uBar, 6),
            'sigma_hat'   => round($sigmaAvg, 6),
            'series'      => $series,
            'ucl_series'  => $uclList,
            'lcl_series'  => $lclList,
        ];
    }
}