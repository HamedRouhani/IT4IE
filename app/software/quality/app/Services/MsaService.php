<?php
/**
 * ============================================================
 * Quality Analyzer — MsaService
 * ============================================================
 * مسیر: app/software/quality/app/Services/MsaService.php
 * Gage R&R — ANOVA Method
 * مرجع: AIAG MSA Manual (4th Edition) — Chapter III
 * ============================================================
 */

namespace App\Software\Quality\Services;

class MsaService
{
    /**
     * محاسبه‌ی کامل Gage R&R (ANOVA)
     *
     * @param array $data  [
     *   'parts'     => p,
     *   'operators' => o,
     *   'trials'    => r,
     *   'values'    => [ [ [t1,t2,...], [t1,t2,...] ], ... ]  // [operator][part][trial]
     * ]
     * @return array
     */
    public function computeAnova(array $data): array
    {
        $p = (int) ($data['parts']     ?? 0);
        $o = (int) ($data['operators'] ?? 0);
        $r = (int) ($data['trials']    ?? 0);
        $values = $data['values'] ?? [];

        if ($p < 2 || $o < 2 || $r < 2) {
            throw new \RuntimeException('حداقل ۲ قطعه، ۲ اپراتور و ۲ تکرار لازم است');
        }

        // ── میانگین‌ها
        $grandSum = 0.0;
        $grandN   = 0;

        $partSums   = array_fill(0, $p, 0.0);
        $partCounts = array_fill(0, $p, 0);

        $opSums     = array_fill(0, $o, 0.0);
        $opCounts   = array_fill(0, $o, 0);

        $cellMeans  = [];
        $cellCounts = [];

        for ($op = 0; $op < $o; $op++) {
            for ($pt = 0; $pt < $p; $pt++) {
                $cellVals = $values[$op][$pt] ?? [];
                $cellN    = count($cellVals);
                $cellSum  = array_sum($cellVals);

                $cellMeans[$op][$pt]  = $cellN > 0 ? $cellSum / $cellN : 0.0;
                $cellCounts[$op][$pt] = $cellN;

                $partSums[$pt]   += $cellSum;
                $partCounts[$pt] += $cellN;

                $opSums[$op]   += $cellSum;
                $opCounts[$op] += $cellN;

                $grandSum += $cellSum;
                $grandN   += $cellN;
            }
        }

        $grandMean = $grandSum / max(1, $grandN);

        $partMeans = [];
        for ($pt = 0; $pt < $p; $pt++) {
            $partMeans[$pt] = $partCounts[$pt] > 0
                ? $partSums[$pt] / $partCounts[$pt]
                : 0.0;
        }

        $opMeans = [];
        for ($op = 0; $op < $o; $op++) {
            $opMeans[$op] = $opCounts[$op] > 0
                ? $opSums[$op] / $opCounts[$op]
                : 0.0;
        }

        // ── مجموع مربعات
        $ssTotal = 0.0;
        for ($op = 0; $op < $o; $op++) {
            for ($pt = 0; $pt < $p; $pt++) {
                foreach (($values[$op][$pt] ?? []) as $v) {
                    $ssTotal += ((float) $v - $grandMean) ** 2;
                }
            }
        }

        $ssPart = 0.0;
        for ($pt = 0; $pt < $p; $pt++) {
            $ssPart += $o * $r * ($partMeans[$pt] - $grandMean) ** 2;
        }

        $ssOp = 0.0;
        for ($op = 0; $op < $o; $op++) {
            $ssOp += $p * $r * ($opMeans[$op] - $grandMean) ** 2;
        }

        $ssInter = 0.0;
        for ($op = 0; $op < $o; $op++) {
            for ($pt = 0; $pt < $p; $pt++) {
                $ssInter += $r * (
                    ($cellMeans[$op][$pt] - $grandMean)
                    - ($partMeans[$pt] - $grandMean)
                    - ($opMeans[$op] - $grandMean)
                ) ** 2;
            }
        }

        $ssError = $ssTotal - $ssPart - $ssOp - $ssInter;

        // ── درجه آزادی
        $dfPart  = $p - 1;
        $dfOp    = $o - 1;
        $dfInter = ($p - 1) * ($o - 1);
        $dfError = $p * $o * ($r - 1);

        // ── میانگین مربعات
        $msPart  = $dfPart  > 0 ? $ssPart  / $dfPart  : 0.0;
        $msOp    = $dfOp    > 0 ? $ssOp    / $dfOp    : 0.0;
        $msInter = $dfInter > 0 ? $ssInter / $dfInter : 0.0;
        $msError = $dfError > 0 ? $ssError / $dfError : 0.0;

        // ── واریانس‌ها
        $varRepeat = $msError;
        $varInter  = max(0.0, ($msInter - $msError) / max(1, $r));
        $varOp     = max(0.0, ($msOp    - $msInter) / max(1, $p * $r));
        $varPart   = max(0.0, ($msPart  - $msInter) / max(1, $o * $r));

        // ── انحراف معیارها
        $ev  = sqrt($varRepeat);
        $av  = sqrt($varOp + $varInter);
        $grr = sqrt($ev ** 2 + $av ** 2);
        $pv  = sqrt($varPart);
        $tv  = sqrt($grr ** 2 + $pv ** 2);

        // ── درصدها
        $pctEv  = $tv > 0 ? 100.0 * $ev  / $tv : 0.0;
        $pctAv  = $tv > 0 ? 100.0 * $av  / $tv : 0.0;
        $pctGrr = $tv > 0 ? 100.0 * $grr / $tv : 0.0;
        $pctPv  = $tv > 0 ? 100.0 * $pv  / $tv : 0.0;

        // ── ndc
        $ndc = $grr > 0 ? (int) floor(1.41 * ($pv / $grr)) : 0;

        // ── ارزیابی
        $verdict = $this->verdict($pctGrr);

        return [
            'ev'      => round($ev,  6),
            'av'      => round($av,  6),
            'grr'     => round($grr, 6),
            'pv'      => round($pv,  6),
            'tv'      => round($tv,  6),
            'pct_ev'  => round($pctEv,  2),
            'pct_av'  => round($pctAv,  2),
            'pct_grr' => round($pctGrr, 2),
            'pct_pv'  => round($pctPv,  2),
            'ndc'     => $ndc,
            'verdict' => $verdict,
            'anova'   => [
                'ss_part'        => round($ssPart,  6),
                'ss_op'          => round($ssOp,    6),
                'ss_interaction' => round($ssInter, 6),
                'ss_error'       => round($ssError, 6),
                'ss_total'       => round($ssTotal, 6),
                'ms_part'        => round($msPart,  6),
                'ms_op'          => round($msOp,    6),
                'ms_interaction' => round($msInter, 6),
                'ms_error'       => round($msError, 6),
            ],
        ];
    }

    /**
     * ارزیابی بر اساس %GRR
     */
    public function verdict(float $pctGrr): string
    {
        if ($pctGrr < 10.0)  return 'acceptable';
        if ($pctGrr <= 30.0) return 'marginal';
        return 'unacceptable';
    }

    /**
     * برچسب فارسی ارزیابی
     */
    public function verdictLabel(float $pctGrr): string
    {
        return match ($this->verdict($pctGrr)) {
            'acceptable'   => 'قابل قبول',
            'marginal'     => 'مرزی',
            'unacceptable' => 'غیرقابل قبول',
        };
    }
}