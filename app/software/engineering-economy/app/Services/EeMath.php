<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Services;

/**
 * هستهٔ محاسبات اقتصاد مهندسی.
 *
 * همهٔ توابع خالص‌اند (بدون دیتابیس و بدون وضعیت) و بر پایهٔ جریان نقدی خالص
 * دوره‌به‌دوره کار می‌کنند: شاخص ۰ سرمایه‌گذاری اولیه است.
 *
 * قرارداد: جریان نقدی «خالص» = inflow − outflow در هر دوره. برای نسبت فایده/هزینه
 * از bcRatio() استفاده کنید که inflow و outflow را جدا می‌گیرد.
 */
final class EeMath
{
    /** خطای نسبی پذیرفتنی در حل ریشه و مقایسه. */
    private const EPSILON = 1.0e-10;

    /** بیشینهٔ دورهٔ قابل بررسی (هم‌راستا با محدودیت اسکیما). */
    public const MAX_PERIODS = 100;

    /* ==================================================================
     * فاکتورهای ارزش زمانی پول
     * ================================================================== */

    /** (P/F, i, n) = (1+i)^-n */
    public static function factorPF(float $rate, int $periods): float
    {
        if ($periods < 0) return 0.0;
        if (abs($rate) < self::EPSILON) return 1.0;

        return 1.0 / ((1.0 + $rate) ** $periods);
    }

    /** (F/P, i, n) = (1+i)^n */
    public static function factorFP(float $rate, int $periods): float
    {
        if ($periods < 0) return 0.0;

        return (1.0 + $rate) ** $periods;
    }

    /** (P/A, i, n) = ((1+i)^n − 1) / (i(1+i)^n) */
    public static function factorPA(float $rate, int $periods): float
    {
        if ($periods < 1) return 0.0;
        if (abs($rate) < self::EPSILON) return (float) $periods;

        $growth = (1.0 + $rate) ** $periods;

        return ($growth - 1.0) / ($rate * $growth);
    }

    /** (A/P, i, n) */
    public static function factorAP(float $rate, int $periods): float
    {
        $factor = self::factorPA($rate, $periods);

        return $factor > 0.0 ? 1.0 / $factor : 0.0;
    }

    /** (F/A, i, n) */
    public static function factorFA(float $rate, int $periods): float
    {
        if ($periods < 1) return 0.0;
        if (abs($rate) < self::EPSILON) return (float) $periods;

        return (((1.0 + $rate) ** $periods) - 1.0) / $rate;
    }

    /** (A/F, i, n) */
    public static function factorAF(float $rate, int $periods): float
    {
        $factor = self::factorFA($rate, $periods);

        return $factor > 0.0 ? 1.0 / $factor : 0.0;
    }

    /** (P/G, i, n) برای گرادیان حسابی */
    public static function factorPG(float $rate, int $periods): float
    {
        if ($periods < 1) return 0.0;
        if (abs($rate) < self::EPSILON) {
            return (float) ($periods * ($periods + 1) / 2);
        }

        return (self::factorPA($rate, $periods) - $periods * self::factorPF($rate, $periods)) / $rate;
    }

    /** (A/G, i, n) */
    public static function factorAG(float $rate, int $periods): float
    {
        if ($periods < 1) return 0.0;
        if (abs($rate) < self::EPSILON) {
            return (float) (($periods - 1) / 2.0);
        }

        return (1.0 / $rate) - ($periods / (((1.0 + $rate) ** $periods) - 1.0));
    }

    /** ارزش فعلی گرادیان هندسی با رشد ثابت g (اولین مبلغ در دورهٔ ۱) */
    public static function factorGeometricPA(float $rate, float $growth, int $periods): float
    {
        if ($periods < 1) return 0.0;
        if (abs($rate - $growth) < self::EPSILON) {
            return $periods / (1.0 + $rate);
        }

        return (1.0 - (($growth + 1.0) / ($rate + 1.0)) ** $periods) / ($rate - $growth);
    }

    /* ==================================================================
     * معیارهای معادل و تصمیم
     * ================================================================== */

    /**
     * ارزش فعلی خالص.
     *
     * @param array<int,float> $flows جریان خالص؛ کلید = دوره
     */
    public static function npv(float $rate, array $flows): float
    {
        $total = 0.0;
        foreach ($flows as $period => $flow) {
            $total += (float) $flow * self::factorPF($rate, (int) $period);
        }

        return $total;
    }

    /** ارزش آتی در پایان دورهٔ n */
    public static function futureWorth(float $rate, array $flows, int $periods): float
    {
        return self::npv($rate, $flows) * self::factorFP($rate, $periods);
    }

    /** معادل سالانهٔ یکنواخت */
    public static function annualWorth(float $rate, array $flows, int $periods): ?float
    {
        if ($periods < 1) return null;

        return self::npv($rate, $flows) * self::factorAP($rate, $periods);
    }

    /** هزینهٔ یکنواخت سالانهٔ سرمایه (EUAC) */
    public static function euac(float $rate, float $firstCost, float $salvage, float $annualCost, int $life): ?float
    {
        if ($life < 1) return null;

        return ($firstCost * self::factorAP($rate, $life))
            - ($salvage * self::factorAF($rate, $life))
            + $annualCost;
    }

    /** هزینهٔ سرمایه‌شده برای عمر نامحدود: P + A/i */
    public static function capitalizedCost(float $rate, float $firstCost, float $annualCost): ?float
    {
        if ($rate <= 0.0) return null;

        return $firstCost + ($annualCost / $rate);
    }

    /**
     * ریشه‌های نرخ بازدهٔ داخلی (NPV = 0).
     *
     * ابتدا بازهٔ [-۹۹.۹۹٪, +۱۰۰۰٪] پویش می‌شود تا همهٔ تغییرهای علامت پیدا شوند؛
     * سپس هر بازه با روش دوبخشی حل می‌شود. امکان چند ریشه (مسئلهٔ کلاسیک IRR) وجود دارد.
     *
     * @return array<int,float> نرخ‌های ریشه به‌ترتیب صعودی
     */
    public static function irrRoots(array $flows): array
    {
        $clean = self::compactFlows($flows);
        if (count($clean) < 2) return [];

        $lower = -0.9999;
        $upper = 10.0;
        $steps = 400;
        $step = ($upper - $lower) / $steps;

        $roots = [];
        $previousRate = $lower;
        $previousValue = self::npv($previousRate, $clean);
        $previousSign = self::sign($previousValue);

        for ($index = 1; $index <= $steps; $index++) {
            $rate = $lower + ($index * $step);
            $value = self::npv($rate, $clean);
            $sign = self::sign($value);

            if ($sign === 0) {
                $roots[] = $rate;
            } elseif ($sign !== $previousSign && $previousSign !== 0) {
                $root = self::bisect(static fn(float $candidate): float => self::npv($candidate, $clean), $previousRate, $rate);
                if ($root !== null) $roots[] = $root;
            } elseif ($previousSign === 0) {
                $root = self::bisect(static fn(float $candidate): float => self::npv($candidate, $clean), $previousRate, $rate);
                if ($root !== null) $roots[] = $root;
            }

            $previousRate = $rate;
            $previousSign = $sign;
        }

        return self::uniqueSorted($roots);
    }

    /** نخستین ریشهٔ IRR (برای نمایش سریع) */
    public static function irr(array $flows): ?float
    {
        $roots = self::irrRoots($flows);

        return $roots === [] ? null : $roots[0];
    }

    /** تعداد تغییر علامت جریان نقدی: بیشتر از یک = احتمال چند ریشه */
    public static function signChanges(array $flows): int
    {
        $signs = [];
        foreach (self::compactFlows($flows) as $flow) {
            $sign = self::sign((float) $flow);
            if ($sign !== 0) $signs[] = $sign;
        }

        $changes = 0;
        for ($index = 1, $count = count($signs); $index < $count; $index++) {
            if ($signs[$index] !== $signs[$index - 1]) $changes++;
        }

        return $changes;
    }

    /**
     * نرخ بازدهٔ خارجی (ERR): سرمایه‌گذاری مجدد جریان‌های مثبت با MARR.
     */
    public static function err(array $flows, float $reinvestmentRate): ?float
    {
        $clean = self::compactFlows($flows);
        $lastPeriod = array_key_last($clean);
        if ($lastPeriod === null || $lastPeriod < 1) return null;

        $futureInflows = 0.0;
        $outflowSchedule = [];
        foreach ($clean as $period => $flow) {
            $flow = (float) $flow;
            if ($flow > 0.0) {
                $futureInflows += $flow * self::factorFP($reinvestmentRate, $lastPeriod - (int) $period);
            } elseif ($flow < 0.0) {
                $outflowSchedule[(int) $period] = -$flow;
            }
        }

        if ($futureInflows <= 0.0 || $outflowSchedule === []) return null;

        $outflowFuture = static function (float $rate) use ($outflowSchedule, $lastPeriod): float {
            $total = 0.0;
            foreach ($outflowSchedule as $period => $amount) {
                $total += $amount * (1.0 + $rate) ** ($lastPeriod - $period);
            }

            return $total;
        };

        $difference = static fn(float $rate): float => $futureInflows - $outflowFuture($rate);

        return self::bisect($difference, -0.9999, 10.0);
    }

    /**
     * نرخ بازدهٔ داخلی تعدیل‌شده (MIRR): ارزش آتی ورودی‌ها با نرخ سرمایه‌گذاری مجدد
     * تقسیم بر ارزش فعلی خروجی‌ها با نرخ تأمین مالی.
     */
    public static function mirr(array $flows, float $reinvestmentRate, ?float $financeRate = null): ?float
    {
        $clean = self::compactFlows($flows);
        $lastPeriod = array_key_last($clean);
        if ($lastPeriod === null || $lastPeriod < 1) return null;

        $financeRate = $financeRate ?? $reinvestmentRate;

        $futureInflows = 0.0;
        $presentOutflows = 0.0;
        foreach ($clean as $period => $flow) {
            $flow = (float) $flow;
            if ($flow > 0.0) {
                $futureInflows += $flow * self::factorFP($reinvestmentRate, $lastPeriod - (int) $period);
            } elseif ($flow < 0.0) {
                $presentOutflows += (-$flow) * self::factorPF($financeRate, (int) $period);
            }
        }

        if ($futureInflows <= 0.0 || $presentOutflows <= 0.0) return null;

        return ($futureInflows / $presentOutflows) ** (1.0 / $lastPeriod) - 1.0;
    }

    /** دورهٔ بازگشت ساده (با درون‌یابی بین دو سال) */
    public static function paybackSimple(array $flows): ?float
    {
        return self::paybackFrom(self::compactFlows($flows));
    }

    /** دورهٔ بازگشت تنزیل‌شده */
    public static function paybackDiscounted(array $flows, float $rate): ?float
    {
        $discounted = [];
        foreach (self::compactFlows($flows) as $period => $flow) {
            $discounted[(int) $period] = (float) $flow * self::factorPF($rate, (int) $period);
        }

        return self::paybackFrom($discounted);
    }

    /** شاخص سودآوری: PV(ورودی‌ها) / |PV(خروجی‌ها)| */
    public static function profitabilityIndex(array $flows, float $rate): ?float
    {
        $presentInflows = 0.0;
        $presentOutflows = 0.0;
        foreach (self::compactFlows($flows) as $period => $flow) {
            $flow = (float) $flow;
            $discount = self::factorPF($rate, (int) $period);
            if ($flow >= 0.0) $presentInflows += $flow * $discount;
            else $presentOutflows += (-$flow) * $discount;
        }

        return $presentOutflows > 0.0 ? $presentInflows / $presentOutflows : null;
    }

    /**
     * نسبت فایده به هزینه.
     *
     * @param array<int,float> $benefits جریان ورودی (فایده/صرفه‌جویی)
     * @param array<int,float> $costs    جریان خروجی (هزینه)
     * @return array{ratio:float|null, net:float, presentBenefits:float, presentCosts:float}
     */
    public static function bcRatio(float $rate, array $benefits, array $costs): array
    {
        $presentBenefits = 0.0;
        foreach ($benefits as $period => $amount) {
            $presentBenefits += (float) $amount * self::factorPF($rate, (int) $period);
        }

        $presentCosts = 0.0;
        foreach ($costs as $period => $amount) {
            $presentCosts += (float) $amount * self::factorPF($rate, (int) $period);
        }

        return [
            'ratio' => $presentCosts > 0.0 ? $presentBenefits / $presentCosts : null,
            'net' => $presentBenefits - $presentCosts,
            'presentBenefits' => $presentBenefits,
            'presentCosts' => $presentCosts,
        ];
    }

    /**
     * تحلیل افزایشی بین دو گزینه: جریان تفاضل و IRR آن.
     *
     * @param array<int,float> $higher جریان گزینهٔ با سرمایهٔ بیشتر
     * @param array<int,float> $lower  جریان گزینهٔ با سرمایهٔ کمتر
     * @return array{flows:array<int,float>, incrementalIrr:float|null, npv:float}
     */
    public static function incremental(array $higher, array $lower, float $rate): array
    {
        $difference = self::differenceFlows($higher, $lower);

        return [
            'flows' => $difference,
            'incrementalIrr' => self::irr($difference),
            'npv' => self::npv($rate, $difference),
        ];
    }

    /* ==================================================================
     * استهلاک و مالیات
     * ================================================================== */

    /** @return array<int,array{year:int,depreciation:float,bookValue:float}> */
    public static function depreciationStraightLine(float $cost, float $salvage, int $life): array
    {
        if ($life < 1) return [];
        $base = max(0.0, $cost - $salvage);
        $annual = $base / $life;

        $schedule = [];
        $bookValue = $cost;
        for ($year = 1; $year <= $life; $year++) {
            $bookValue -= $annual;
            $schedule[] = ['year' => $year, 'depreciation' => $annual, 'bookValue' => max($salvage, $bookValue)];
        }

        return $schedule;
    }

    /**
     * استهلاک نزولی با امکان تغییر به خط مستقیم.
     *
     * @param float $rate نرخ نزولی (مثلاً 0.20 برای ۲۰٪)
     */
    public static function depreciationDeclining(float $cost, float $salvage, int $life, float $rate, bool $switchToStraight = true): array
    {
        if ($life < 1 || $rate <= 0.0) return [];

        $rate = min(1.0, $rate);
        $bookValue = $cost;
        $schedule = [];

        for ($year = 1; $year <= $life; $year++) {
            $declining = $bookValue * $rate;
            $straight = $year <= $life ? ($bookValue - $salvage) / ($life - $year + 1) : 0.0;
            $amount = $switchToStraight ? max($declining, $straight) : $declining;

            $amount = max(0.0, min($amount, $bookValue - $salvage));
            $bookValue -= $amount;

            $schedule[] = ['year' => $year, 'depreciation' => $amount, 'bookValue' => $bookValue];
        }

        return $schedule;
    }

    /** استهلاک مجموع سنوات */
    public static function depreciationSumOfYears(float $cost, float $salvage, int $life): array
    {
        if ($life < 1) return [];

        $base = max(0.0, $cost - $salvage);
        $sum = $life * ($life + 1) / 2.0;

        $schedule = [];
        $bookValue = $cost;
        for ($year = 1; $year <= $life; $year++) {
            $amount = $base * ($life - $year + 1) / $sum;
            $bookValue -= $amount;
            $schedule[] = ['year' => $year, 'depreciation' => $amount, 'bookValue' => max($salvage, $bookValue)];
        }

        return $schedule;
    }

    /**
     * استهلاک بر پایهٔ واحد تولید.
     *
     * @param array<int,float> $unitsByYear مقدار تولید هر سال
     */
    public static function depreciationUnitsOfProduction(float $cost, float $salvage, array $unitsByYear): array
    {
        $totalUnits = array_sum(array_map(static fn($units): float => max(0.0, (float) $units), $unitsByYear));
        if ($totalUnits <= 0.0) return [];

        $base = max(0.0, $cost - $salvage);
        $perUnit = $base / $totalUnits;

        $schedule = [];
        $bookValue = $cost;
        $year = 0;
        foreach ($unitsByYear as $units) {
            $year++;
            $amount = min($base, max(0.0, (float) $units) * $perUnit);
            $amount = max(0.0, min($amount, $bookValue - $salvage));
            $bookValue -= $amount;
            $schedule[] = ['year' => $year, 'depreciation' => $amount, 'bookValue' => $bookValue];
        }

        return $schedule;
    }

    /**
     * جریان نقدی پس از مالیات برای پروژهٔ درآمدی.
     *
     * taxable = revenue − cost − depreciation و tax = max(0, taxable) × rate
     * (انتقال زیان به سال‌های بعد در این نسخه مدل نشده است.)
     *
     * @param array<int,float> $revenues      درآمد سال‌ها (شاخص ۱ = سال اول)
     * @param array<int,float> $costs         هزینهٔ نقدی سال‌ها
     * @param array<int,float> $depreciations استهلاک سال‌ها
     * @return array<int,array{taxable:float,tax:float,netCash:float}>
     */
    public static function afterTaxFlows(array $revenues, array $costs, array $depreciations, float $taxRate): array
    {
        $rows = [];
        $years = max(count($revenues), count($costs), count($depreciations));

        for ($index = 0; $index < $years; $index++) {
            $period = $index + 1;
            $revenue = (float) ($revenues[$period] ?? $revenues[$index] ?? 0.0);
            $cost = (float) ($costs[$period] ?? $costs[$index] ?? 0.0);
            $depreciation = (float) ($depreciations[$period] ?? $depreciations[$index] ?? 0.0);

            $taxable = $revenue - $cost - $depreciation;
            $tax = max(0.0, $taxable) * max(0.0, $taxRate);

            $rows[$period] = [
                'taxable' => $taxable,
                'tax' => $tax,
                'netCash' => $revenue - $cost - $tax,
            ];
        }

        return $rows;
    }

    /**
     * جریان خالص پس از مالیات با سپر مالیاتی استهلاک.
     *
     * سال ۰ (سرمایه‌گذاری) بدون اثر مالیاتی می‌ماند. برای سال‌های بعد:
     *   taxable = net − depreciation
     *   tax     = max(0, taxable) × rate
     *   ATCF    = net − tax
     *
     * @param array<int,float> $flows              جریان خالص پیش از مالیات
     * @param array<int,float> $depreciationByYear نگاشت سال => استهلاک
     * @return array<int,float>
     */
    public static function afterTaxNetFlows(array $flows, array $depreciationByYear, float $taxRate): array
    {
        $taxRate = max(0.0, min(1.0, $taxRate));
        $result = [];

        foreach (self::compactFlows($flows) as $year => $flow) {
            $year = (int) $year;
            if ($year === 0) {
                $result[0] = $flow;
                continue;
            }

            $depreciation = (float) ($depreciationByYear[$year] ?? 0.0);
            $taxable = $flow - $depreciation;
            $result[$year] = $flow - (max(0.0, $taxable) * $taxRate);
        }

        return $result;
    }

    /**
     * تبدیل جدول استهلاک به نگاشت سال => مبلغ.
     *
     * @param array<int,array{year:int|string,depreciation:float|string}> $schedule
     * @return array<int,float>
     */
    public static function depreciationByYear(array $schedule): array
    {
        $map = [];
        foreach ($schedule as $row) {
            $map[(int) $row['year']] = (float) $row['depreciation'];
        }

        return $map;
    }

    /**
     * جدول استهلاک بر پایهٔ روش انتخاب‌شدهٔ پروژه.
     *
     * @param array<int,float> $unitsByYear فقط برای روش واحد تولید
     * @return array<int,array{year:int,depreciation:float,bookValue:float}>
     */
    public static function depreciationSchedule(
        string $method,
        float $cost,
        float $salvage,
        int $life,
        float $decliningRate = 0.20,
        array $unitsByYear = []
    ): array {
        return match ($method) {
            'straight' => self::depreciationStraightLine($cost, $salvage, $life),
            'declining' => self::depreciationDeclining($cost, $salvage, $life, $decliningRate),
            'soyd' => self::depreciationSumOfYears($cost, $salvage, $life),
            'uop' => self::depreciationUnitsOfProduction($cost, $salvage, $unitsByYear),
            default => [],
        };
    }

    /* ==================================================================
     * تورم و نرخ‌ها
     * ================================================================== */

    /** نرخ اسمی از نرخ حقیقی و تورم: (1+i) = (1+r)(1+f) */
    public static function marketRateFromReal(float $realRate, float $inflation): float
    {
        return ((1.0 + $realRate) * (1.0 + $inflation)) - 1.0;
    }

    /** نرخ حقیقی از نرخ اسمی و تورم */
    public static function realRateFromMarket(float $marketRate, float $inflation): float
    {
        return ((1.0 + $marketRate) / (1.0 + $inflation)) - 1.0;
    }

    /** نرخ سالانهٔ مؤثر از نرخ اسمی با m بار مرکب‌شدن در سال */
    public static function effectiveAnnualRate(float $nominalRate, int $compoundings): float
    {
        if ($compoundings < 1) return $nominalRate;

        return ((1.0 + ($nominalRate / $compoundings)) ** $compoundings) - 1.0;
    }

    /** نرخ پیوسته از نرخ مؤثر: r = ln(1+i) */
    public static function continuousRate(float $effectiveRate): float
    {
        return log(1.0 + $effectiveRate);
    }

    /**
     * افزایش سالانهٔ یک جریان بر پایهٔ تورم (شاخص دوره = دوره).
     *
     * @param array<int,float> $flows
     * @return array<int,float>
     */
    public static function escalate(array $flows, float $inflation): array
    {
        $escalated = [];
        foreach ($flows as $period => $flow) {
            $escalated[(int) $period] = (float) $flow * ((1.0 + $inflation) ** (int) $period);
        }

        return $escalated;
    }

    /* ==================================================================
     * جایگزینی و عمر اقتصادی
     * ================================================================== */

    /**
     * EUAC برای هر افق نگهداری k سال.
     *
     * @param array<int,float> $annualCosts هزینهٔ بهره‌برداری/نگهداری سال‌ها (شاخص ۱)
     * @param array<int,float> $salvageByYear ارزش اسقاط پایان هر سال (شاخص ۱)
     * @return array<int,array{year:int,euac:float|null,presentCost:float}>
     */
    public static function euacByYear(float $firstCost, array $annualCosts, array $salvageByYear, float $rate): array
    {
        $rows = [];
        $presentCosts = $firstCost;
        $lastYear = max(count($annualCosts), count($salvageByYear));

        for ($year = 1; $year <= $lastYear; $year++) {
            $presentCosts += (float) ($annualCosts[$year] ?? 0.0) * self::factorPF($rate, $year);
            $salvage = (float) ($salvageByYear[$year] ?? 0.0);
            $netPresent = $presentCosts - ($salvage * self::factorPF($rate, $year));

            $rows[] = [
                'year' => $year,
                'euac' => $netPresent * self::factorAP($rate, $year),
                'presentCost' => $netPresent,
            ];
        }

        return $rows;
    }

    /** سالی که کمترین EUAC دارد (عمر اقتصادی) */
    public static function economicLife(array $euacRows): ?int
    {
        $bestYear = null;
        $bestValue = null;
        foreach ($euacRows as $row) {
            if ($row['euac'] === null) continue;
            if ($bestValue === null || $row['euac'] < $bestValue) {
                $bestValue = $row['euac'];
                $bestYear = (int) $row['year'];
            }
        }

        return $bestYear;
    }

    /* ==================================================================
     * حساسیت و برآورد
     * ================================================================== */

    /**
     * NPV پس از تغییر درصدی یک دورهٔ خاص.
     */
    public static function npvWithShock(array $flows, float $rate, int $period, float $changePct): float
    {
        $shocked = self::compactFlows($flows);
        if (!array_key_exists($period, $shocked)) return self::npv($rate, $shocked);
        $shocked[$period] = $shocked[$period] * (1.0 + $changePct);

        return self::npv($rate, $shocked);
    }

    /**
     * «ارزش تغییر» یک دوره: درصدی که NPV را صفر می‌کند.
     *
     * چون NPV نسبت به یک دوره خطی است، مقدار دقیق از رابطهٔ بسته به‌دست می‌آید.
     * اگر NPV اولیه صفر باشد یا جریان آن دوره صفر باشد، null برمی‌گردد.
     */
    public static function switchingValue(array $flows, float $rate, int $period): ?float
    {
        $clean = self::compactFlows($flows);
        $baseNpv = self::npv($rate, $clean);
        if (!array_key_exists($period, $clean)) return null;

        $presentValue = (float) $clean[$period] * self::factorPF($rate, $period);
        if (abs($presentValue) < self::EPSILON) return null;

        return -$baseNpv / $presentValue;
    }

    /**
     * حساسیت NPV به نرخ: مقدار NPV در نرخ‌های ±گام.
     *
     * @return array<int,array{rate:float,npv:float}>
     */
    public static function npvRateSensitivity(array $flows, float $rate, float $step, int $steps = 3): array
    {
        $rows = [];
        for ($index = -$steps; $index <= $steps; $index++) {
            $candidate = $rate + ($index * $step);
            if ($candidate <= -0.9999) continue;
            $rows[] = ['rate' => $candidate, 'npv' => self::npv($candidate, $flows)];
        }

        return $rows;
    }

    /** برآورد هزینه با قاعدهٔ شش‌دهم: C2 = C1 × (Q2/Q1)^x */
    public static function capacityCost(float $cost1, float $capacity1, float $capacity2, float $exponent = 0.6): ?float
    {
        if ($capacity1 <= 0.0 || $cost1 <= 0.0) return null;

        return $cost1 * (($capacity2 / $capacity1) ** $exponent);
    }

    /* ==================================================================
     * ورودی/خروجی و بررسی
     * ================================================================== */

    /**
     * ساخت جریان خالص از ردیف‌های دیتابیس.
     *
     * @param array<int,array{year_no:int|string,inflow?:float|string,outflow?:float|string}> $rows
     * @return array<int,float>
     */
    public static function netFlows(array $rows): array
    {
        $flows = [];
        foreach ($rows as $row) {
            $year = (int) ($row['year_no'] ?? 0);
            $inflow = (float) ($row['inflow'] ?? 0.0);
            $outflow = (float) ($row['outflow'] ?? 0.0);
            $flows[$year] = ($flows[$year] ?? 0.0) + ($inflow - $outflow);
        }

        ksort($flows);

        return $flows;
    }

    /**
     * بررسی اعتبار جریان نقدی برای تحلیل.
     *
     * @return array<int,string> فهرست خطاها (خالی = معتبر)
     */
    public static function validate(array $flows): array
    {
        $clean = self::compactFlows($flows);
        $issues = [];

        if ($clean === []) return ['جریان نقدی خالی است.'];
        if (count($clean) < 2) $issues[] = 'برای تحلیل، دست‌کم دو دورهٔ مالی لازم است.';
        if (array_key_last($clean) > self::MAX_PERIODS) $issues[] = 'دورهٔ مطالعه از سقف ' . self::MAX_PERIODS . ' سال بیشتر است.';

        $hasPositive = false;
        $hasNegative = false;
        foreach ($clean as $flow) {
            if ($flow > 0.0) $hasPositive = true;
            if ($flow < 0.0) $hasNegative = true;
        }

        if (!$hasNegative) $issues[] = 'جریان نقدی هیچ خروجی (سرمایه‌گذاری) ندارد.';
        if (!$hasPositive) $issues[] = 'جریان نقدی هیچ ورودی (درآمد/صرفه‌جویی) ندارد.';
        if (self::signChanges($clean) > 1) {
            $issues[] = 'جریان نقدی بیش از یک تغییر علامت دارد؛ IRR می‌تواند چند مقدار داشته باشد و باید با احتیاط تفسیر شود.';
        }

        return $issues;
    }

    /** جریان‌های صفر انتهایی حذف می‌شوند تا دورهٔ تحلیل درست تعیین شود. */
    public static function compactFlows(array $flows): array
    {
        $clean = [];
        foreach ($flows as $period => $flow) {
            $period = (int) $period;
            if ($period < 0 || $period > self::MAX_PERIODS) continue;
            $clean[$period] = (float) $flow;
        }
        ksort($clean);

        return $clean;
    }

    /** آیا جریان نقدی «متعارف» است (یک تغییر علامت)؟ */
    public static function isConventional(array $flows): bool
    {
        return self::signChanges($flows) <= 1;
    }

    /* ==================================================================
     * توابع کمکی داخلی
     * ================================================================== */

    private static function sign(float $value): int
    {
        if ($value > self::EPSILON) return 1;
        if ($value < -self::EPSILON) return -1;

        return 0;
    }

    private static function bisect(callable $function, float $lower, float $upper, int $iterations = 200): ?float
    {
        $lowerValue = (float) $function($lower);
        $upperValue = (float) $function($upper);

        if ($lowerValue === 0.0) return $lower;
        if ($upperValue === 0.0) return $upper;
        if (($lowerValue > 0.0) === ($upperValue > 0.0)) return null;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $middle = ($lower + $upper) / 2.0;
            $middleValue = (float) $function($middle);

            if (abs($middleValue) < 1.0e-8 || ($upper - $lower) < 1.0e-12) return $middle;

            if (($middleValue > 0.0) === ($lowerValue > 0.0)) {
                $lower = $middle;
                $lowerValue = $middleValue;
            } else {
                $upper = $middle;
            }
        }

        return ($lower + $upper) / 2.0;
    }

    private static function paybackFrom(array $flows): ?float
    {
        $cumulative = 0.0;
        $previous = 0.0;
        $periods = array_keys($flows);

        foreach ($periods as $period) {
            $previous = $cumulative;
            $cumulative += (float) $flows[$period];

            if ($cumulative >= 0.0 && $previous < 0.0) {
                $flow = (float) $flows[$period];
                if (abs($flow) < self::EPSILON) return (float) $period;

                return ($period - 1) + (-$previous / $flow);
            }
        }

        return null;
    }

    /** @return array<int,float> */
    private static function differenceFlows(array $higher, array $lower): array
    {
        $difference = [];
        $keys = array_unique(array_merge(array_keys($higher), array_keys($lower)));
        sort($keys);

        foreach ($keys as $key) {
            $difference[(int) $key] = (float) ($higher[$key] ?? 0.0) - (float) ($lower[$key] ?? 0.0);
        }

        return $difference;
    }

    private static function uniqueSorted(array $values, float $tolerance = 1.0e-6): array
    {
        sort($values);
        $unique = [];
        foreach ($values as $value) {
            if ($unique === [] || abs($value - end($unique)) > $tolerance) $unique[] = $value;
        }

        return $unique;
    }
}
