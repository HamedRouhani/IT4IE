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
        if (count($types) !== $m) return ['status' => 'error', 'message' => 'تعداد انواع معیارها با ستون‌های ماتریس سازگار نیست.'];
        foreach ($matrix as $row) {
            if (!is_array($row) || count($row) !== $m) return ['status' => 'error', 'message' => 'ماتریس تصمیم باید مستطیلی و کامل باشد.'];
            foreach ($row as $value) if (!is_numeric($value) || !is_finite((float)$value) || (float)$value < 0) return ['status' => 'error', 'message' => 'SAW با این پیاده‌سازی فقط مقادیر نامنفی را می‌پذیرد.'];
        }
        foreach ($weights as $weight) if (!is_numeric($weight) || !is_finite((float)$weight) || (float)$weight < 0) return ['status' => 'error', 'message' => 'وزن معیارها باید نامنفی و متناهی باشند.'];
        $weightTotal = array_sum($weights);
        if ($weightTotal <= 0) return ['status' => 'error', 'message' => 'مجموع وزن معیارها باید مثبت باشد.'];
        $weights = array_map(fn($weight) => (float)$weight / $weightTotal, $weights);
        foreach ($types as $type) if (!in_array($type, ['benefit', 'cost'], true)) return ['status' => 'error', 'message' => 'نوع معیار باید سودی یا هزینه‌ای باشد.'];

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
                    if ($matrix[$i][$j] <= 0 || $min <= 0) return ['status' => 'error', 'message' => 'معیار هزینه‌ای در SAW باید مقادیر مثبت داشته باشد.'];
                    $normalized[$i][$j] = $min / $matrix[$i][$j];
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
