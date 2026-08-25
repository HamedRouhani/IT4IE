<?php

namespace App\Software\Mcdm\Helpers;

class SawCalculator
{
    public static function calculate(array $matrix, array $weights, array $types): array
    {
        $n = count($matrix);
        $m = count($weights);

        if ($n === 0 || $m === 0) {
            return ['status' => 'error', 'message' => 'ماتریس تصمیم خالی است.'];
        }

        // ۱. نرمال‌سازی
        $normalized = [];
        for ($j = 0; $j < $m; $j++) {
            $col = array_column($matrix, $j);
            $max = max($col);
            $min = min($col);
            for ($i = 0; $i < $n; $i++) {
                if (($types[$j] ?? 'benefit') === 'benefit') {
                    $normalized[$i][$j] = $max > 0 ? $matrix[$i][$j] / $max : 0;
                } else {
                    $normalized[$i][$j] = $matrix[$i][$j] > 0 ? $min / $matrix[$i][$j] : 0;
                }
            }
        }

        // ۲. مجموع وزنی
        $scores = [];
        for ($i = 0; $i < $n; $i++) {
            $s = 0;
            for ($j = 0; $j < $m; $j++) $s += $normalized[$i][$j] * ($weights[$j] ?? 0);
            $scores[$i] = $s;
        }

        arsort($scores);
        $ranking = [];
        $rank = 1;
        foreach ($scores as $idx => $score) {
            $ranking[] = [
                'alternative_index' => $idx,
                'score' => round($score, 6),
                'rank'  => $rank++
            ];
        }

        return [
            'status'  => 'success',
            'method'  => 'SAW',
            'ranking' => $ranking,
            'scores'  => array_map(fn($s) => round($s, 4), $scores)
        ];
    }
}