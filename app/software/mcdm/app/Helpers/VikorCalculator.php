<?php

namespace App\Software\Mcdm\Helpers;

class VikorCalculator
{
    public static function calculate(array $matrix, array $weights, array $types, float $v = 0.5): array
    {
        $n = count($matrix);
        $m = count($weights);

        if ($n === 0 || $m === 0) {
            return ['status' => 'error', 'message' => 'ماتریس تصمیم خالی است.'];
        }
        if (count($types) !== $m || $v < 0 || $v > 1 || !is_finite($v)) return ['status' => 'error', 'message' => 'وزن راهبردی VIKOR باید بین صفر و یک باشد و ابعاد ورودی سازگار باشند.'];
        foreach ($matrix as $row) {
            if (!is_array($row) || count($row) !== $m) return ['status' => 'error', 'message' => 'ماتریس تصمیم باید مستطیلی و کامل باشد.'];
            foreach ($row as $value) if (!is_numeric($value) || !is_finite((float)$value)) return ['status' => 'error', 'message' => 'مقادیر ماتریس باید عددی و متناهی باشند.'];
        }
        foreach ($weights as $weight) if (!is_numeric($weight) || !is_finite((float)$weight) || (float)$weight < 0) return ['status' => 'error', 'message' => 'وزن معیارها باید نامنفی و متناهی باشند.'];
        $weightTotal = array_sum($weights);
        if ($weightTotal <= 0) return ['status' => 'error', 'message' => 'مجموع وزن معیارها باید مثبت باشد.'];
        $weights = array_map(fn($weight) => (float)$weight / $weightTotal, $weights);
        foreach ($types as $type) if (!in_array($type, ['benefit', 'cost'], true)) return ['status' => 'error', 'message' => 'نوع معیار باید سودی یا هزینه‌ای باشد.'];

        $best = $worst = [];
        for ($j = 0; $j < $m; $j++) {
            $col = array_column($matrix, $j);
            if (($types[$j] ?? 'benefit') === 'benefit') {
                $best[$j] = max($col); $worst[$j] = min($col);
            } else {
                $best[$j] = min($col); $worst[$j] = max($col);
            }
        }

        $S = $R = [];
        for ($i = 0; $i < $n; $i++) {
            $s = 0; $r = 0;
            for ($j = 0; $j < $m; $j++) {
                $range = abs($best[$j] - $worst[$j]);
                $deviation = ($types[$j] === 'benefit')
                    ? ($best[$j] - $matrix[$i][$j])
                    : ($matrix[$i][$j] - $best[$j]);
                $val = $range > 0 ? ($deviation / $range) * $weights[$j] : 0.0;
                $s += $val;
                $r = max($r, $val);
            }
            $S[$i] = $s; $R[$i] = $r;
        }

        $minS = min($S); $maxS = max($S);
        $minR = min($R); $maxR = max($R);
        $rangeS = ($maxS - $minS) ?: 1;
        $rangeR = ($maxR - $minR) ?: 1;

        $Q = [];
        for ($i = 0; $i < $n; $i++) {
            $Q[$i] = $v * (($S[$i] - $minS) / $rangeS) + (1 - $v) * (($R[$i] - $minR) / $rangeR);
        }

        asort($Q);
        $qOrder = array_keys($Q);
        $first = $qOrder[0];
        $second = $qOrder[1] ?? null;
        $dq = $n > 1 ? 1.0 / ($n - 1) : 0.0;
        $acceptableAdvantage = $second === null || ($Q[$second] - $Q[$first]) + 1e-12 >= $dq;
        asort($S);
        $bestS = array_key_first($S);
        asort($R);
        $bestR = array_key_first($R);
        $acceptableStability = $first === $bestS || $first === $bestR;

        $compromise = [$first];
        if (!$acceptableAdvantage && $second !== null) $compromise[] = $second;
        if (!$acceptableStability) {
            foreach ([$bestS, $bestR] as $idx) if (!in_array($idx, $compromise, true)) $compromise[] = $idx;
        }

        $ranking = [];
        $rank = 1;
        foreach ($Q as $idx => $q) {
            $ranking[] = [
                'alternative_index' => $idx,
                'score' => round($q, 6),
                'rank'  => $rank++,
                'S' => round($S[$idx], 4),
                'R' => round($R[$idx], 4)
            ];
        }

        return [
            'status'  => 'success',
            'method'  => 'VIKOR',
            'v'       => $v,
            'ranking' => $ranking,
            'scores'  => array_map(fn($q) => round($q, 4), $Q),
            'compromise_alternative_indices' => $compromise,
            'conditions' => [
                'acceptable_advantage' => $acceptableAdvantage,
                'acceptable_stability' => $acceptableStability,
                'dq' => round($dq, 6),
            ],
        ];
    }
}
