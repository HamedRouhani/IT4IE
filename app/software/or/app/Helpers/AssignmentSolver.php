<?php
namespace App\Software\Or\Helpers;

class AssignmentSolver
{
    const BIG_M = 999999;
    const MAX_ITER = 100;

    public static function solve(array $costMatrix, bool $isMinimize = true): array
    {
        $n = count($costMatrix);
        if ($n === 0)
            return ['status'=>'error','message'=>'ماتریس تخصیص خالی است.'];
        foreach ($costMatrix as $row)
            if (count($row) !== $n)
                return ['status'=>'error','message'=>'ماتریس تخصیص باید مربع باشد.'];

        // جایگزینی null با Big-M
        $cost = [];
        for ($i = 0; $i < $n; $i++)
            for ($j = 0; $j < $n; $j++)
                $cost[$i][$j] = ($costMatrix[$i][$j] !== null) ? (float)$costMatrix[$i][$j] : self::BIG_M;

        $orig = $cost;

        // تبدیل ماکزیمم به کمینه
        if (!$isMinimize) {
            $mx = 0;
            for ($i = 0; $i < $n; $i++)
                for ($j = 0; $j < $n; $j++)
                    if ($cost[$i][$j] < self::BIG_M) $mx = max($mx, $cost[$i][$j]);
            for ($i = 0; $i < $n; $i++)
                for ($j = 0; $j < $n; $j++)
                    if ($cost[$i][$j] < self::BIG_M) $cost[$i][$j] = $mx - $cost[$i][$j];
        }

        // کاهش سطری
        for ($i = 0; $i < $n; $i++) {
            $mr = min($cost[$i]);
            if ($mr > 0) for ($j = 0; $j < $n; $j++) $cost[$i][$j] -= $mr;
        }

        // کاهش ستونی
        for ($j = 0; $j < $n; $j++) {
            $mc = PHP_FLOAT_MAX;
            for ($i = 0; $i < $n; $i++) $mc = min($mc, $cost[$i][$j]);
            if ($mc > 0) for ($i = 0; $i < $n; $i++) $cost[$i][$j] -= $mc;
        }

        // تخصیص حریصانه با پوشش صفرها
        $assignment = [];
        $colUsed = array_fill(0, $n, false);

        // اول سطرهایی با فقط یک صفر
        for ($i = 0; $i < $n; $i++) {
            $zc = [];
            for ($j = 0; $j < $n; $j++)
                if (!$colUsed[$j] && abs($cost[$i][$j]) < 1e-9) $zc[] = $j;
            if (count($zc) === 1) {
                $assignment[$i] = $zc[0];
                $colUsed[$zc[0]] = true;
            }
        }
        // بقیه سطرها
        for ($i = 0; $i < $n; $i++) {
            if (isset($assignment[$i])) continue;
            for ($j = 0; $j < $n; $j++) {
                if (!$colUsed[$j] && abs($cost[$i][$j]) < 1e-9) {
                    $assignment[$i] = $j;
                    $colUsed[$j] = true;
                    break;
                }
            }
        }

        if (count($assignment) < $n) {
            // Hungarian کامل با پوشش خطوط
            $result = self::hungarianFull($cost, $n);
            if ($result !== null) $assignment = $result;
        }

        if (count($assignment) < $n)
            return ['status'=>'error','message'=>'تخصیص کامل یافت نشد.'];

        // محاسبه هزینه کل با ماتریس اصلی
        $totalCost = 0;
        $assignments = [];
        foreach ($assignment as $i => $j) {
            $rc = $orig[$i][$j];
            $totalCost += $rc;
            $assignments[] = ['agent_index'=>$i, 'task_index'=>$j, 'cost'=>$rc];
        }

        $hasProhibited = false;
        foreach ($assignment as $i => $j)
            if ($orig[$i][$j] >= self::BIG_M) { $hasProhibited = true; break; }

        return [
            'status'         => 'success',
            'method'         => 'Hungarian',
            'assignments'    => $assignments,
            'assignment_map' => $assignment,
            'total_cost'     => round($totalCost, 4),
            'has_prohibited' => $hasProhibited,
            'smart_feedback' => self::feedback($totalCost, $hasProhibited),
        ];
    }

    private static function hungarianFull(array $cost, int $n): ?array
    {
        // Hungarian با پوشش خطوط (Kőnig)
        for ($iter = 0; $iter < self::MAX_ITER; $iter++) {
            // تخصیص حداکثری صفرها
            $match = array_fill(0, $n, -1);
            for ($i = 0; $i < $n; $i++) {
                $visited = array_fill(0, $n, false);
                self::bipartiteMatch($i, $cost, $match, $visited, $n);
            }

            $matched = 0;
            for ($i = 0; $i < $n; $i++) if ($match[$i] >= 0) $matched++;

            if ($matched === $n) {
                $result = [];
                for ($i = 0; $i < $n; $i++) $result[$i] = $match[$i];
                return $result;
            }

            // پوشش خطوط و بهبود ماتریس
            $rowCover = array_fill(0, $n, false);
            $colCover = array_fill(0, $n, false);
            self::coverLines($cost, $match, $rowCover, $colCover, $n);

            // یافتن کمترین مقدار پوشش‌نداده
            $minVal = PHP_FLOAT_MAX;
            for ($i = 0; $i < $n; $i++)
                for ($j = 0; $j < $n; $j++)
                    if (!$rowCover[$i] && !$colCover[$j])
                        $minVal = min($minVal, $cost[$i][$j]);

            if ($minVal === PHP_FLOAT_MAX) return null;

            // اعمال بهبود
            for ($i = 0; $i < $n; $i++)
                for ($j = 0; $j < $n; $j++) {
                    if (!$rowCover[$i] && !$colCover[$j]) $cost[$i][$j] -= $minVal;
                    elseif ($rowCover[$i] && $colCover[$j]) $cost[$i][$j] += $minVal;
                }
        }
        return null;
    }

    private static function bipartiteMatch(int $i, array $cost, array &$match, array &$visited, int $n): bool
    {
        for ($j = 0; $j < $n; $j++) {
            if (abs($cost[$i][$j]) < 1e-9 && !$visited[$j]) {
                $visited[$j] = true;
                if ($match[$j] === -1 || self::bipartiteMatch($match[$j], $cost, $match, $visited, $n)) {
                    $match[$j] = $i;
                    return true;
                }
            }
        }
        return false;
    }

    private static function coverLines(array $cost, array $match, array &$rowCover, array &$colCover, int $n): void
    {
        $unmatchedRows = [];
        for ($i = 0; $i < $n; $i++)
            if ($match[$i] === -1) $unmatchedRows[] = $i;

        $markedRows = array_fill(0, $n, false);
        $markedCols = array_fill(0, $n, false);

        foreach ($unmatchedRows as $i) $markedRows[$i] = true;

        $changed = true;
        while ($changed) {
            $changed = false;
            for ($i = 0; $i < $n; $i++) {
                if (!$markedRows[$i]) continue;
                for ($j = 0; $j < $n; $j++) {
                    if (!$markedCols[$j] && abs($cost[$i][$j]) < 1e-9) {
                        $markedCols[$j] = true;
                        $changed = true;
                    }
                }
            }
            for ($j = 0; $j < $n; $j++) {
                if (!$markedCols[$j]) continue;
                for ($i = 0; $i < $n; $i++) {
                    if (!$markedRows[$i] && $match[$i] === $j) {
                        $markedRows[$i] = true;
                        $changed = true;
                    }
                }
            }
        }

        for ($i = 0; $i < $n; $i++) $rowCover[$i] = !$markedRows[$i];
        for ($j = 0; $j < $n; $j++) $colCover[$j] = $markedCols[$j];
    }

    private static function feedback(float $totalCost, bool $hasProhibited): string
    {
        $f = ["💰 هزینه کل تخصیص بهینه: " . number_format($totalCost, 2)];
        $f[] = $hasProhibited
            ? "⚠️ از تخصیص‌های ممنوعه استفاده شده است."
            : "✅ همه تخصیص‌ها مجاز هستند.";
        return implode(' ', $f);
    }
}