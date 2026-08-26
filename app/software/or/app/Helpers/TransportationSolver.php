<?php
namespace App\Software\Or\Helpers;

class TransportationSolver
{
    const BIG_M = 999999;
    const MAX_ITER = 200;

    public static function solve(array $costMatrix, array $supply, array $demand,
                                  string $method = 'VAM', bool $optimize = true): array
    {
        $m = count($supply); $n = count($demand);
        if ($m === 0 || $n === 0)
            return ['status'=>'error','message'=>'ماتریس حمل و نقل خالی است.'];

        $totalSupply = array_sum($supply);
        $totalDemand = array_sum($demand);
        if ($totalSupply !== $totalDemand)
            return ['status'=>'error','message'=>"مسئله نامتوازن است. عرضه: {$totalSupply}، تقاضا: {$totalDemand}"];

        // جایگزینی null با Big-M
        $cost = [];
        for ($i = 0; $i < $m; $i++)
            for ($j = 0; $j < $n; $j++)
                $cost[$i][$j] = ($costMatrix[$i][$j] !== null) ? (float)$costMatrix[$i][$j] : self::BIG_M;

        // روش اولیه
        $allocation = match (strtoupper($method)) {
            'NWC' => self::northWestCorner($cost, $supply, $demand),
            'LCM' => self::leastCost($cost, $supply, $demand),
            'VAM' => self::vogelApproximation($cost, $supply, $demand),
            default => self::vogelApproximation($cost, $supply, $demand),
        };

        $initialCost = self::calcCost($allocation, $cost);
        $iterations = 0; $history = [];

        if ($optimize) {
            $r = self::modiOptimize($cost, $allocation, $m, $n);
            $allocation = $r['alloc'];
            $iterations = $r['iterations'];
            $history = $r['history'];
        }

        $optimalCost = self::calcCost($allocation, $cost);

        $basicCells = [];
        for ($i = 0; $i < $m; $i++)
            for ($j = 0; $j < $n; $j++)
                if ($allocation[$i][$j] > 0)
                    $basicCells[] = ['row'=>$i, 'col'=>$j, 'value'=>$allocation[$i][$j]];

        $hasProhibited = false;
        foreach ($basicCells as $c)
            if ($cost[$c['row']][$c['col']] >= self::BIG_M) { $hasProhibited = true; break; }

        return [
            'status'         => 'success',
            'method'         => strtoupper($method),
            'allocation'     => $allocation,
            'basic_cells'    => $basicCells,
            'initial_cost'   => round($initialCost, 4),
            'optimal_cost'   => round($optimalCost, 4),
            'iterations'     => $iterations,
            'modi_history'   => $history,
            'has_prohibited' => $hasProhibited,
            'total_supply'   => $totalSupply,
            'total_demand'   => $totalDemand,
            'is_degenerate'  => count($basicCells) < ($m + $n - 1),
            'smart_feedback' => self::feedback($optimalCost, $initialCost, $iterations, $hasProhibited),
        ];
    }

    // ---- Northwest Corner ----
    private static function northWestCorner(array $cost, array $supply, array $demand): array
    {
        $m = count($supply); $n = count($demand);
        $alloc = array_fill(0, $m, array_fill(0, $n, 0));
        $s = $supply; $d = $demand; $i = 0; $j = 0;
        while ($i < $m && $j < $n) {
            $q = min($s[$i], $d[$j]);
            $alloc[$i][$j] = $q;
            $s[$i] -= $q; $d[$j] -= $q;
            if ($s[$i] === 0) $i++;
            if ($d[$j] === 0) $j++;
        }
        return $alloc;
    }

    // ---- Least Cost Method ----
    private static function leastCost(array $cost, array $supply, array $demand): array
    {
        $m = count($supply); $n = count($demand);
        $alloc = array_fill(0, $m, array_fill(0, $n, 0));
        $s = $supply; $d = $demand;
        $rd = array_fill(0, $m, false);
        $cd = array_fill(0, $n, false);
        $done = 0; $total = array_sum($supply);

        while ($done < $total) {
            $mc = PHP_FLOAT_MAX; $bi = -1; $bj = -1;
            for ($i = 0; $i < $m; $i++) {
                if ($rd[$i]) continue;
                for ($j = 0; $j < $n; $j++) {
                    if ($cd[$j]) continue;
                    if ($cost[$i][$j] < $mc) { $mc = $cost[$i][$j]; $bi = $i; $bj = $j; }
                }
            }
            if ($bi === -1) break;
            $q = min($s[$bi], $d[$bj]);
            $alloc[$bi][$bj] = $q;
            $s[$bi] -= $q; $d[$bj] -= $q; $done += $q;
            if ($s[$bi] === 0) $rd[$bi] = true;
            if ($d[$bj] === 0) $cd[$bj] = true;
        }
        return $alloc;
    }

    // ---- Vogel's Approximation Method ----
    private static function vogelApproximation(array $cost, array $supply, array $demand): array
    {
        $m = count($supply); $n = count($demand);
        $alloc = array_fill(0, $m, array_fill(0, $n, 0));
        $s = $supply; $d = $demand;
        $rd = array_fill(0, $m, false);
        $cd = array_fill(0, $n, false);
        $done = 0; $total = array_sum($supply);

        while ($done < $total) {
            // جریمه سطرها
            $rp = [];
            for ($i = 0; $i < $m; $i++) {
                if ($rd[$i]) { $rp[$i] = -1; continue; }
                $cs = [];
                for ($j = 0; $j < $n; $j++) if (!$cd[$j]) $cs[] = $cost[$i][$j];
                sort($cs);
                $rp[$i] = (count($cs) >= 2) ? ($cs[1] - $cs[0]) : ($cs[0] ?? 0);
            }
            // جریمه ستون‌ها
            $cp = [];
            for ($j = 0; $j < $n; $j++) {
                if ($cd[$j]) { $cp[$j] = -1; continue; }
                $cs = [];
                for ($i = 0; $i < $m; $i++) if (!$rd[$i]) $cs[] = $cost[$i][$j];
                sort($cs);
                $cp[$j] = (count($cs) >= 2) ? ($cs[1] - $cs[0]) : ($cs[0] ?? 0);
            }

            $mrp = max($rp); $mcp = max($cp);

            if ($mrp >= $mcp) {
                $i = array_search($mrp, $rp);
                $mc = PHP_FLOAT_MAX; $bj = -1;
                for ($j = 0; $j < $n; $j++)
                    if (!$cd[$j] && $cost[$i][$j] < $mc) { $mc = $cost[$i][$j]; $bj = $j; }
                if ($bj === -1) break;
                $q = min($s[$i], $d[$bj]);
                $alloc[$i][$bj] = $q;
                $s[$i] -= $q; $d[$bj] -= $q; $done += $q;
                if ($s[$i] === 0) $rd[$i] = true;
                if ($d[$bj] === 0) $cd[$bj] = true;
            } else {
                $j = array_search($mcp, $cp);
                $mc = PHP_FLOAT_MAX; $bi = -1;
                for ($i = 0; $i < $m; $i++)
                    if (!$rd[$i] && $cost[$i][$j] < $mc) { $mc = $cost[$i][$j]; $bi = $i; }
                if ($bi === -1) break;
                $q = min($s[$bi], $d[$j]);
                $alloc[$bi][$j] = $q;
                $s[$bi] -= $q; $d[$j] -= $q; $done += $q;
                if ($s[$bi] === 0) $rd[$bi] = true;
                if ($d[$j] === 0) $cd[$j] = true;
            }
        }
        return $alloc;
    }

    // ---- MODI Optimization ----
    private static function modiOptimize(array $cost, array $allocation, int $m, int $n): array
    {
        $alloc = $allocation;
        $history = [];
        $iterations = 0;

        while ($iterations < self::MAX_ITER) {
            $iterations++;

            // سلول‌های پایه
            $bc = [];
            for ($i = 0; $i < $m; $i++)
                for ($j = 0; $j < $n; $j++)
                    if ($alloc[$i][$j] > 0) $bc[] = [$i, $j];

            // رفع degeneracy
            $req = $m + $n - 1;
            if (count($bc) < $req) {
                $alloc = self::fixDegeneracy($alloc, $m, $n, $req);
                $bc = [];
                for ($i = 0; $i < $m; $i++)
                    for ($j = 0; $j < $n; $j++)
                        if ($alloc[$i][$j] > 0) $bc[] = [$i, $j];
            }

            // محاسبه u و v
            $u = array_fill(0, $m, null);
            $v = array_fill(0, $n, null);
            $u[0] = 0;
            $changed = true; $loop = 0;
            while ($changed && $loop < $m + $n + 5) {
                $changed = false; $loop++;
                foreach ($bc as [$bi, $bj]) {
                    if ($u[$bi] !== null && $v[$bj] === null) { $v[$bj] = $cost[$bi][$bj] - $u[$bi]; $changed = true; }
                    elseif ($u[$bi] === null && $v[$bj] !== null) { $u[$bi] = $cost[$bi][$bj] - $v[$bj]; $changed = true; }
                }
            }
            for ($i = 0; $i < $m; $i++) if ($u[$i] === null) $u[$i] = 0;
            for ($j = 0; $j < $n; $j++) if ($v[$j] === null) $v[$j] = 0;

            // opportunity cost
            $minDelta = 0; $enterCell = null;
            for ($i = 0; $i < $m; $i++)
                for ($j = 0; $j < $n; $j++) {
                    if ($alloc[$i][$j] > 0) continue;
                    $delta = $cost[$i][$j] - $u[$i] - $v[$j];
                    if ($delta < $minDelta) { $minDelta = $delta; $enterCell = [$i, $j]; }
                }

            if ($enterCell === null || $minDelta >= 0) {
                $history[] = ['iteration'=>$iterations, 'action'=>'optimal', 'message'=>'✅ جواب بهینه یافت شد.'];
                break;
            }

            // مسیر بسته
            $loopPath = self::findClosedLoop($enterCell, $alloc, $m, $n);
            if ($loopPath === null) {
                $history[] = ['iteration'=>$iterations, 'action'=>'error', 'message'=>'⚠️ مسیر بسته یافت نشد.'];
                break;
            }

            // theta
            $theta = PHP_FLOAT_MAX;
            for ($k = 1; $k < count($loopPath); $k += 2) {
                [$ri, $rj] = $loopPath[$k];
                $theta = min($theta, $alloc[$ri][$rj]);
            }
            if ($theta === PHP_FLOAT_MAX || $theta <= 0) $theta = 0.0001;

            // اعمال بهبود
            for ($k = 0; $k < count($loopPath); $k++) {
                [$ri, $rj] = $loopPath[$k];
                $alloc[$ri][$rj] += ($k % 2 === 0) ? $theta : -$theta;
                if (abs($alloc[$ri][$rj]) < 0.00001) $alloc[$ri][$rj] = 0;
            }

            $cc = self::calcCost($alloc, $cost);
            $history[] = [
                'iteration'=>$iterations, 'action'=>'improve',
                'enter_cell'=>$enterCell, 'theta'=>round($theta, 4),
                'delta'=>round($minDelta, 4), 'cost_after'=>round($cc, 4),
            ];
        }

        return ['alloc'=>$alloc, 'iterations'=>$iterations, 'history'=>$history];
    }

    private static function findClosedLoop(array $enterCell, array $alloc, int $m, int $n): ?array
    {
        [$si, $sj] = $enterCell;
        $ib = array_fill(0, $m, array_fill(0, $n, false));
        for ($i = 0; $i < $m; $i++)
            for ($j = 0; $j < $n; $j++)
                if ($alloc[$i][$j] > 0) $ib[$i][$j] = true;

        $path = [[$si, $sj]];
        $vis = array_fill(0, $m, array_fill(0, $n, false));
        $vis[$si][$sj] = true;
        return self::dfsLoop($si, $sj, $si, $sj, $path, $vis, $ib, $m, $n);
    }

    private static function dfsLoop(int $ci, int $cj, int $ti, int $tj,
                                     array $path, array $vis, array $ib, int $m, int $n): ?array
    {
        foreach ([[0,1],[0,-1],[1,0],[-1,0]] as [$di, $dj]) {
            $ni = $ci + $di; $nj = $cj + $dj;
            if ($ni < 0 || $ni >= $m || $nj < 0 || $nj >= $n) continue;
            if ($ni === $ti && $nj === $tj && count($path) >= 4) return $path;
            if ($vis[$ni][$nj]) continue;
            if (!$ib[$ni][$nj]) continue;
            $vis[$ni][$nj] = true;
            $path[] = [$ni, $nj];
            $r = self::dfsLoop($ni, $nj, $ti, $tj, $path, $vis, $ib, $m, $n);
            if ($r !== null) return $r;
            array_pop($path);
            $vis[$ni][$nj] = false;
        }
        return null;
    }

    private static function fixDegeneracy(array $alloc, int $m, int $n, int $req): array
    {
        $cur = 0;
        for ($i = 0; $i < $m; $i++)
            for ($j = 0; $j < $n; $j++)
                if ($alloc[$i][$j] > 0) $cur++;

        $eps = 0.0001; $need = $req - $cur;
        for ($i = 0; $i < $m && $need > 0; $i++)
            for ($j = 0; $j < $n && $need > 0; $j++)
                if ($alloc[$i][$j] == 0) {
                    $rh = false; $ch = false;
                    for ($k = 0; $k < $n; $k++) if ($alloc[$i][$k] > 0) $rh = true;
                    for ($k = 0; $k < $m; $k++) if ($alloc[$k][$j] > 0) $ch = true;
                    if ($rh && $ch) { $alloc[$i][$j] = $eps; $need--; }
                }
        return $alloc;
    }

    private static function calcCost(array $alloc, array $cost): float
    {
        $total = 0;
        $m = count($alloc); $n = ($m > 0) ? count($alloc[0]) : 0;
        for ($i = 0; $i < $m; $i++)
            for ($j = 0; $j < $n; $j++)
                if ($alloc[$i][$j] > 0) {
                    $c = ($cost[$i][$j] >= self::BIG_M) ? 0 : $cost[$i][$j];
                    $total += $alloc[$i][$j] * $c;
                }
        return $total;
    }

    private static function feedback(float $opt, float $init, int $iter, bool $prohib): string
    {
        $f = [];
        if ($init > 0) {
            $imp = round((1 - $opt / $init) * 100, 2);
            $f[] = ($imp > 0) ? "📉 بهبود {$imp}% نسبت به جواب اولیه." : "✅ جواب اولیه از ابتدا بهینه بود.";
        }
        if ($iter > 0) $f[] = "🔄 تعداد تکرار MODI: {$iter}";
        if ($prohib) $f[] = "⚠️ از مسیرهای ممنوعه استفاده شده است.";
        return implode(' ', $f);
    }
}