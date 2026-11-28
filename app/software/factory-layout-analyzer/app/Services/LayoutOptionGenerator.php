<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Services;

/** Deterministic constructive block-layout alternatives with Pareto-front ranking. */
final class LayoutOptionGenerator
{
    private const MAX_AREAS = 12;
    private const EPSILON = 0.000001;

    /**
     * @param list<array{name:string,area_m2:mixed,basis?:mixed}> $areas
     * @param list<array<string,mixed>> $flows
     * @param list<array<string,mixed>> $relationships
     * @return array<string,mixed>
     */
    public function generate(array $areas, array $flows, array $relationships, int $workingDaysPerWeek): array
    {
        $issues = [];
        $warnings = [];
        $byName = [];
        foreach ($areas as $area) {
            $name = trim((string) ($area['name'] ?? ''));
            $size = $area['area_m2'] ?? null;
            if ($name === '') {
                $issues[] = 'یک نام فعالیت خالی است و در چیدمان قابل استفاده نیست.';
                continue;
            }
            if (isset($byName[$name])) {
                $issues[] = 'نام «' . $name . '» هم‌زمان در چند منبع فضا ثبت شده است؛ پیش از چیدمان، این فضاها را یکپارچه یا نام‌گذاری متمایز کنید.';
                continue;
            }
            if (!is_numeric($size) || !is_finite((float) $size) || (float) $size <= 0) {
                $issues[] = 'مساحت معتبر برای «' . $name . '» ثبت نشده است.';
                continue;
            }
            if (!is_string($area['basis'] ?? null) || trim($area['basis']) === '') {
                $issues[] = 'مبنای قابل‌ردیابی برآورد مساحت «' . $name . '» ثبت نشده است.';
                continue;
            }
            $size = (float) $size;
            $byName[$name] = [
                'name' => $name,
                'area_m2' => $size,
                'basis' => trim($area['basis']),
                'width' => sqrt($size),
                'height' => sqrt($size),
            ];
        }

        if (count($byName) < 2) {
            $issues[] = 'برای ساخت گزینهٔ چیدمان دست‌کم دو فعالیت با مساحت معتبر لازم است.';
        }
        if (count($byName) > self::MAX_AREAS) {
            $issues[] = 'نسخهٔ مقدماتی حداکثر برای ۱۲ فعالیت گزینه تولید می‌کند؛ فعالیت‌های هم‌کارکرد را با مبنای مستند گروه‌بندی کنید.';
        }
        if ($issues) {
            return ['ready' => false, 'issues' => array_values(array_unique($issues)), 'warnings' => [], 'options' => []];
        }

        ksort($byName, SORT_STRING);
        $names = array_map('strval', array_keys($byName));
        $known = array_fill_keys($names, true);
        $flowByPair = [];
        $flowDegree = array_fill_keys($names, 0.0);
        $hasFlowData = false;
        foreach ($flows as $flow) {
            $from = (string) ($flow['from_area'] ?? '');
            $to = (string) ($flow['to_area'] ?? '');
            $quantity = $flow['load_moves'] ?? null;
            $period = (string) ($flow['flow_period'] ?? '');
            if (!isset($known[$from], $known[$to]) || $from === $to || !is_numeric($quantity) || (float) $quantity < 0) {
                $warnings[] = 'یک جریان ناقص یا ناسازگار از محاسبهٔ فاصلهٔ بار کنار گذاشته شد.';
                continue;
            }
            $weekly = $this->weeklyFlow((float) $quantity, $period, $workingDaysPerWeek);
            if ($weekly === null) {
                $warnings[] = 'جریان «' . $from . ' → ' . $to . '» دورهٔ قابل‌تبدیل ندارد و در رتبه‌بندی لحاظ نشد.';
                continue;
            }
            $key = $this->pairKey($from, $to);
            $flowByPair[$key] = ($flowByPair[$key] ?? 0.0) + $weekly;
            $flowDegree[$from] += $weekly;
            $flowDegree[$to] += $weekly;
            $hasFlowData = true;
        }

        $relationByPair = [];
        $relationDegree = array_fill_keys($names, ['A' => 0, 'E' => 0, 'I' => 0]);
        $relationshipCounts = ['A' => 0, 'E' => 0, 'I' => 0, 'O' => 0, 'U' => 0, 'X' => 0];
        foreach ($relationships as $relationship) {
            $a = (string) ($relationship['area_a'] ?? '');
            $b = (string) ($relationship['area_b'] ?? '');
            $code = strtoupper((string) ($relationship['closeness_code'] ?? ''));
            if (!isset($known[$a], $known[$b]) || $a === $b || !isset($relationshipCounts[$code])) {
                $warnings[] = 'یک رابطهٔ قدیمی یا ناسازگار از گزینه‌سازی کنار گذاشته شد.';
                continue;
            }
            $relationByPair[$this->pairKey($a, $b)] = $code;
            $relationshipCounts[$code]++;
            if (isset($relationDegree[$a][$code]) && in_array($code, ['A', 'E', 'I'], true)) {
                $relationDegree[$a][$code]++;
                $relationDegree[$b][$code]++;
            }
        }

        $orders = [
            'flow' => $names,
            'relationship' => $names,
            'compact' => $names,
        ];
        usort($orders['flow'], static fn(string $a, string $b): int =>
            ($flowDegree[$b] <=> $flowDegree[$a]) ?: ($byName[$b]['area_m2'] <=> $byName[$a]['area_m2']) ?: strcmp($a, $b)
        );
        usort($orders['relationship'], static function (string $a, string $b) use ($relationDegree, $flowDegree, $byName): int {
            foreach (['A', 'E', 'I'] as $code) {
                $difference = $relationDegree[$b][$code] <=> $relationDegree[$a][$code];
                if ($difference !== 0) return $difference;
            }
            return ($flowDegree[$b] <=> $flowDegree[$a])
                ?: ($byName[$b]['area_m2'] <=> $byName[$a]['area_m2'])
                ?: strcmp($a, $b);
        });
        usort($orders['compact'], static fn(string $a, string $b): int =>
            ($byName[$b]['area_m2'] <=> $byName[$a]['area_m2']) ?: ($flowDegree[$b] <=> $flowDegree[$a]) ?: strcmp($a, $b)
        );

        $specs = [
            ['key' => 'flow', 'label' => 'جریان‌محور', 'priority' => 'flow'],
            ['key' => 'relationship', 'label' => 'رابطه‌محور', 'priority' => 'relationship'],
            ['key' => 'compact', 'label' => 'فشرده‌سازی هندسی', 'priority' => 'compact'],
        ];
        $options = [];
        $signatures = [];
        foreach ($specs as $spec) {
            $layout = $this->construct($orders[$spec['key']], $byName, $flowByPair, $relationByPair, $spec['priority']);
            if ($layout === null) continue;
            $signatureParts = [];
            foreach ($layout as $name => $rect) {
                $signatureParts[] = $name . ':' . round($rect['x'], 4) . ':' . round($rect['y'], 4);
            }
            sort($signatureParts, SORT_STRING);
            $signature = implode('|', $signatureParts);
            if (isset($signatures[$signature])) continue;
            $signatures[$signature] = true;

            $metrics = $this->measure($layout, $flowByPair, $relationByPair);
            $options[] = [
                'label' => $spec['label'],
                'rectangles' => $layout,
                'width' => $metrics['width'],
                'height' => $metrics['height'],
                'flow_distance' => $metrics['flow_distance'],
                'envelope_area' => $metrics['envelope_area'],
                'adjacent' => $metrics['adjacent'],
                'x_violations' => $metrics['x_violations'],
                'feasible' => $metrics['x_violations'] === 0,
                'pareto_rank' => null,
            ];
        }

        $this->assignParetoRanks($options);
        usort($options, static function (array $a, array $b): int {
            if ($a['feasible'] !== $b['feasible']) return $a['feasible'] ? -1 : 1;
            if ($a['pareto_rank'] !== $b['pareto_rank']) {
                if ($a['pareto_rank'] === null) return 1;
                if ($b['pareto_rank'] === null) return -1;
                return $a['pareto_rank'] <=> $b['pareto_rank'];
            }
            return ($a['flow_distance'] <=> $b['flow_distance']) ?: strcmp($a['label'], $b['label']);
        });

        $ratedPairCount = count($relationByPair);
        $possiblePairCount = intdiv(count($names) * (count($names) - 1), 2);
        if ($ratedPairCount < $possiblePairCount) {
            $warnings[] = 'رابطهٔ ثبت‌نشده به‌معنای U نیست؛ پوشش فعلی ' . $ratedPairCount . ' از ' . $possiblePairCount . ' زوج است.';
        }
        if (!$hasFlowData || array_sum($flowByPair) <= 0) {
            $warnings[] = 'جریان مثبت قابل‌استفاده ثبت نشده؛ گزینه‌ها با روابط و هندسه ساخته می‌شوند و معیار فاصلهٔ بار قدرت تفکیک ندارد.';
        }
        $warnings[] = 'برای تبدیل جریان به مبنای هفتگی، روز = روز کاری ثبت‌شده، ماه = ۱۲/۵۲ هفته و سال = ۵۲ هفته فرض شده است.';
        $warnings[] = 'هر فعالیت به‌شکل مربع با مساحت خالص ثبت‌شده رسم می‌شود؛ شکل واقعی، راهرو، ستون، ورودی، خروجی، حدود ساختمان و قیود ایمنی مدل نشده‌اند.';
        $warnings[] = 'گزینه‌ها هنگام مشاهده از داده‌های جاری ساخته می‌شوند و نسخهٔ ذخیره‌شده یا قابل‌ویرایش ندارند.';

        return [
            'ready' => count($options) > 0,
            'issues' => [],
            'warnings' => array_values(array_unique($warnings)),
            'options' => $options,
            'area_count' => count($names),
            'total_area' => array_sum(array_column($byName, 'area_m2')),
            'rated_pair_count' => $ratedPairCount,
            'possible_pair_count' => $possiblePairCount,
            'relationship_counts' => $relationshipCounts,
        ];
    }

    /** @return array<string,array{name:string,area_m2:float,width:float,height:float,x:float,y:float}>|null */
    private function construct(array $order, array $areas, array $flows, array $relationships, string $priority): ?array
    {
        $layout = [];
        foreach ($order as $name) {
            $block = $areas[$name];
            if (!$layout) {
                $block['x'] = 0.0;
                $block['y'] = 0.0;
                $layout[$name] = $block;
                continue;
            }

            $candidates = [];
            foreach ($layout as $placed) {
                $x = $placed['x'];
                $y = $placed['y'];
                $w = $placed['width'];
                $h = $placed['height'];
                $newW = $block['width'];
                $newH = $block['height'];
                foreach ([
                    [$x + $w, $y], [$x + $w, $y + $h - $newH],
                    [$x, $y + $h], [$x + $w - $newW, $y + $h],
                    [$x - $newW, $y], [$x - $newW, $y + $h - $newH],
                    [$x, $y - $newH], [$x + $w - $newW, $y - $newH],
                ] as [$candidateX, $candidateY]) {
                    $candidate = $block;
                    $candidate['x'] = $candidateX;
                    $candidate['y'] = $candidateY;
                    if ($this->overlapsAny($candidate, $layout)) continue;
                    $key = round($candidateX, 6) . ':' . round($candidateY, 6);
                    $candidates[$key] = $candidate;
                }
            }
            if (!$candidates) return null;

            $best = null;
            $bestTuple = null;
            foreach ($candidates as $candidate) {
                $candidateLayout = $layout;
                $candidateLayout[$name] = $candidate;
                $stats = $this->measure($candidateLayout, $flows, $relationships);
                $tuple = match ($priority) {
                    'relationship' => [$stats['x_violations'], -$stats['adjacent']['A'], -$stats['adjacent']['E'], -$stats['adjacent']['I'], $stats['flow_distance'], $stats['width'] * $stats['height']],
                    'compact' => [$stats['x_violations'], $stats['width'] * $stats['height'], $stats['flow_distance'], -$stats['adjacent']['A'], -$stats['adjacent']['E'], -$stats['adjacent']['I']],
                    default => [$stats['x_violations'], $stats['flow_distance'], -$stats['adjacent']['A'], -$stats['adjacent']['E'], -$stats['adjacent']['I'], $stats['width'] * $stats['height']],
                };
                if ($bestTuple === null || $this->compareTuples($tuple, $bestTuple) < 0) {
                    $best = $candidate;
                    $bestTuple = $tuple;
                }
            }
            if ($best === null) return null;
            $layout[$name] = $best;
        }

        $minX = min(array_column($layout, 'x'));
        $minY = min(array_column($layout, 'y'));
        foreach ($layout as &$rect) {
            $rect['x'] -= $minX;
            $rect['y'] -= $minY;
        }
        unset($rect);
        ksort($layout, SORT_STRING);
        return $layout;
    }

    private function compareTuples(array $a, array $b): int
    {
        foreach ($a as $index => $value) {
            if (abs($value - $b[$index]) > self::EPSILON) return $value <=> $b[$index];
        }
        return 0;
    }

    private function overlapsAny(array $candidate, array $layout): bool
    {
        foreach ($layout as $other) {
            $overlapX = $candidate['x'] < $other['x'] + $other['width'] - self::EPSILON
                && $candidate['x'] + $candidate['width'] > $other['x'] + self::EPSILON;
            $overlapY = $candidate['y'] < $other['y'] + $other['height'] - self::EPSILON
                && $candidate['y'] + $candidate['height'] > $other['y'] + self::EPSILON;
            if ($overlapX && $overlapY) return true;
        }
        return false;
    }

    /** @return array{width:float,height:float,envelope_area:float,flow_distance:float,adjacent:array{A:int,E:int,I:int},x_violations:int} */
    private function measure(array $layout, array $flows, array $relationships): array
    {
        $minX = min(array_column($layout, 'x'));
        $minY = min(array_column($layout, 'y'));
        $maxX = max(array_map(static fn(array $rect): float => $rect['x'] + $rect['width'], $layout));
        $maxY = max(array_map(static fn(array $rect): float => $rect['y'] + $rect['height'], $layout));
        $flowDistance = 0.0;
        foreach ($flows as $key => $weeklyFlow) {
            [$a, $b] = explode("\0", $key, 2);
            if (!isset($layout[$a], $layout[$b])) continue;
            $centerAX = $layout[$a]['x'] + $layout[$a]['width'] / 2;
            $centerAY = $layout[$a]['y'] + $layout[$a]['height'] / 2;
            $centerBX = $layout[$b]['x'] + $layout[$b]['width'] / 2;
            $centerBY = $layout[$b]['y'] + $layout[$b]['height'] / 2;
            $flowDistance += $weeklyFlow * (abs($centerAX - $centerBX) + abs($centerAY - $centerBY));
        }

        $adjacent = ['A' => 0, 'E' => 0, 'I' => 0];
        $xViolations = 0;
        foreach ($relationships as $key => $code) {
            if (!in_array($code, ['A', 'E', 'I', 'X'], true)) continue;
            [$a, $b] = explode("\0", $key, 2);
            if (!isset($layout[$a], $layout[$b]) || !$this->shareEdge($layout[$a], $layout[$b])) continue;
            if ($code === 'X') $xViolations++;
            else $adjacent[$code]++;
        }

        return [
            'width' => $maxX - $minX,
            'height' => $maxY - $minY,
            'envelope_area' => ($maxX - $minX) * ($maxY - $minY),
            'flow_distance' => $flowDistance,
            'adjacent' => $adjacent,
            'x_violations' => $xViolations,
        ];
    }

    private function shareEdge(array $a, array $b): bool
    {
        $verticalOverlap = min($a['y'] + $a['height'], $b['y'] + $b['height']) - max($a['y'], $b['y']);
        $horizontalOverlap = min($a['x'] + $a['width'], $b['x'] + $b['width']) - max($a['x'], $b['x']);
        $touchesVertically = abs(($a['x'] + $a['width']) - $b['x']) <= self::EPSILON
            || abs(($b['x'] + $b['width']) - $a['x']) <= self::EPSILON;
        $touchesHorizontally = abs(($a['y'] + $a['height']) - $b['y']) <= self::EPSILON
            || abs(($b['y'] + $b['height']) - $a['y']) <= self::EPSILON;
        return ($touchesVertically && $verticalOverlap > self::EPSILON)
            || ($touchesHorizontally && $horizontalOverlap > self::EPSILON);
    }

    private function weeklyFlow(float $quantity, string $period, int $workingDaysPerWeek): ?float
    {
        return match ($period) {
            'day' => $quantity * max(1, min(7, $workingDaysPerWeek)),
            'week' => $quantity,
            'month' => $quantity * 12 / 52,
            'year' => $quantity / 52,
            default => null,
        };
    }

    private function pairKey(string $a, string $b): string
    {
        if (strcmp($a, $b) > 0) [$a, $b] = [$b, $a];
        return $a . "\0" . $b;
    }

    private function assignParetoRanks(array &$options): void
    {
        $remaining = [];
        foreach ($options as $index => $option) {
            if ($option['feasible']) $remaining[] = $index;
        }
        $rank = 1;
        while ($remaining) {
            $front = [];
            foreach ($remaining as $candidateIndex) {
                $dominated = false;
                foreach ($remaining as $otherIndex) {
                    if ($otherIndex !== $candidateIndex && $this->dominates($options[$otherIndex], $options[$candidateIndex])) {
                        $dominated = true;
                        break;
                    }
                }
                if (!$dominated) $front[] = $candidateIndex;
            }
            if (!$front) break;
            foreach ($front as $index) $options[$index]['pareto_rank'] = $rank;
            $remaining = array_values(array_diff($remaining, $front));
            $rank++;
        }
    }

    private function dominates(array $a, array $b): bool
    {
        $noWorse = $a['flow_distance'] <= $b['flow_distance'] + self::EPSILON
            && $a['envelope_area'] <= $b['envelope_area'] + self::EPSILON
            && $a['adjacent']['A'] >= $b['adjacent']['A']
            && $a['adjacent']['E'] >= $b['adjacent']['E']
            && $a['adjacent']['I'] >= $b['adjacent']['I'];
        $strictlyBetter = $a['flow_distance'] < $b['flow_distance'] - self::EPSILON
            || $a['envelope_area'] < $b['envelope_area'] - self::EPSILON
            || $a['adjacent']['A'] > $b['adjacent']['A']
            || $a['adjacent']['E'] > $b['adjacent']['E']
            || $a['adjacent']['I'] > $b['adjacent']['I'];
        return $noWorse && $strictlyBetter;
    }
}
