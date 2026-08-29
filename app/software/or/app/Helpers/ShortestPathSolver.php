<?php
namespace App\Software\Or\Helpers;

class ShortestPathSolver
{
    const INF = PHP_INT_MAX;

    /**
     * الگوریتم Dijkstra - کوتاه‌ترین مسیر از یک مبدأ به همه گره‌ها
     */
    public static function dijkstra(array $nodes, array $edges, int $sourceId): array
    {
        $n = count($nodes);
        $nodeMap = array_flip(array_column($nodes, 'id'));
        
        // ساخت ماتریس مجاورت
        $adj = array_fill(0, $n, array_fill(0, $n, self::INF));
        foreach ($edges as $edge) {
            $i = $nodeMap[$edge['source_id']] ?? null;
            $j = $nodeMap[$edge['destination_id']] ?? null;
            if ($i !== null && $j !== null) {
                $adj[$i][$j] = (float)$edge['cost'];
            }
        }

        $sourceIdx = $nodeMap[$sourceId] ?? 0;
        $dist = array_fill(0, $n, self::INF);
        $prev = array_fill(0, $n, null);
        $visited = array_fill(0, $n, false);
        $dist[$sourceIdx] = 0;

        for ($count = 0; $count < $n - 1; $count++) {
            // یافتن گره با کمترین فاصله
            $min = self::INF;
            $u = -1;
            for ($v = 0; $v < $n; $v++) {
                if (!$visited[$v] && $dist[$v] < $min) {
                    $min = $dist[$v];
                    $u = $v;
                }
            }

            if ($u === -1) break;
            $visited[$u] = true;

            // به‌روزرسانی فاصله همسایه‌ها
            for ($v = 0; $v < $n; $v++) {
                if (!$visited[$v] && $adj[$u][$v] !== self::INF) {
                    $newDist = $dist[$u] + $adj[$u][$v];
                    if ($newDist < $dist[$v]) {
                        $dist[$v] = $newDist;
                        $prev[$v] = $u;
                    }
                }
            }
        }

        // ساخت مسیرها
        $paths = [];
        for ($i = 0; $i < $n; $i++) {
            if ($i !== $sourceIdx && $dist[$i] !== self::INF) {
                $path = [];
                $current = $i;
                while ($current !== null) {
                    array_unshift($path, $nodes[$current]['name']);
                    $current = $prev[$current];
                }
                $paths[] = [
                    'destination' => $nodes[$i]['name'],
                    'distance' => round($dist[$i], 4),
                    'path' => $path,
                    'path_str' => implode(' → ', $path)
                ];
            }
        }

        return [
            'status' => 'success',
            'method' => 'Dijkstra',
            'source' => $nodes[$sourceIdx]['name'],
            'paths' => $paths,
            'total_paths' => count($paths)
        ];
    }

    /**
     * الگوریتم Floyd-Warshall - کوتاه‌ترین مسیر بین همه جفت گره‌ها
     */
    public static function floydWarshall(array $nodes, array $edges): array
    {
        $n = count($nodes);
        $nodeMap = array_flip(array_column($nodes, 'id'));
        
        // مقداردهی اولیه ماتریس فاصله
        $dist = array_fill(0, $n, array_fill(0, $n, self::INF));
        $next = array_fill(0, $n, array_fill(0, $n, null));
        
        for ($i = 0; $i < $n; $i++) {
            $dist[$i][$i] = 0;
        }
        
        foreach ($edges as $edge) {
            $i = $nodeMap[$edge['source_id']] ?? null;
            $j = $nodeMap[$edge['destination_id']] ?? null;
            if ($i !== null && $j !== null) {
                $dist[$i][$j] = (float)$edge['cost'];
                $next[$i][$j] = $j;
            }
        }

        // الگوریتم اصلی
        for ($k = 0; $k < $n; $k++) {
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    if ($dist[$i][$k] !== self::INF && $dist[$k][$j] !== self::INF) {
                        $newDist = $dist[$i][$k] + $dist[$k][$j];
                        if ($newDist < $dist[$i][$j]) {
                            $dist[$i][$j] = $newDist;
                            $next[$i][$j] = $next[$i][$k];
                        }
                    }
                }
            }
        }

        // ساخت نتایج
        $results = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i !== $j && $dist[$i][$j] !== self::INF) {
                    $path = self::buildPath($next, $nodes, $i, $j);
                    $results[] = [
                        'from' => $nodes[$i]['name'],
                        'to' => $nodes[$j]['name'],
                        'distance' => round($dist[$i][$j], 4),
                        'path' => $path
                    ];
                }
            }
        }

        return [
            'status' => 'success',
            'method' => 'Floyd-Warshall',
            'paths' => $results,
            'total_paths' => count($results)
        ];
    }

    private static function buildPath(array $next, array $nodes, int $i, int $j): array
    {
        if ($next[$i][$j] === null) return [];
        
        $path = [$nodes[$i]['name']];
        while ($i !== $j) {
            $i = $next[$i][$j];
            $path[] = $nodes[$i]['name'];
        }
        return $path;
    }
}