<?php
namespace App\Software\Or\Helpers;

class GameTheoryEngine
{
    /**
     * حل بازی بر اساس نوع
     */
    public static function solve(string $gameType, array $p1Strats, array $p2Strats, array $matrixA, array $matrixB = []): array
    {
        if ($gameType === 'zero_sum') {
            return self::solveZeroSum($p1Strats, $p2Strats, $matrixA);
        } elseif ($gameType === 'bimatrix') {
            return self::solveBimatrix($p1Strats, $p2Strats, $matrixA, $matrixB);
        }
        
        return ['status' => 'error', 'message' => 'نوع بازی نامعتبر است.'];
    }

    /**
     * ۱. حل بازی دو نفره مجموع صفر
     */
    private static function solveZeroSum(array $p1Strats, array $p2Strats, array $matrix): array
    {
        $rows = count($matrix);
        $cols = count($matrix[0]);

        // بررسی نقطه زینی (Saddle Point)
        $saddlePoint = self::findSaddlePoint($matrix);
        
        if ($saddlePoint['exists']) {
            return [
                'status' => 'ok',
                'type' => 'pure',
                'game_mode' => 'zero_sum',
                'saddle_point' => $saddlePoint,
                'value_of_game' => $saddlePoint['value'],
                'p1_optimal' => $saddlePoint['row'],
                'p2_optimal' => $saddlePoint['col'],
                'interpretation' => "بازی دارای نقطه زینی (Saddle Point) است. بازیکن ۱ باید استراتژی خالص «{$p1Strats[$saddlePoint['row']]}» و بازیکن ۲ باید استراتژی خالص «{$p2Strats[$saddlePoint['col']]}» را انتخاب کند. ارزش بازی برابر {$saddlePoint['value']} است."
            ];
        }

        // اگر نقطه زینی ندارد، فقط ماتریس ۲×۲ را با فرمول جبری حل می‌کنیم
        if ($rows === 2 && $cols === 2) {
            $a = $matrix[0][0]; $b = $matrix[0][1];
            $c = $matrix[1][0]; $d = $matrix[1][1];
            
            $denom = ($a + $d) - ($b + $c);
            
            if (abs($denom) < 1e-9) {
                return ['status' => 'error', 'message' => 'ماتریس دارای ساختار ویژه است و با این روش حل نمی‌شود (مخرج کسر صفر).'];
            }

            $p = ($d - $c) / $denom; // احتمال انتخاب سطر ۱ توسط بازیکن ۱
            $q = ($d - $b) / $denom; // احتمال انتخاب ستون ۱ توسط بازیکن ۲
            $v = ($a * $d - $b * $c) / $denom; // ارزش بازی

            return [
                'status' => 'ok',
                'type' => 'mixed',
                'game_mode' => 'zero_sum',
                'value_of_game' => round($v, 4),
                'p1_probs' => [round($p, 4), round(1 - $p, 4)],
                'p2_probs' => [round($q, 4), round(1 - $q, 4)],
                'interpretation' => "بازی نقطه زینی ندارد و نیاز به استراتژی ترکیبی دارد.\n" .
                    "بازیکن ۱ باید استراتژی «{$p1Strats[0]}» را با احتمال " . round($p * 100, 1) . "% و «{$p1Strats[1]}» را با احتمال " . round((1 - $p) * 100, 1) . "% انتخاب کند.\n" .
                    "بازیکن ۲ باید استراتژی «{$p2Strats[0]}» را با احتمال " . round($q * 100, 1) . "% و «{$p2Strats[1]}» را با احتمال " . round((1 - $q) * 100, 1) . "% انتخاب کند.\n" .
                    "ارزش مورد انتظار بازی (Value of Game) برابر " . round($v, 4) . " است."
            ];
        }

        return [
            'status' => 'error',
            'message' => "ماتریس {$rows}×{$cols} فاقد نقطه زینی است. حل ماتریس‌های بزرگتر از ۲×۲ بدون نقطه زینی نیازمند روش برنامه‌ریزی خطی (LP) است."
        ];
    }

    /**
     * ۲. حل بازی دو نفره مجموع غیر صفر (Bimatrix) - یافتن تعادل نش خالص
     */
    private static function solveBimatrix(array $p1Strats, array $p2Strats, array $matrixA, array $matrixB): array
    {
        $rows = count($matrixA);
        $cols = count($matrixA[0]);
        $nashEquilibria = [];

        // جستجو برای تعادل نش خالص (Pure Strategy Nash Equilibrium)
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $isBestForP1 = true;
                for ($k = 0; $k < $rows; $k++) {
                    if ($matrixA[$k][$j] > $matrixA[$i][$j]) {
                        $isBestForP1 = false;
                        break;
                    }
                }

                $isBestForP2 = true;
                for ($k = 0; $k < $cols; $k++) {
                    if ($matrixB[$i][$k] > $matrixB[$i][$j]) {
                        $isBestForP2 = false;
                        break;
                    }
                }

                if ($isBestForP1 && $isBestForP2) {
                    $nashEquilibria[] = [
                        'row' => $i,
                        'col' => $j,
                        'p1_strat' => $p1Strats[$i],
                        'p2_strat' => $p2Strats[$j],
                        'p1_payoff' => $matrixA[$i][$j],
                        'p2_payoff' => $matrixB[$i][$j]
                    ];
                }
            }
        }

        if (!empty($nashEquilibria)) {
            $interpretation = "تعداد " . count($nashEquilibria) . " تعادل نش خالص یافت شد:\n";
            foreach ($nashEquilibria as $idx => $ne) {
                $interpretation .= ($idx + 1) . ". بازیکن ۱ استراتژی «{$ne['p1_strat']}» و بازیکن ۲ استراتژی «{$ne['p2_strat']}» را انتخاب کند. (پرداخت‌ها: بازیکن ۱ = {$ne['p1_payoff']}، بازیکن ۲ = {$ne['p2_payoff']})\n";
            }
            
            return [
                'status' => 'ok',
                'type' => 'pure_nash',
                'game_mode' => 'bimatrix',
                'nash_equilibria' => $nashEquilibria,
                'interpretation' => trim($interpretation)
            ];
        }

        return [
            'status' => 'error',
            'message' => "هیچ تعادل نش خالصی در این بازی وجود ندارد. یافتن تعادل نش ترکیبی برای ماتریس‌های بزرگ نیازمند الگوریتم‌های پیشرفته (مانند Lemke-Howson) است."
        ];
    }

    /** یافتن نقطه زینی */
    private static function findSaddlePoint(array $matrix): array
    {
        $rows = count($matrix);
        $cols = count($matrix[0]);
        $rowMins = [];
        $colMaxs = array_fill(0, $cols, -INF);

        for ($i = 0; $i < $rows; $i++) {
            $min = INF;
            for ($j = 0; $j < $cols; $j++) {
                if ($matrix[$i][$j] < $min) $min = $matrix[$i][$j];
                if ($matrix[$i][$j] > $colMaxs[$j]) $colMaxs[$j] = $matrix[$i][$j];
            }
            $rowMins[] = $min;
        }

        $maximin = max($rowMins);
        $minimax = min($colMaxs);

        if ($maximin === $minimax) {
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $cols; $j++) {
                    if ($matrix[$i][$j] === $maximin) {
                        return ['exists' => true, 'value' => $maximin, 'row' => $i, 'col' => $j];
                    }
                }
            }
        }
        return ['exists' => false];
    }
}