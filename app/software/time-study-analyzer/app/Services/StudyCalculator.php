<?php
namespace App\Software\TimeStudyAnalyzer\Services;

/** Pure calculations for stopwatch time studies and work sampling. */
final class StudyCalculator
{
    private const Z = ['90' => 1.644853626951, '95' => 1.959963984540, '99' => 2.575829303549];

    /**
     * مقدار بحرانی دوطرفهٔ توزیع t برای درجات آزادی ۱ تا ۳۰.
     * برای نمونه‌های کوچک (n<31) دقیق‌تر از z است؛ برای n بزرگ‌تر از مقدار z استفاده می‌شود.
     */
    private const T = [
        '90' => [1 => 6.314, 2 => 2.920, 3 => 2.353, 4 => 2.132, 5 => 2.015, 6 => 1.943, 7 => 1.895, 8 => 1.860, 9 => 1.833, 10 => 1.812,
                 11 => 1.796, 12 => 1.782, 13 => 1.771, 14 => 1.761, 15 => 1.753, 16 => 1.746, 17 => 1.740, 18 => 1.734, 19 => 1.729, 20 => 1.725,
                 21 => 1.721, 22 => 1.717, 23 => 1.714, 24 => 1.711, 25 => 1.708, 26 => 1.706, 27 => 1.703, 28 => 1.701, 29 => 1.699, 30 => 1.697],
        '95' => [1 => 12.706, 2 => 4.303, 3 => 3.182, 4 => 2.776, 5 => 2.571, 6 => 2.447, 7 => 2.365, 8 => 2.306, 9 => 2.262, 10 => 2.228,
                 11 => 2.201, 12 => 2.179, 13 => 2.160, 14 => 2.145, 15 => 2.131, 16 => 2.120, 17 => 2.110, 18 => 2.101, 19 => 2.093, 20 => 2.086,
                 21 => 2.080, 22 => 2.074, 23 => 2.069, 24 => 2.064, 25 => 2.060, 26 => 2.056, 27 => 2.052, 28 => 2.048, 29 => 2.045, 30 => 2.042],
        '99' => [1 => 63.657, 2 => 9.925, 3 => 5.841, 4 => 4.604, 5 => 4.032, 6 => 3.707, 7 => 3.499, 8 => 3.355, 9 => 3.250, 10 => 3.169,
                 11 => 3.106, 12 => 3.055, 13 => 3.012, 14 => 2.977, 15 => 2.947, 16 => 2.921, 17 => 2.898, 18 => 2.878, 19 => 2.861, 20 => 2.845,
                 21 => 2.831, 22 => 2.819, 23 => 2.807, 24 => 2.797, 25 => 2.787, 26 => 2.779, 27 => 2.771, 28 => 2.763, 29 => 2.756, 30 => 2.750],
    ];

    public static function stopwatch(array $study, array $elements, array $cycles, array $observations): array
    {
        $byCycle = [];
        $elementStats = [];
        foreach ($elements as $element) {
            $elementStats[(int) $element['id']] = [
                'element' => $element, 'observed' => [], 'normal' => [], 'ratings' => [], 'valid_count' => 0,
                'excluded_count' => 0, 'average_observed' => null, 'average_rating' => null,
                'normal_seconds' => null, 'std_dev' => null, 'cv_pct' => null,
            ];
        }
        foreach ($observations as $observation) {
            $elementId = (int) $observation['element_id'];
            $cycleId = (int) $observation['cycle_id'];
            if (!isset($elementStats[$elementId])) continue;
            $valid = (int) $observation['is_valid'] === 1 && ($observation['cycle_status'] ?? 'valid') === 'valid';
            if (!$valid) {
                $elementStats[$elementId]['excluded_count']++;
                continue;
            }
            $seconds = (float) $observation['observed_seconds'];
            $rating = $elementStats[$elementId]['element']['element_type'] === 'machine'
                ? 100.0 : (float) $observation['performance_rating_pct'];
            $normalSeconds = $seconds * $rating / 100.0;
            $elementStats[$elementId]['observed'][] = $seconds;
            $elementStats[$elementId]['normal'][] = $normalSeconds;
            $elementStats[$elementId]['ratings'][] = $rating;
            $elementStats[$elementId]['valid_count']++;
            $byCycle[$cycleId][$elementId] = $normalSeconds * (float) $elementStats[$elementId]['element']['frequency_per_cycle'];
        }

        $laborNormal = 0.0;
        $machineTime = 0.0;
        $completeCycleTotals = [];
        foreach ($elementStats as &$stats) {
            $normal = $stats['normal'];
            $observed = $stats['observed'];
            if ($normal) {
                $meanNormal = array_sum($normal) / count($normal);
                $meanObserved = array_sum($observed) / count($observed);
                $std = self::sampleStdDev($normal);
                $stats['average_observed'] = $meanObserved;
                $stats['average_rating'] = $stats['element']['element_type'] === 'machine'
                    ? 100.0 : array_sum($stats['ratings']) / count($stats['ratings']);
                $stats['normal_seconds'] = $meanNormal;
                $stats['std_dev'] = $std;
                $stats['cv_pct'] = $meanNormal > 0 && $std !== null ? 100.0 * $std / $meanNormal : null;
                $cycleContribution = $meanNormal * (float) $stats['element']['frequency_per_cycle'];
                if ($stats['element']['element_type'] === 'machine') $machineTime += $cycleContribution;
                else $laborNormal += $cycleContribution;
            }
        }
        unset($stats);

        $elementIds = array_keys($elementStats);
        foreach ($cycles as $cycle) {
            if (($cycle['cycle_status'] ?? '') !== 'valid') continue;
            $cycleId = (int) $cycle['id'];
            $total = 0.0;
            $complete = count($elementIds) > 0;
            foreach ($elementIds as $elementId) {
                if (!isset($byCycle[$cycleId][$elementId])) { $complete = false; break; }
                $total += $byCycle[$cycleId][$elementId];
            }
            if ($complete) $completeCycleTotals[] = $total;
        }

        $allowance = (
            (float) $study['personal_allowance_pct'] + (float) $study['fatigue_allowance_pct'] +
            (float) $study['delay_allowance_pct'] + (float) $study['other_allowance_pct']
        ) / 100.0;
        $manualStandard = $study['allowance_basis'] === 'standard'
            ? ($allowance < 1.0 ? $laborNormal / (1.0 - $allowance) : null)
            : $laborNormal * (1.0 + $allowance);
        $standardCycle = $manualStandard === null ? null : $manualStandard + $machineTime;
        $meanCycle = $completeCycleTotals ? array_sum($completeCycleTotals) / count($completeCycleTotals) : null;
        $cycleStd = count($completeCycleTotals) > 1 ? self::sampleStdDev($completeCycleTotals) : null;
        $confidence = (string) ($study['confidence_level'] ?? '95');
        $z = self::Z[$confidence] ?? self::Z['95'];
        $precision = (float) $study['precision_pct'] / 100.0;
        $n = count($completeCycleTotals);

        // برای نمونهٔ کوچک (کمتر از ۳۱ چرخه) مقدار بحرانی t و برای نمونهٔ بزرگ z استفاده می‌شود.
        $critical = self::criticalValue($confidence, $n > 1 ? $n - 1 : null);
        $requiredN = ($meanCycle !== null && $cycleStd !== null)
            ? self::requiredSampleSize($confidence, $cycleStd, $meanCycle, $precision) : null;
        $margin = ($n > 1 && $cycleStd !== null) ? $critical * $cycleStd / sqrt($n) : null;

        // ---- کفایت نمونهٔ هر عنصر (روش استاندارد: هر عنصر جداگانه باید نمونهٔ کافی داشته باشد) ----
        foreach ($elementStats as &$stats) {
            $stats['required_count'] = null;
            $stats['missing_count'] = null;
            $stats['sample_is_sufficient'] = null;
            $stats['cv_flag'] = null;

            $meanNormal = $stats['normal_seconds'];
            $std = $stats['std_dev'];
            $count = (int) $stats['valid_count'];

            if (!$stats['normal'] || $meanNormal === null || $meanNormal <= 0 || $std === null || $count < 2) continue;

            $required = self::requiredSampleSize($confidence, $std, $meanNormal, $precision);
            $stats['required_count'] = $required;
            $stats['missing_count'] = max(0, $required - $count);
            $stats['sample_is_sufficient'] = $stats['missing_count'] === 0;

            $cv = $stats['cv_pct'];
            $stats['cv_flag'] = $cv === null ? null : ($cv >= 25.0 ? 'high' : ($cv >= 15.0 ? 'watch' : 'ok'));
        }
        unset($stats);

        // ---- روند زمانی چرخه‌ها: یادگیری، خستگی یا تغییر روش در میانهٔ مطالعه ----
        // چرخه‌ها نزولی برمی‌گردند؛ برای تحلیل روند، ترتیب زمانی صعودی لازم است.
        $chronological = array_reverse($completeCycleTotals);
        $trendPct = null;
        $hasTrend = false;
        $chronologicalCount = count($chronological);
        if ($chronologicalCount >= 4) {
            $half = (int) floor($chronologicalCount / 2);
            $firstHalf = array_slice($chronological, 0, $half);
            $secondHalf = array_slice($chronological, -$half);
            $firstMean = array_sum($firstHalf) / max(1, count($firstHalf));
            $secondMean = array_sum($secondHalf) / max(1, count($secondHalf));
            if ($firstMean > 0) {
                $trendPct = 100.0 * ($secondMean - $firstMean) / $firstMean;
                $hasTrend = abs($trendPct) >= 5.0;
            }
        }

        // ---- هشدارهای کیفیت داده (قابل نمایش در گزارش) ----
        $warnings = [];
        if ($n === 0) {
            $warnings[] = 'هنوز چرخهٔ کامل و معتبری ثبت نشده است؛ تا آن زمان محاسبات فقط برآورد موقت است.';
        } elseif ($n < 5) {
            $warnings[] = 'تعداد چرخه‌های کامل معتبر (' . $n . ') کمتر از حداقل توصیه‌شده (۵) است؛ برآورد پراکندگی ناپایدار می‌ماند.';
        }
        if ($requiredN !== null && $n < $requiredN) {
            $warnings[] = 'برای دقت هدف، دست‌کم ' . $requiredN . ' چرخهٔ کامل معتبر لازم است.';
        }
        foreach ($elementStats as $stats) {
            if (!empty($stats['missing_count'])) {
                $warnings[] = 'عنصر «' . $stats['element']['name'] . '» به ' . (int) $stats['missing_count'] . ' مشاهدهٔ معتبر دیگر نیاز دارد.';
            }
            if (($stats['cv_flag'] ?? null) === 'high') {
                $warnings[] = 'پراکندگی عنصر «' . $stats['element']['name'] . '» بالاست (CV ' . number_format((float) $stats['cv_pct'], 1, '.', '') . '٪)؛ مرزهای عنصر یا روش کار را بازبینی کنید.';
            }
        }
        if ($hasTrend) {
            $warnings[] = 'زمان چرخه در نیمهٔ دوم نسبت به نیمهٔ اول ' . ($trendPct > 0 ? 'افزایش' : 'کاهش') . ' '
                . number_format(abs((float) $trendPct), 1, '.', '') . '٪ داشته است؛ احتمال یادگیری، خستگی یا تغییر روش را بررسی کنید.';
        }

        return [
            'elements' => array_values($elementStats),
            'cycles_recorded' => count($cycles),
            'cycles_excluded' => count(array_filter($cycles, static fn($cycle) => $cycle['cycle_status'] === 'excluded')),
            'complete_valid_cycles' => $n,
            'incomplete_valid_cycles' => max(0, count($cycles) - count(array_filter($cycles, static fn($cycle) => $cycle['cycle_status'] === 'excluded')) - $n),
            'labor_normal_seconds' => $laborNormal,
            'machine_seconds' => $machineTime,
            'normal_cycle_seconds' => ($meanCycle !== null) ? $meanCycle : $laborNormal + $machineTime,
            'standard_cycle_seconds' => $standardCycle,
            'allowance_total_pct' => $allowance * 100.0,
            'confidence_level' => $confidence,
            'cycle_mean' => $meanCycle,
            'cycle_std_dev' => $cycleStd,
            'confidence_margin' => $margin,
            'confidence_lower' => $margin !== null ? max(0.0, $meanCycle - $margin) : null,
            'confidence_upper' => $margin !== null ? $meanCycle + $margin : null,
            'required_cycles' => $requiredN,
            'sample_is_sufficient' => $requiredN !== null && $n >= $requiredN,
            'capacity_per_hour' => ($standardCycle !== null && $standardCycle > 0) ? 3600.0 / $standardCycle : null,
            'z_value' => $z,
            'critical_value' => $critical,
            't_distribution_used' => ($n > 1 && $n - 1 <= 30),
            'degrees_of_freedom' => $n > 1 ? $n - 1 : null,
            'has_trend' => $hasTrend,
            'trend_change_pct' => $trendPct,
            'warnings' => $warnings,
        ];
    }

    public static function workSampling(array $study, array $categories, array $slots): array
    {
        $counts = [];
        foreach ($categories as $category) $counts[(int) $category['id']] = 0;
        $observed = 0;
        $missed = 0;
        $planned = 0;
        foreach ($slots as $slot) {
            if ($slot['slot_status'] === 'void') continue;
            $planned++;
            if ($slot['slot_status'] === 'missed') { $missed++; continue; }
            if ($slot['slot_status'] !== 'observed' || $slot['category_id'] === null) continue;
            $categoryId = (int) $slot['category_id'];
            if (!array_key_exists($categoryId, $counts)) continue;
            $counts[$categoryId]++;
            $observed++;
        }
        $confidence = (string) ($study['confidence_level'] ?? '95');
        $z = self::Z[$confidence] ?? self::Z['95'];
        $precision = (float) $study['precision_pct'] / 100.0;
        $required = (int) ceil(($z * $z * 0.25) / ($precision * $precision));
        $rows = [];
        foreach ($categories as $category) {
            $id = (int) $category['id'];
            $count = $counts[$id] ?? 0;
            $p = $observed > 0 ? $count / $observed : null;
            [$lower, $upper] = $p === null ? [null, null] : self::wilson($count, $observed, $z);
            $requiredCount = null;
            $achievedPrecision = null;
            $categorySufficient = null;

            if ($p !== null) {
                $variance = $p * (1.0 - $p);
                $requiredCount = (int) ceil(($z * $z * $variance) / ($precision * $precision));
                $achievedPrecision = $observed > 0 ? 100.0 * $z * sqrt($variance / $observed) : null;
                $categorySufficient = $count >= $requiredCount;
            }

            $rows[] = [
                'category' => $category, 'count' => $count, 'share' => $p,
                'lower' => $lower, 'upper' => $upper,
                'required_count' => $requiredCount,
                'achieved_precision_pct' => $achievedPrecision,
                'is_sufficient' => $categorySufficient,
            ];
        }
        $productive = 0;
        foreach ($rows as $row) if ($row['category']['category_type'] === 'productive') $productive += $row['count'];
        $void = count(array_filter($slots, static fn($slot) => $slot['slot_status'] === 'void'));
        $resolved = $observed + $missed + $void;

        // دقت واقعی بهدست‌آمده برای سهم فعالیت مولد (بر پایهٔ دادهٔ فعلی، نه بدترین حالت p=0.5)
        $productiveShareValue = $observed > 0 ? $productive / $observed : null;
        $achievedPrecision = ($productiveShareValue !== null && $observed > 0)
            ? 100.0 * $z * sqrt($productiveShareValue * (1.0 - $productiveShareValue) / $observed) : null;

        $warnings = [];
        if ($observed < $required) {
            $warnings[] = 'تعداد مشاهده‌های معتبر (' . $observed . ') کمتر از حجم نمونهٔ هدف (' . $required . ') است.';
        }
        if ($achievedPrecision !== null && $achievedPrecision > (float) $study['precision_pct']) {
            $warnings[] = 'دقت واقعی سهم مولد (' . number_format($achievedPrecision, 1, '.', '') . ' واحد درصد) از دقت هدف بدتر است؛ مشاهده‌های بیشتری ثبت کنید.';
        }
        if ($missed > 0) {
            $warnings[] = $missed . ' زمان برنامه‌ریزی‌شده ازدست رفته است؛ نرخ ازدست‌رفته بر اعتبار برآورد اثر می‌گذارد.';
        }
        return [
            'categories' => $rows, 'observed_count' => $observed, 'planned_count' => $planned,
            'missed_count' => $missed, 'void_count' => $void, 'resolved_count' => $resolved,
            'productive_share' => $observed > 0 ? $productive / $observed : null,
            'required_sample_size' => $required, 'confidence_level' => $confidence,
            'precision_pct' => (float) $study['precision_pct'], 'sample_is_sufficient' => $observed >= $required,
            'completion_pct' => $planned > 0 ? 100.0 * ($observed + $missed) / $planned : null,
            'achieved_precision_pct' => $achievedPrecision,
            'warnings' => $warnings,
        ];
    }

    private static function criticalValue(string $confidence, ?int $df): float
    {
        if ($df !== null && $df >= 1 && $df <= 30) {
            return self::T[$confidence][$df] ?? (self::Z[$confidence] ?? self::Z['95']);
        }

        return self::Z[$confidence] ?? self::Z['95'];
    }

    /**
     * حجم نمونهٔ لازم برای دقت نسبی هدف.
     *
     * از z شروع می‌کند و با مقدار t متناظر همان حجم نمونه چند بار تکرار می‌شود تا
     * به مقدار پایدار برسد؛ این روش از تخمین تک‌مرحله‌ای با t (که برای نمونهٔ کوچک
     * عدد هدف را به‌شدت متورم می‌کند) دقیق‌تر و واقع‌بینانه‌تر است.
     */
    private static function requiredSampleSize(string $confidence, float $std, float $mean, float $precision): int
    {
        $z = self::Z[$confidence] ?? self::Z['95'];
        if ($mean <= 0 || $std <= 0 || $precision <= 0) return 1;

        $estimate = max(1, (int) ceil(($z * $std / ($precision * $mean)) ** 2));

        // کوچک‌ترین n که با مقدار t خودش (df=n−1) شرط دقت را برآورده می‌کند.
        for ($candidate = $estimate; $candidate <= 10000; $candidate++) {
            $critical = self::criticalValue($confidence, max(1, $candidate - 1));
            $needed = (int) ceil(($critical * $std / ($precision * $mean)) ** 2);
            if ($needed <= $candidate) return $candidate;
        }

        return 10000;
    }

    private static function sampleStdDev(array $values): ?float
    {
        $n = count($values);
        if ($n < 2) return null;
        $mean = array_sum($values) / $n;
        $sumSquares = 0.0;
        foreach ($values as $value) $sumSquares += ((float) $value - $mean) ** 2;
        return sqrt(max(0.0, $sumSquares / ($n - 1)));
    }

    private static function wilson(int $successes, int $n, float $z): array
    {
        if ($n <= 0) return [null, null];
        $p = $successes / $n;
        $z2 = $z * $z;
        $denominator = 1.0 + $z2 / $n;
        $center = ($p + $z2 / (2.0 * $n)) / $denominator;
        $radius = ($z / $denominator) * sqrt(($p * (1.0 - $p) / $n) + ($z2 / (4.0 * $n * $n)));
        return [max(0.0, $center - $radius), min(1.0, $center + $radius)];
    }
}
