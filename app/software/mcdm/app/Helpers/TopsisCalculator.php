<?php

namespace App\Software\Mcdm\Helpers;

class TopsisCalculator
{
    public static function calculate(array $matrix, array $weights, array $types): array
    {
        $n = count($matrix);
        $m = count($weights);

        if ($n === 0 || $m === 0) {
            return ['status' => 'error', 'message' => 'ماتریس تصمیم خالی است.'];
        }

        // ۱. نرمال‌سازی برداری
        $normalized = [];
        for ($j = 0; $j < $m; $j++) {
            $sumSq = 0;
            for ($i = 0; $i < $n; $i++) $sumSq += $matrix[$i][$j] ** 2;
            $denom = sqrt($sumSq) ?: 1;
            for ($i = 0; $i < $n; $i++) $normalized[$i][$j] = $matrix[$i][$j] / $denom;
        }

        // ۲. اعمال وزن
        $weighted = [];
        for ($i = 0; $i < $n; $i++)
            for ($j = 0; $j < $m; $j++)
                $weighted[$i][$j] = $normalized[$i][$j] * ($weights[$j] ?? 0);

        // ۳. ایده‌آل مثبت و منفی
        $idealPos = $idealNeg = [];
        for ($j = 0; $j < $m; $j++) {
            $col = array_column($weighted, $j);
            if (($types[$j] ?? 'benefit') === 'benefit') {
                $idealPos[$j] = max($col);
                $idealNeg[$j] = min($col);
            } else {
                $idealPos[$j] = min($col);
                $idealNeg[$j] = max($col);
            }
        }

        // ۴. فاصله‌ها و امتیاز
        $scores = [];
        for ($i = 0; $i < $n; $i++) {
            $sp = $sn = 0;
            for ($j = 0; $j < $m; $j++) {
                $sp += ($weighted[$i][$j] - $idealPos[$j]) ** 2;
                $sn += ($weighted[$i][$j] - $idealNeg[$j]) ** 2;
            }
            $dp = sqrt($sp);
            $dn = sqrt($sn);
            $scores[$i] = ($dp + $dn) > 0 ? $dn / ($dp + $dn) : 0;
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
            'method'  => 'TOPSIS',
            'ranking' => $ranking,
            'scores'  => array_map(fn($s) => round($s, 4), $scores)
        ];
    }
}