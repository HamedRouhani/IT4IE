<?php
namespace App\Software\TimeStudyAnalyzer\Services;

/** Pure calculations for stopwatch time studies and work sampling. */
final class StudyCalculator
{
    private const Z = ['90' => 1.644853626951, '95' => 1.959963984540, '99' => 2.575829303549];

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
        $requiredN = ($meanCycle !== null && $meanCycle > 0 && $cycleStd !== null)
            ? max(1, (int) ceil(($z * $cycleStd / ($precision * $meanCycle)) ** 2)) : null;
        $n = count($completeCycleTotals);
        $margin = ($n > 1 && $cycleStd !== null) ? $z * $cycleStd / sqrt($n) : null;

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
            $rows[] = [
                'category' => $category, 'count' => $count, 'share' => $p,
                'lower' => $lower, 'upper' => $upper,
            ];
        }
        $productive = 0;
        foreach ($rows as $row) if ($row['category']['category_type'] === 'productive') $productive += $row['count'];
        return [
            'categories' => $rows, 'observed_count' => $observed, 'planned_count' => $planned,
            'missed_count' => $missed, 'productive_share' => $observed > 0 ? $productive / $observed : null,
            'required_sample_size' => $required, 'confidence_level' => $confidence,
            'precision_pct' => (float) $study['precision_pct'], 'sample_is_sufficient' => $observed >= $required,
            'completion_pct' => $planned > 0 ? 100.0 * ($observed + $missed) / $planned : null,
        ];
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
