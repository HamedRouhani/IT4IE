<?php
namespace App\Software\Or\Helpers;

class TransshipmentSolver
{
    /**
     * حل مسئله ترانشیپمنت با روش تبدیل به حمل و نقل
     * 
     * @param array $nodes آرایه گره‌ها (source, destination, transshipment)
     * @param array $edges آرایه یال‌ها (source_id, destination_id, cost)
     * @return array نتیجه حل
     */
    public static function solve(array $nodes, array $edges): array
    {
        if (empty($nodes) || empty($edges)) {
            return ['status' => 'error', 'message' => 'گره‌ها یا یال‌ها تعریف نشده‌اند.'];
        }

        // ۱. محاسبه عرضه/تقاضای خالص هر گره
        $nodeMap = [];
        foreach ($nodes as $node) {
            $nodeMap[$node['id']] = [
                'name' => $node['name'],
                'type' => $node['type'],
                'capacity' => (int)$node['capacity'],
                'net_supply' => 0,
            ];
            
            // محاسبه عرضه خالص
            if ($node['type'] === 'source') {
                $nodeMap[$node['id']]['net_supply'] = (int)$node['capacity'];
            } elseif ($node['type'] === 'destination') {
                $nodeMap[$node['id']]['net_supply'] = -(int)$node['capacity'];
            }
            // گره‌های ترانشیپمنت عرضه خالص صفر دارند
        }

        // ۲. ساخت ماتریس هزینه برای تبدیل به حمل و نقل
        $nodeIds = array_keys($nodeMap);
        $n = count($nodeIds);
        $nodeIndex = array_flip($nodeIds);
        
        $costMatrix = array_fill(0, $n, array_fill(0, $n, null));
        
        // پر کردن ماتریس هزینه از یال‌ها
        foreach ($edges as $edge) {
            $i = $nodeIndex[$edge['source_id']] ?? null;
            $j = $nodeIndex[$edge['destination_id']] ?? null;
            if ($i !== null && $j !== null) {
                $costMatrix[$i][$j] = $edge['is_prohibited'] ? null : (float)$edge['cost'];
            }
        }

        // ۳. محاسبه عرضه و تقاضا برای مسئله حمل و نقل
        $supply = [];
        $demand = [];
        
        foreach ($nodeIds as $idx => $nodeId) {
            $netSupply = $nodeMap[$nodeId]['net_supply'];
            $capacity = $nodeMap[$nodeId]['capacity'];
            
            // برای ترانشیپمنت: ظرفیت عبور = ظرفیت گره + مجموع عرضه/تقاضا
            if ($nodeMap[$nodeId]['type'] === 'transshipment') {
                $totalFlow = array_sum(array_column($nodeMap, 'net_supply'));
                $transshipCapacity = $capacity + abs($totalFlow);
                
                $supply[$idx] = $transshipCapacity;
                $demand[$idx] = $transshipCapacity;
            } elseif ($netSupply > 0) {
                $supply[$idx] = $netSupply;
                $demand[$idx] = 0;
            } else {
                $supply[$idx] = 0;
                $demand[$idx] = abs($netSupply);
            }
        }

        // ۴. متوازن‌سازی
        $totalSupply = array_sum($supply);
        $totalDemand = array_sum($demand);
        
        if ($totalSupply !== $totalDemand) {
            $diff = abs($totalSupply - $totalDemand);
            if ($totalSupply > $totalDemand) {
                $demand[] = $diff;
                $costMatrix[] = array_fill(0, $n, 0);
                foreach ($costMatrix as &$row) {
                    $row[] = 0;
                }
                $nodeMap['dummy'] = ['name' => 'مقصد مجازی', 'type' => 'dummy', 'capacity' => $diff];
            } else {
                $supply[] = $diff;
                $costMatrix[] = array_fill(0, $n + 1, 0);
                foreach ($costMatrix as &$row) {
                    $row[] = 0;
                }
                $nodeMap['dummy'] = ['name' => 'مبدأ مجازی', 'type' => 'dummy', 'capacity' => $diff];
            }
        }

        // ۵. حل با TransportationSolver
        $result = TransportationSolver::solve($costMatrix, $supply, $demand, 'VAM', true);
        
        if (($result['status'] ?? 'error') !== 'success') {
            return $result;
        }

        // ۶. تفسیر نتایج
        $allocations = [];
        $totalCost = 0;
        
        for ($i = 0; $i < count($nodeIds); $i++) {
            for ($j = 0; $j < count($nodeIds); $j++) {
                if ($result['allocation'][$i][$j] > 0) {
                    $fromNode = $nodeMap[$nodeIds[$i]] ?? null;
                    $toNode = $nodeMap[$nodeIds[$j]] ?? null;
                    
                    if ($fromNode && $toNode && $fromNode['type'] !== 'dummy' && $toNode['type'] !== 'dummy') {
                        $cost = $costMatrix[$i][$j] ?? 0;
                        $allocations[] = [
                            'from' => $fromNode['name'],
                            'to' => $toNode['name'],
                            'amount' => $result['allocation'][$i][$j],
                            'unit_cost' => $cost,
                            'total_cost' => $result['allocation'][$i][$j] * $cost,
                        ];
                        $totalCost += $result['allocation'][$i][$j] * $cost;
                    }
                }
            }
        }

        return [
            'status' => 'success',
            'method' => 'Transshipment (Converted to Transportation)',
            'allocations' => $allocations,
            'total_cost' => round($totalCost, 4),
            'iterations' => $result['iterations'] ?? 0,
            'smart_feedback' => "✅ مسئله ترانشیپمنت با موفقیت حل شد. هزینه کل: " . number_format($totalCost, 2),
        ];
    }
}