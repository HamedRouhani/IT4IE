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
                $range = ($best[$j] - $worst[$j]) ?: 1;
                $val = ($best[$j] - $matrix[$i][$j]) / $range * ($weights[$j] ?? 0);
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
            'scores'  => array_map(fn($q) => round($q, 4), $Q)
        ];
    }
}