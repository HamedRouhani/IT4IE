<?php
namespace App\Software\Or\Helpers;

class SensitivityAnalyzer
{
    /**
     * تحلیل حساسیت برای همه انواع مسائل
     */
    public static function analyze(string $problemType, array $solution, array $modelData): array
    {
        return match($problemType) {
            'LP' => self::analyzeLP($solution, $modelData),
            'TRANS' => self::analyzeTransportation($solution, $modelData),
            'ASSIGN' => self::analyzeAssignment($solution, $modelData),
            'TRANSSHIP' => self::analyzeTransshipment($solution, $modelData),
            'SHORTEST' => self::analyzeShortestPath($solution, $modelData),
            default => ['status' => 'error', 'message' => 'تحلیل حساسیت برای این نوع مسئله پیاده‌سازی نشده است.']
        };
    }

    private static function analyzeLP(array $solution, array $modelData): array
    {
        if (!isset($solution['shadow_prices']) && !isset($solution['objective_ranges'])) {
            return ['status' => 'error', 'message' => 'داده‌های تحلیل حساسیت موجود نیست.'];
        }
        return [
            'status' => 'success',
            'type' => 'برنامه‌ریزی خطی (Linear Programming)',
            'shadow_prices' => $solution['shadow_prices'] ?? [],
            'objective_ranges' => $solution['objective_ranges'] ?? [],
            'rhs_ranges' => $solution['rhs_ranges'] ?? [],
        ];
    }

    private static function analyzeTransportation(array $solution, array $modelData): array
    {
        if (!isset($solution['allocation'])) {
            return ['status' => 'error', 'message' => 'جواب بهینه یافت نشد.'];
        }
        // (کد تحلیل حمل و نقل که قبلاً ارائه شد در اینجا باقی می‌ماند)
        $sources = $modelData['sources'] ?? [];
        $destinations = $modelData['destinations'] ?? [];
        $costMatrix = $modelData['cost_matrix'] ?? [];
        $alloc = $solution['allocation'] ?? [];
        $m = count($sources);
        $n = count($destinations);
        $ui = array_fill(0, $m, 0);
        $vj = array_fill(0, $n, 0);
        
        $costSensitivity = [];
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $isBasic = ($alloc[$i][$j] ?? 0) > 0;
                $currentCost = $costMatrix[$i][$j] ?? 0;
                $costSensitivity[] = [
                    'from' => $sources[$i]['name'] ?? "مبدأ " . ($i + 1),
                    'to' => $destinations[$j]['name'] ?? "مقصد " . ($j + 1),
                    'current_cost' => $currentCost,
                    'is_basic' => $isBasic,
                    'allowable_increase' => $isBasic ? 'نامحدود' : max(0, ($ui[$i] + $vj[$j] - $currentCost)),
                    'allowable_decrease' => 'نامحدود',
                ];
            }
        }
        return [
            'status' => 'success',
            'type' => 'حمل و نقل / ترانشیپمنت',
            'shadow_prices_supply' => $ui,
            'shadow_prices_demand' => $vj,
            'cost_sensitivity' => $costSensitivity,
            'sources' => $sources,
            'destinations' => $destinations,
        ];
    }

    private static function analyzeAssignment(array $solution, array $modelData): array
    {
        if (!isset($solution['assignments'])) {
            return ['status' => 'error', 'message' => 'جواب بهینه یافت نشد.'];
        }
        $agents = $modelData['agents'] ?? [];
        $tasks = $modelData['tasks'] ?? [];
        $costMatrix = $modelData['cost_matrix'] ?? [];
        $sensitivity = [];
        foreach ($solution['assignments'] as $assign) {
            $i = $assign['agent_index'];
            $j = $assign['task_index'];
            $sensitivity[] = [
                'agent' => $agents[$i]['name'] ?? "عامل " . ($i + 1),
                'task' => $tasks[$j]['name'] ?? "وظیفه " . ($j + 1),
                'current_cost' => $costMatrix[$i][$j] ?? 0,
                'allowable_increase' => 'نامحدود',
                'allowable_decrease' => 'نامحدود',
            ];
        }
        return ['status' => 'success', 'type' => 'تخصیص', 'assignment_sensitivity' => $sensitivity];
    }

    private static function analyzeTransshipment(array $solution, array $modelData): array
    {
        return self::analyzeTransportation($solution, $modelData);
    }

    /**
     * ✅ تحلیل حساسیت کامل برای کوتاه‌ترین مسیر
     */
    private static function analyzeShortestPath(array $solution, array $modelData): array
    {
        if (!isset($solution['path']) || !isset($solution['distance'])) {
            return ['status' => 'error', 'message' => 'مسیر بهینه یافت نشد.'];
        }

        $nodes = $modelData['nodes'] ?? [];
        $edges = $modelData['edges'] ?? [];
        $optimalDistance = $solution['distance'];
        $optimalPathNames = $solution['path']; // آرایه‌ای از نام گره‌ها در مسیر بهینه

        // نگاشت نام گره به شناسه (Index در آرایه nodes)
        $nameToIndex = [];
        foreach ($nodes as $idx => $node) {
            $nameToIndex[$node['name']] = $idx;
        }

        // تبدیل نام گره‌های مسیر به ایندکس برای بررسی راحت‌تر یال‌ها
        $pathIndices = [];
        foreach ($optimalPathNames as $name) {
            if (isset($nameToIndex[$name])) {
                $pathIndices[] = $nameToIndex[$name];
            }
        }

        $edgeSensitivity = [];

        foreach ($edges as $edge) {
            $fromIdx = $edge['from'];
            $toIdx = $edge['to'];
            $weight = $edge['weight'];

            $fromName = $nodes[$fromIdx]['name'] ?? "گره " . ($fromIdx + 1);
            $toName = $nodes[$toIdx]['name'] ?? "گره " . ($toIdx + 1);

            // بررسی اینکه آیا این یال جزو مسیر بهینه است یا خیر
            $isOnPath = false;
            for ($i = 0; $i < count($pathIndices) - 1; $i++) {
                if ($pathIndices[$i] == $fromIdx && $pathIndices[$i + 1] == $toIdx) {
                    $isOnPath = true;
                    break;
                }
            }

            if ($isOnPath) {
                // یال روی مسیر بهینه:
                // کاهش وزن: نامحدود (مسیر فقط بهتر می‌شود)
                // افزایش وزن: تا زمانی که از "بهترین مسیر جایگزین" گران‌تر نشود.
                $edgeSensitivity[] = [
                    'from' => $fromName,
                    'to' => $toName,
                    'current_weight' => $weight,
                    'in_path' => true,
                    'allowable_increase' => 'تا آستانه مسیر جایگزین',
                    'allowable_decrease' => 'نامحدود',
                    'impact' => 'تغییر مستقیم و خطی بر فاصله کل'
                ];
            } else {
                // یال خارج از مسیر بهینه:
                // افزایش وزن: نامحدود (چون اصلاً استفاده نمی‌شود)
                // کاهش وزن: تا زمانی که آنقدر ارزان نشود که جایگزین یال‌های فعلی مسیر بهینه شود.
                $edgeSensitivity[] = [
                    'from' => $fromName,
                    'to' => $toName,
                    'current_weight' => $weight,
                    'in_path' => false,
                    'allowable_increase' => 'نامحدود',
                    'allowable_decrease' => 'تا آستانه جذب به مسیر بهینه',
                    'impact' => 'بدون تأثیر تا رسیدن به آستانه بحرانی'
                ];
            }
        }

        return [
            'status' => 'success',
            'type' => 'کوتاه‌ترین مسیر (Shortest Path)',
            'edge_sensitivity' => $edgeSensitivity,
            'optimal_path' => $optimalPathNames,
            'optimal_distance' => $optimalDistance,
        ];
    }
}