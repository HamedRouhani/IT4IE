<?php
namespace App\Software\Quality\Services;

use App\Software\Quality\Models\ParetoAnalysis;
use App\Software\Quality\Models\ParetoItem;

class ParetoService
{
    /**
     * آستانه‌ی Vital Few (طبق اصل Pareto: 80%)
     */
    const VITAL_FEW_THRESHOLD = 80.0;

    protected $analysisModel;
    protected $itemModel;

    public function __construct()
    {
        $this->analysisModel = new ParetoAnalysis();
        $this->itemModel     = new ParetoItem();
    }

    /**
     * ═══════════════════════════════════════════════════════
     * محاسبه‌ی Pareto از داده‌ی خام
     * ═══════════════════════════════════════════════════════
     * 
     * ورودی:
     *   $rawItems = [
     *       ['category_name' => 'ترک', 'value' => 45, 'frequency' => 45, 'notes' => '...'],
     *       ['category_name' => 'سوراخ', 'value' => 30],
     *       ['category_name' => 'رنگ‌پریدگی', 'value' => 15],
     *   ]
     * 
     * خروجی:
     *   [
     *       'items'              => [...], // آیتم‌های محاسبه‌شده
     *       'total_value'        => 90,
     *       'vital_few_count'    => 2,
     *       'vital_few_percent'  => 83.33,
     *       'trivial_many_count' => 1,
     *   ]
     */
    public function calculate(array $rawItems, $threshold = self::VITAL_FEW_THRESHOLD)
    {
        // ۱. پاک‌سازی و اعتبارسنجی
        $items = $this->sanitizeItems($rawItems);

        if (empty($items)) {
            return [
                'items'              => [],
                'total_value'        => 0,
                'vital_few_count'    => 0,
                'vital_few_percent'  => 0,
                'trivial_many_count' => 0,
            ];
        }

        // ۲. مرتب‌سازی نزولی بر اساس value
        usort($items, function ($a, $b) {
            if ($a['value'] == $b['value']) {
                return strcmp($a['category_name'], $b['category_name']);
            }
            return $b['value'] <=> $a['value'];
        });

        // ۳. محاسبه‌ی مجموع
        $total = array_sum(array_column($items, 'value'));

        if ($total <= 0) {
            return [
                'items'              => $items,
                'total_value'        => 0,
                'vital_few_count'    => 0,
                'vital_few_percent'  => 0,
                'trivial_many_count' => count($items),
            ];
        }

        // ۴. محاسبه‌ی percent و cumulative
        $cumulative = 0;
        $vitalFewCount = 0;
        $vitalFewPercent = 0;

        foreach ($items as $index => &$item) {
            $percent = ($item['value'] / $total) * 100;
            $cumulative += $percent;

            $item['percent']            = round($percent, 3);
            $item['cumulative_value']   = round($cumulative * $total / 100, 4);
            $item['cumulative_percent'] = round($cumulative, 3);
            $item['sort_order']         = $index + 1;

            // تشخیص Vital Few
            // قانون: آخرین آیتمی که cumulative_percent <= threshold
            // یعنی همه‌ی آیتم‌های قبل از عبور از آستانه
            if ($cumulative <= $threshold + 0.001) { // +0.001 برای خطای گرد کردن
                $item['is_vital_few'] = 1;
                $vitalFewCount++;
                $vitalFewPercent = round($cumulative, 2);
            } else {
                $item['is_vital_few'] = 0;
            }
        }
        unset($item);

        return [
            'items'              => $items,
            'total_value'        => round($total, 4),
            'vital_few_count'    => $vitalFewCount,
            'vital_few_percent'  => $vitalFewPercent,
            'trivial_many_count' => count($items) - $vitalFewCount,
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════
     * ساخت یک تحلیل Pareto جدید (ذخیره در DB)
     * ═══════════════════════════════════════════════════════
     */
    public function createAnalysis(array $data, array $rawItems)
    {
        // ۱. محاسبه
        $calculated = $this->calculate($rawItems, $data['threshold'] ?? self::VITAL_FEW_THRESHOLD);

        // ۲. ذخیره‌ی Analysis
        $this->analysisModel->beginTransaction();

        try {
            $analysisId = $this->analysisModel->create([
                'system_id'          => $data['system_id'],
                'project_id'         => $data['project_id'] ?? null,
                'title'              => $data['title'],
                'problem_statement'  => $data['problem_statement'] ?? null,
                'category_type'      => $data['category_type'] ?? null,
                'unit'               => $data['unit'] ?? null,
                'analysis_date'      => $data['analysis_date'] ?? date('Y-m-d'),
                'period_from'        => $data['period_from'] ?? null,
                'period_to'          => $data['period_to'] ?? null,
                'total_value'        => $calculated['total_value'],
                'vital_few_count'    => $calculated['vital_few_count'],
                'vital_few_percent'  => $calculated['vital_few_percent'],
                'status'             => $data['status'] ?? 'draft',
                'notes'              => $data['notes'] ?? null,
                'created_by'         => $data['created_by'] ?? null,
            ]);

            // ۳. ذخیره‌ی Items
            $this->itemModel->bulkInsert($analysisId, $calculated['items']);

            $this->analysisModel->commit();

            return [
                'success'     => true,
                'analysis_id' => $analysisId,
                'calculated'  => $calculated,
            ];

        } catch (\Exception $e) {
            $this->analysisModel->rollback();
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * ═══════════════════════════════════════════════════════
     * به‌روزرسانی یک تحلیل موجود (حذف و بازنویسی items)
     * ═══════════════════════════════════════════════════════
     */
    public function updateAnalysis($analysisId, array $data, array $rawItems)
    {
        $analysis = $this->analysisModel->find($analysisId);
        if (!$analysis) {
            return ['success' => false, 'error' => 'تحلیل یافت نشد'];
        }

        // ۱. محاسبه‌ی مجدد
        $calculated = $this->calculate($rawItems, $data['threshold'] ?? self::VITAL_FEW_THRESHOLD);

        $this->analysisModel->beginTransaction();

        try {
            // ۲. به‌روزرسانی Analysis
            $this->analysisModel->update($analysisId, [
                'title'              => $data['title'] ?? $analysis['title'],
                'problem_statement'  => $data['problem_statement'] ?? $analysis['problem_statement'],
                'category_type'      => $data['category_type'] ?? $analysis['category_type'],
                'unit'               => $data['unit'] ?? $analysis['unit'],
                'analysis_date'      => $data['analysis_date'] ?? $analysis['analysis_date'],
                'period_from'        => $data['period_from'] ?? $analysis['period_from'],
                'period_to'          => $data['period_to'] ?? $analysis['period_to'],
                'total_value'        => $calculated['total_value'],
                'vital_few_count'    => $calculated['vital_few_count'],
                'vital_few_percent'  => $calculated['vital_few_percent'],
                'status'             => $data['status'] ?? $analysis['status'],
                'notes'              => $data['notes'] ?? $analysis['notes'],
            ]);

            // ۳. حذف آیتم‌های قبلی
            $this->itemModel->deleteByAnalysis($analysisId);

            // ۴. درج آیتم‌های جدید
            $this->itemModel->bulkInsert($analysisId, $calculated['items']);

            $this->analysisModel->commit();

            return [
                'success'    => true,
                'calculated' => $calculated,
            ];

        } catch (\Exception $e) {
            $this->analysisModel->rollback();
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * ═══════════════════════════════════════════════════════
     * دریافت داده‌ی کامل یک تحلیل (برای نمایش)
     * ═══════════════════════════════════════════════════════
     */
    public function getAnalysisWithItems($analysisId)
    {
        $analysis = $this->analysisModel->find($analysisId);
        if (!$analysis) {
            return null;
        }

        $items = $this->itemModel->findByAnalysis($analysisId);

        // آماده‌سازی داده برای Chart.js
        $chart = $this->prepareChartData($items);

        return [
            'analysis' => $analysis,
            'items'    => $items,
            'chart'    => $chart,
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════
     * آماده‌سازی داده برای نمودار Chart.js
     * ═══════════════════════════════════════════════════════
     * 
     * خروجی:
     *   [
     *       'labels'      => ['ترک', 'سوراخ', 'رنگ‌پریدگی'],
     *       'values'      => [45, 30, 15],
     *       'cumulative'  => [50.0, 83.33, 100.0],
     *       'colors'      => ['#dc2626', '#dc2626', '#6b7280'],
     *       'vital_count' => 2,
     *   ]
     */
    public function prepareChartData(array $items)
    {
        $labels     = [];
        $values     = [];
        $cumulative = [];
        $colors     = [];

        foreach ($items as $item) {
            $labels[]     = $item['category_name'];
            $values[]     = (float)$item['value'];
            $cumulative[] = (float)$item['cumulative_percent'];

            // رنگ: قرمز برای Vital Few، خاکستری برای Trivial Many
            $colors[] = $item['is_vital_few'] ? '#dc2626' : '#9ca3af';
        }

        return [
            'labels'      => $labels,
            'values'      => $values,
            'cumulative'  => $cumulative,
            'colors'      => $colors,
            'vital_count' => count(array_filter($items, fn($i) => $i['is_vital_few'])),
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════
     * محاسبه‌ی پارامترهای مورد نیاز در فرم (JS)
     * ═══════════════════════════════════════════════════════
     * این متد برای استفاده در فرم با AJAX خوبه (پیش‌نمایش زنده)
     */
    public function previewCalculation(array $rawItems, $threshold = self::VITAL_FEW_THRESHOLD)
    {
        $result = $this->calculate($rawItems, $threshold);
        return [
            'items'              => $result['items'],
            'total_value'        => $result['total_value'],
            'vital_few_count'    => $result['vital_few_count'],
            'vital_few_percent'  => $result['vital_few_percent'],
            'trivial_many_count' => $result['trivial_many_count'],
            'chart'              => $this->prepareChartData($result['items']),
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════
     * پاک‌سازی و اعتبارسنجی آیتم‌های خام
     * ═══════════════════════════════════════════════════════
     */
    protected function sanitizeItems(array $rawItems)
    {
        $cleaned = [];
        $seenNames = [];

        foreach ($rawItems as $item) {
            // category_name اجباری
            $name = trim($item['category_name'] ?? '');
            if ($name === '') {
                continue;
            }

            // value باید عددی و >= 0 باشه
            $value = isset($item['value']) ? (float)$item['value'] : 0;
            if ($value < 0) {
                $value = 0;
            }

            // حذف تکراری‌ها (ادغام value)
            if (isset($seenNames[$name])) {
                $cleaned[$seenNames[$name]]['value'] += $value;
                continue;
            }

            $seenNames[$name] = count($cleaned);

            $cleaned[] = [
                'category_name' => $name,
                'value'         => $value,
                'frequency'     => isset($item['frequency']) ? (int)$item['frequency'] : null,
                'cost_per_unit' => isset($item['cost_per_unit']) ? (float)$item['cost_per_unit'] : null,
                'notes'         => $item['notes'] ?? null,
            ];
        }

        return $cleaned;
    }

    /**
     * ═══════════════════════════════════════════════════════
     * محاسبه‌ی مقدار بر اساس frequency × cost_per_unit
     * ═══════════════════════════════════════════════════════
     * در بعضی تحلیل‌ها value = frequency × cost_per_unit هست
     * (مثلاً هزینه‌ی کل یک نوع نقص = تعداد × هزینه‌ی واحد)
     */
    public function calculateValueFromFrequency(array $item)
    {
        if (isset($item['frequency']) && isset($item['cost_per_unit'])) {
            return (float)$item['frequency'] * (float)$item['cost_per_unit'];
        }
        return (float)($item['value'] ?? 0);
    }

    /**
     * ═══════════════════════════════════════════════════════
     * پیشنهاد CAPA بر اساس Vital Few
     * ═══════════════════════════════════════════════════════
     * برای هر Vital Few یک CAPA پیشنهادی می‌سازه
     * (فقط پیشنهاد، ذخیره نمی‌کنه — User باید تأیید کنه)
     */
    public function suggestCapaActions($analysisId)
    {
        $data = $this->getAnalysisWithItems($analysisId);
        if (!$data) {
            return [];
        }

        $suggestions = [];
        foreach ($data['items'] as $item) {
            if (!$item['is_vital_few']) {
                continue;
            }

            $suggestions[] = [
                'title'                 => "اقدام اصلاحی برای «{$item['category_name']}»",
                'problem_description'   => "دسته «{$item['category_name']}» یکی از Vital Few‌های تحلیل «{$data['analysis']['title']}» است " .
                                          "با {$item['value']} {$data['analysis']['unit']} " .
                                          "({$item['percent']}% از کل، تجمعی {$item['cumulative_percent']}%).",
                'source_type'           => 'pareto',
                'source_id'             => $analysisId,
                'action_type'           => 'corrective',
                'priority'              => $item['percent'] >= 30 ? 'critical' : 'high',
                'before_value'          => $item['value'],
            ];
        }

        return $suggestions;
    }
}