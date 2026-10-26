<?php
namespace App\Software\Inventory\Services;

/** Transparent continuous-review inventory calculations. */
final class InventoryPolicyService
{
    private const SERVICE_Z = [
        '90' => 1.2815515655,
        '95' => 1.6448536270,
        '97.5' => 1.9599639845,
        '99' => 2.3263478740,
        '99.5' => 2.5758293035,
        '99.9' => 3.0902323062,
    ];

    public static function serviceLevels(): array
    {
        return ['90' => '90٪', '95' => '95٪', '97.5' => '97.5٪', '99' => '99٪', '99.5' => '99.5٪', '99.9' => '99.9٪'];
    }

    public static function analyze(array $item): array
    {
        $demand = max(0.0, (float) $item['annual_demand']);
        $dailyAverage = $demand / 365.0;
        $dailyStd = max(0.0, (float) $item['daily_demand_std']);
        $leadDays = max(0.0, (float) $item['lead_time_days']);
        $service = (string) $item['service_level'];
        $z = self::SERVICE_Z[$service] ?? self::SERVICE_Z['95'];
        $safety = $z * $dailyStd * sqrt($leadDays);
        $reorderPoint = ($dailyAverage * $leadDays) + $safety;
        $position = (float) $item['on_hand'] + (float) $item['on_order'];
        $unitCost = max(0.0, (float) $item['unit_cost']);
        $orderCost = max(0.0, (float) $item['order_cost']);
        $holdingRate = max(0.0, (float) $item['holding_rate']);
        $annualHoldingPerUnit = $unitCost * $holdingRate;
        $eoq = ($demand > 0 && $orderCost > 0 && $annualHoldingPerUnit > 0)
            ? sqrt((2.0 * $demand * $orderCost) / $annualHoldingPerUnit)
            : null;
        $gap = max(0.0, $reorderPoint - $position);
        $shouldOrder = $position <= $reorderPoint && $demand > 0;

        return [
            'average_daily_demand' => $dailyAverage,
            'z_value' => $z,
            'safety_stock' => $safety,
            'reorder_point' => $reorderPoint,
            'inventory_position' => $position,
            'eoq' => $eoq,
            'reorder_gap' => $gap,
            'should_order' => $shouldOrder,
            'suggested_order' => $shouldOrder ? max($gap, $eoq ?? 0.0) : 0.0,
            'annual_holding_cost_per_unit' => $annualHoldingPerUnit,
            'annual_usage_value' => $demand * $unitCost,
        ];
    }

    /** ABC by annual usage value; thresholds are configurable policy defaults, not laws. */
    public static function classifyAbc(array $items): array
    {
        foreach ($items as &$item) $item['_analysis'] = self::analyze($item);
        unset($item);
        usort($items, static fn($a, $b) => $b['_analysis']['annual_usage_value'] <=> $a['_analysis']['annual_usage_value']);
        $total = array_sum(array_map(static fn($item) => $item['_analysis']['annual_usage_value'], $items));
        if ($total <= 0) {
            foreach ($items as &$item) {
                $item['_analysis']['annual_usage_share'] = 0.0;
                $item['_analysis']['abc_class'] = 'C';
            }
            unset($item);
            return $items;
        }
        $cumulative = 0.0;
        foreach ($items as &$item) {
            $value = $item['_analysis']['annual_usage_value'];
            $before = $total > 0 ? $cumulative / $total : 0.0;
            $cumulative += $value;
            $item['_analysis']['annual_usage_share'] = $total > 0 ? $value / $total : 0.0;
            $item['_analysis']['abc_class'] = $before < 0.80 ? 'A' : ($before < 0.95 ? 'B' : 'C');
        }
        unset($item);
        return $items;
    }
}
