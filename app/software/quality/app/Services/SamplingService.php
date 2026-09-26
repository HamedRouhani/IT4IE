<?php
/**
 * ============================================================
 * Quality Analyzer — SamplingService
 * ============================================================
 * مسیر: app/software/quality/app/Services/SamplingService.php
 * 
 * محاسبات Acceptance Sampling:
 *   - OC Curve (Operating Characteristic)
 *   - Producer's Risk (α)
 *   - Consumer's Risk (β)
 *   - AOQL (Average Outgoing Quality Limit)
 *   - ATI (Average Total Inspection)
 * مرجع: MIL-STD-105E / ISO 2859-1
 * ============================================================
 */

namespace App\Software\Quality\Services;

class SamplingService
{
    /**
     * توزیع دو جمله‌ای — احتمال دقیقاً k موفقیت از n آزمایش
     */
    private function binomialPmf(int $k, int $n, float $p): float
    {
        if ($k < 0 || $k > $n) return 0.0;
        if ($p <= 0) return $k === 0 ? 1.0 : 0.0;
        if ($p >= 1) return $k === $n ? 1.0 : 0.0;

        // log برای جلوگیری از overflow
        $logC = $this->logCombination($n, $k);
        $logP = $logC + $k * log($p) + ($n - $k) * log(1 - $p);
        return exp($logP);
    }

    /**
     * لگاریتم طبیعی ترکیب C(n, k)
     */
    private function logCombination(int $n, int $k): float
    {
        if ($k < 0 || $k > $n) return -INF;
        if ($k === 0 || $k === $n) return 0.0;
        // log C(n,k) = logGamma(n+1) - logGamma(k+1) - logGamma(n-k+1)
        return $this->logGamma($n + 1) - $this->logGamma($k + 1) - $this->logGamma($n - $k + 1);
    }

    /**
     * لگاریتم تابع گاما (Lanczos approximation)
     */
    private function logGamma(float $z): float
    {
        if ($z < 0.5) {
            // reflection formula
            return log(M_PI / sin(M_PI * $z)) - $this->logGamma(1 - $z);
        }
        $z -= 1;
        $x = 0.99999999999980993;
        $coef = [
            676.5203681218851,
            -1259.1392167224028,
            771.32342877765313,
            -176.61502916214059,
            12.507343278686905,
            -0.13857109526572012,
            9.9843695780195716e-6,
            1.5056327351493116e-7,
        ];
        for ($i = 0; $i < 8; $i++) {
            $x += $coef[$i] / ($z + $i + 1);
        }
        $t = $z + 7.5;
        return 0.5 * log(2 * M_PI) + ($z + 0.5) * log($t) - $t + log($x);
    }

    /**
     * احتمال پذیرش (Pa) برای یک p معین
     */
    public function acceptanceProbability(int $n, int $c, float $p): float
    {
        $pa = 0.0;
        for ($k = 0; $k <= $c; $k++) {
            $pa += $this->binomialPmf($k, $n, $p);
        }
        return $pa;
    }

    /**
     * ساخت منحنی OC
     *
     * @return array  [['p' => 0.01, 'pa' => 0.95], ...]
     */
    public function computeOcCurve(int $n, int $c, int $points = 51): array
    {
        $curve = [];
        for ($i = 0; $i < $points; $i++) {
            $p = $i / ($points - 1);  // 0 تا 1
            $curve[] = [
                'p'  => round($p, 4),
                'pa' => round($this->acceptanceProbability($n, $c, $p), 6),
            ];
        }
        return $curve;
    }

    /**
     * محاسبه ریسک‌ها
     *
     * @param int   $n           حجم نمونه
     * @param int   $c           عدد پذیرش
     * @param float $aqlPercent  AQL به درصد
     * @param float $ltpdPercent LTPD به درصد
     * @return array
     */
    public function computeRisks(int $n, int $c, float $aqlPercent, float $ltpdPercent): array
    {
        $aql  = $aqlPercent  / 100.0;
        $ltpd = $ltpdPercent / 100.0;

        $paAtAql  = $this->acceptanceProbability($n, $c, $aql);
        $paAtLtpd = $this->acceptanceProbability($n, $c, $ltpd);

        return [
            'producer_risk' => round(1.0 - $paAtAql, 6),    // α = 1 - Pa(AQL)
            'consumer_risk' => round($paAtLtpd, 6),         // β = Pa(LTPD)
            'pa_at_aql'     => round($paAtAql, 6),
            'pa_at_ltpd'    => round($paAtLtpd, 6),
        ];
    }

    /**
     * محاسبه AOQL (تقریبی)
     */
    public function computeAoql(int $n, int $c, int $lotSize): float
    {
        $maxAoq = 0.0;
        $step = 0.005;
        for ($p = 0.0; $p <= 1.0; $p += $step) {
            $pa = $this->acceptanceProbability($n, $c, $p);
            // AOQ = p × Pa × (N-n)/N
            $aoq = $p * $pa * (($lotSize - $n) / $lotSize);
            if ($aoq > $maxAoq) $maxAoq = $aoq;
        }
        return round($maxAoq, 6);
    }

    /**
     * ATI (Average Total Inspection)
     */
    public function computeAti(int $n, int $c, int $lotSize, float $p): float
    {
        $pa = $this->acceptanceProbability($n, $c, $p);
        return round($n + (1 - $pa) * ($lotSize - $n), 2);
    }

    /**
     * یافتن مقادیر c و n برای AQL/LTPD داده‌شده
     * (جستجوی عددی)
     */
    public function findPlan(float $aqlPercent, float $ltpdPercent, float $alpha = 0.05, float $beta = 0.10): array
    {
        $best = null;
        $bestScore = INF;

        // جستجو برای n بین ۵ و ۵۰۰
        for ($n = 5; $n <= 500; $n++) {
            for ($c = 0; $c <= min(20, $n - 1); $c++) {
                $paAql  = $this->acceptanceProbability($n, $c, $aqlPercent / 100);
                $paLtpd = $this->acceptanceProbability($n, $c, $ltpdPercent / 100);

                $alphaActual = 1 - $paAql;
                $betaActual  = $paLtpd;

                // اختلاف با هدف
                $score = abs($alphaActual - $alpha) + abs($betaActual - $beta);

                // شرط: α ≤ هدف و β ≤ هدف
                if ($alphaActual <= $alpha && $betaActual <= $beta && $score < $bestScore) {
                    $bestScore = $score;
                    $best = [
                        'sample_size'   => $n,
                        'accept_number' => $c,
                        'alpha_actual'  => $alphaActual,
                        'beta_actual'   => $betaActual,
                    ];
                }
            }
            // اگه جواب پیدا شد، متوقف کن
            if ($best !== null) break;
        }

        return $best ?? ['error' => 'طرح مناسبی پیدا نشد'];
    }

    /**
     * جدول طرح‌های پیشنهادی MIL-STD-105E (نمونه)
     */
    public function getStandardPlans(): array
    {
        return [
            ['aql' => 0.65, 'lot_range' => [2, 8],       'code' => 'A', 'n' => 2,  'c' => 0, 'r' => 1],
            ['aql' => 1.0,  'lot_range' => [9, 15],      'code' => 'B', 'n' => 3,  'c' => 0, 'r' => 1],
            ['aql' => 1.5,  'lot_range' => [16, 25],     'code' => 'C', 'n' => 5,  'c' => 0, 'r' => 1],
            ['aql' => 2.5,  'lot_range' => [26, 50],     'code' => 'D', 'n' => 8,  'c' => 0, 'r' => 1],
            ['aql' => 4.0,  'lot_range' => [51, 90],     'code' => 'E', 'n' => 13, 'c' => 0, 'r' => 1],
            ['aql' => 6.5,  'lot_range' => [91, 150],    'code' => 'F', 'n' => 20, 'c' => 1, 'r' => 2],
            ['aql' => 10.0, 'lot_range' => [151, 280],   'code' => 'G', 'n' => 32, 'c' => 2, 'r' => 3],
            ['aql' => 15.0, 'lot_range' => [281, 500],   'code' => 'H', 'n' => 50, 'c' => 3, 'r' => 4],
        ];
    }
}