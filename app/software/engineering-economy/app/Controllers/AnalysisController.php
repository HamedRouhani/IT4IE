<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Controllers;

use App\Core\Controller;
use App\Software\EngineeringEconomy\Models\AlternativeRepository;
use App\Software\EngineeringEconomy\Models\AnalysisRepository;
use App\Software\EngineeringEconomy\Models\ProjectRepository;
use App\Software\EngineeringEconomy\Services\EeMath;

/**
 * تحلیل، مقایسه، حساسیت، جایگزینی و گزارش.
 */
final class AnalysisController extends Controller
{
    public function index(int $projectId): void
    {
        $userId = $this->userId();
        $analysis = $this->build($userId, $projectId);

        $this->renderSoftware('analysis/index', [
            'pageTitle' => 'تحلیل اقتصادی',
            'title' => 'تحلیل - ' . $analysis['project']['title'],
            'softwareName' => 'Engineering Economy Analyzer',
            'analysis' => $analysis,
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function report(int $projectId): void
    {
        $userId = $this->userId();
        $analysis = $this->build($userId, $projectId);

        $this->renderSoftware('analysis/report', [
            'pageTitle' => 'گزارش اقتصادی',
            'title' => 'گزارش - ' . $analysis['project']['title'],
            'softwareName' => 'Engineering Economy Analyzer',
            'analysis' => $analysis,
            'flash' => null,
        ], 'engineering-economy');
    }

    public function saveSnapshot(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(ee_url('analysis', 'index', ['id' => $projectId]));
        }

        try {
            $analysis = $this->build($userId, $projectId);
            $payload = [
                'rates' => [
                    'marr' => $analysis['rate'],
                    'inflation' => $analysis['inflation'],
                    'tax' => $analysis['tax'],
                ],
                'alternatives' => array_map(static function (array $row): array {
                    return [
                        'name' => $row['alternative']['name'],
                        'npv' => $row['npv'],
                        'irr' => $row['irr'],
                        'err' => $row['err'],
                        'mirr' => $row['mirr'],
                        'payback' => $row['payback'],
                        'discountedPayback' => $row['discountedPayback'],
                        'pi' => $row['pi'],
                        'aw' => $row['aw'],
                        'bcRatio' => $row['bc']['ratio'] ?? null,
                    ];
                }, $analysis['rows']),
                'ranking' => array_map(static fn(array $row): string => $row['alternative']['name'], $analysis['ranking']),
                'recommendation' => $analysis['recommendation'],
            ];

            (new AnalysisRepository())->save($projectId, null, 'full', $payload, 'ثبت دستی از صفحهٔ تحلیل');
            ee_flash_set('success', 'نسخهٔ تحلیل ثبت شد؛ گزارش قبلی با همین اعداد قابل بازبینی می‌ماند.');
        } catch (\Throwable $e) {
            error_log('Engineering Economy snapshot failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ثبت نسخهٔ تحلیل انجام نشد.');
        }

        $this->redirect(ee_url('analysis', 'index', ['id' => $projectId]));
    }

    public function export(int $projectId): void
    {
        $userId = $this->userId();
        $analysis = $this->build($userId, $projectId);
        $kind = (string) ($_GET['kind'] ?? 'metrics');
        $project = $analysis['project'];
        $filename = 'ee-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($project['project_no'] ?: $projectId)) . '-' . $kind . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            $this->redirect(ee_url('analysis', 'index', ['id' => $projectId]));
        }

        // BOM برای نمایش درست فارسی در Excel
        fwrite($out, "\xEF\xBB\xBF");

        if ($kind === 'flows') {
            fputcsv($out, ['alternative', 'year', 'inflow', 'outflow', 'net', 'label']);
            foreach ($analysis['rows'] as $row) {
                foreach ($row['rawRows'] as $raw) {
                    fputcsv($out, [
                        $this->csvCell($row['alternative']['name']),
                        (int) $raw['year_no'],
                        (float) $raw['inflow'],
                        (float) $raw['outflow'],
                        (float) $raw['inflow'] - (float) $raw['outflow'],
                        $this->csvCell((string) ($raw['label'] ?? '')),
                    ]);
                }
            }
        } else {
            fputcsv($out, ['alternative', 'npv', 'irr', 'err', 'mirr', 'payback_years', 'discounted_payback_years', 'pi', 'annual_worth', 'bc_ratio', 'after_tax_npv', 'after_tax_irr']);
            foreach ($analysis['rows'] as $row) {
                fputcsv($out, [
                    $this->csvCell($row['alternative']['name']),
                    $row['npv'],
                    $row['irr'],
                    $row['err'],
                    $row['mirr'],
                    $row['payback'],
                    $row['discountedPayback'],
                    $row['pi'],
                    $row['aw'],
                    $row['bc']['ratio'] ?? null,
                    $row['afterTax']['npv'] ?? null,
                    $row['afterTax']['irr'] ?? null,
                ]);
            }
            fputcsv($out, []);
            fputcsv($out, ['project', $this->csvCell($project['title'])]);
            fputcsv($out, ['currency', $project['currency']]);
            fputcsv($out, ['marr_pct', (float) $project['marr_pct']]);
            fputcsv($out, ['inflation_pct', (float) $project['inflation_pct']]);
            fputcsv($out, ['tax_rate_pct', (float) $project['tax_rate_pct']]);
            fputcsv($out, ['study_period', (int) $project['study_period']]);
            fputcsv($out, ['generated_at', date('Y-m-d H:i:s')]);
        }

        fclose($out);
        exit;
    }

    /* ---------------------------------------------------------------
     * محاسبهٔ مشترک
     * --------------------------------------------------------------- */

    /**
     * @return array<string,mixed>
     */
    private function build(int $userId, int $projectId): array
    {
        $project = (new ProjectRepository())->find($userId, $projectId);
        if (!$project) {
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        $rate = ((float) $project['marr_pct']) / 100.0;
        $inflation = ((float) $project['inflation_pct']) / 100.0;
        $tax = ((float) $project['tax_rate_pct']) / 100.0;
        $flowsByAlternative = (new AlternativeRepository())->flowsByProject($projectId);
        $studyPeriod = (int) $project['study_period'];

        $rows = [];
        foreach ($project['alternatives'] as $alternative) {
            $rawRows = $flowsByAlternative[(int) $alternative['id']] ?? [];
            $flows = EeMath::netFlows($rawRows);
            $periods = $flows === [] ? 0 : max(1, (int) array_key_last($flows));

            $benefits = [];
            $costs = [];
            foreach ($rawRows as $raw) {
                $year = (int) $raw['year_no'];
                $benefits[$year] = (float) $raw['inflow'];
                $costs[$year] = (float) $raw['outflow'];
            }

            // ---- تحلیل پس از مالیات (فقط وقتی نرخ مالیات و روش استهلاک تعیین شده باشد) ----
            $afterTax = null;
            if ($tax > 0.0 && $project['depreciation_method'] !== 'none' && $flows !== []) {
                $initialInvestment = -(float) ($flows[0] ?? 0.0);
                $depreciationLife = min($studyPeriod, max(1, (int) $alternative['life_years']));
                $schedule = EeMath::depreciationSchedule(
                    (string) $project['depreciation_method'],
                    max(0.0, $initialInvestment),
                    0.0,
                    $depreciationLife
                );
                $afterTaxFlows = EeMath::afterTaxNetFlows($flows, EeMath::depreciationByYear($schedule), $tax);

                $afterTax = [
                    'flows' => $afterTaxFlows,
                    'schedule' => $schedule,
                    'life' => $depreciationLife,
                    'npv' => EeMath::npv($rate, $afterTaxFlows),
                    'irr' => EeMath::irr($afterTaxFlows),
                    'payback' => EeMath::paybackSimple($afterTaxFlows),
                ];
            }

            $rows[] = [
                'alternative' => $alternative,
                'afterTax' => $afterTax,
                'rawRows' => $rawRows,
                'flows' => $flows,
                'issues' => $flows === [] ? ['جریان نقدی ثبت نشده است.'] : EeMath::validate($flows),
                'npv' => $flows === [] ? null : EeMath::npv($rate, $flows),
                'fw' => $flows === [] ? null : EeMath::futureWorth($rate, $flows, $periods),
                'aw' => $flows === [] ? null : EeMath::annualWorth($rate, $flows, $periods),
                'irr' => $flows === [] ? null : EeMath::irr($flows),
                'irrRoots' => $flows === [] ? [] : EeMath::irrRoots($flows),
                'err' => $flows === [] ? null : EeMath::err($flows, $rate),
                'mirr' => $flows === [] ? null : EeMath::mirr($flows, $rate),
                'payback' => $flows === [] ? null : EeMath::paybackSimple($flows),
                'discountedPayback' => $flows === [] ? null : EeMath::paybackDiscounted($flows, $rate),
                'pi' => $flows === [] ? null : EeMath::profitabilityIndex($flows, $rate),
                'bc' => $flows === [] ? ['ratio' => null, 'net' => 0.0, 'presentBenefits' => 0.0, 'presentCosts' => 0.0] : EeMath::bcRatio($rate, $benefits, $costs),
                'signChanges' => $flows === [] ? 0 : EeMath::signChanges($flows),
            ];
        }

        $ranking = array_values(array_filter($rows, static fn(array $row): bool => $row['npv'] !== null));
        usort($ranking, static function (array $left, array $right): int {
            return ($right['npv'] ?? -INF) <=> ($left['npv'] ?? -INF);
        });

        // ΔIRR هر رتبه نسبت به رتبهٔ قبل (محاسبه در کنترلر تا ویو به سرویس وابسته نباشد).
        foreach ($ranking as $index => &$rankRow) {
            if ($index === 0) {
                $rankRow['deltaIrr'] = null;
                $rankRow['deltaDecision'] = 'بهترین NPV';
                continue;
            }

            $previous = $ranking[$index - 1];
            $comparison = EeMath::incremental($rankRow['flows'], $previous['flows'], $rate);
            $rankRow['deltaIrr'] = $comparison['incrementalIrr'];
            $rankRow['deltaDecision'] = ($comparison['incrementalIrr'] !== null && $comparison['incrementalIrr'] >= $rate)
                ? 'جایگزینی توجیه دارد (ΔIRR ≥ MARR)'
                : 'جایگزینی توجیه ندارد (ΔIRR < MARR)';
        }
        unset($rankRow);

        $incremental = $this->incrementalPairs($rows, $rate);
        $sensitivity = $this->sensitivity($rows, $rate);
        $replacement = $this->replacement($rows, $rate, $studyPeriod);
        $snapshots = (new AnalysisRepository())->listForProject($projectId);

        $recommendation = null;
        if ($ranking !== []) {
            $best = $ranking[0];
            $alternative = $best['alternative'];
            if ($best['npv'] > 0) {
                $recommendation = 'بر پایهٔ NPV، گزینهٔ «' . $alternative['name'] . '» با NPV مثبت و رتبهٔ اول پیشنهاد می‌شود.';
            } else {
                $recommendation = 'هیچ گزینه‌ای NPV مثبت ندارد؛ بر پایهٔ MARR فعلی هیچ‌کدام از نظر اقتصادی توجیه‌پذیر نیست.';
            }
            if (count($ranking) > 1 && $incremental !== []) {
                $last = $incremental[count($incremental) - 1];
                if ($last['decision'] !== null) $recommendation .= ' ' . $last['decision'];
            }
        }

        return [
            'project' => $project,
            'rate' => $rate,
            'inflation' => $inflation,
            'tax' => $tax,
            'studyPeriod' => $studyPeriod,
            'rows' => $rows,
            'ranking' => $ranking,
            'incremental' => $incremental,
            'sensitivity' => $sensitivity,
            'replacement' => $replacement,
            'snapshots' => $snapshots,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * تحلیل افزایشی: گزینه‌ها به ترتیب سرمایهٔ اولیه (خروجی سال ۰) و مقایسهٔ هر جفت.
     *
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,array<string,mixed>>
     */
    private function incrementalPairs(array $rows, float $rate): array
    {
        $eligible = [];
        foreach ($rows as $row) {
            if ($row['flows'] === []) continue;
            $initial = (float) ($row['flows'][0] ?? 0.0);
            if ($initial >= 0.0) continue;
            $eligible[] = $row;
        }

        if (count($eligible) < 2) return [];

        usort($eligible, static function (array $left, array $right): int {
            return ((float) ($left['flows'][0] ?? 0.0)) <=> ((float) ($right['flows'][0] ?? 0.0));
        });

        $pairs = [];
        for ($index = 1, $count = count($eligible); $index < $count; $index++) {
            $lower = $eligible[$index - 1];
            $higher = $eligible[$index];
            $result = EeMath::incremental($higher['flows'], $lower['flows'], $rate);
            $decision = null;

            if ($result['incrementalIrr'] !== null) {
                $decision = $result['incrementalIrr'] >= $rate
                    ? 'سرمایه‌گذاری اضافی برای رسیدن به «' . $higher['alternative']['name'] . '» توجیه دارد (ΔIRR ≥ MARR).'
                    : 'سرمایه‌گذاری اضافی برای رسیدن به «' . $higher['alternative']['name'] . '» توجیه ندارد (ΔIRR < MARR)؛ گزینهٔ کم‌سرمایه‌تر ترجیح دارد.';
            }

            $pairs[] = [
                'lower' => $lower['alternative'],
                'higher' => $higher['alternative'],
                'flows' => $result['flows'],
                'deltaIrr' => $result['incrementalIrr'],
                'deltaNpv' => $result['npv'],
                'decision' => $decision,
            ];
        }

        return $pairs;
    }

    /**
     * حساسیت: نرخ (MARR) و تغییر ±۱۰٪ هر سال + ارزش تغییر.
     *
     * @param array<int,array<string,mixed>> $rows
     * @return array{rates:array<int,float>,rateMatrix:array<int,array<string,mixed>>,years:array<int,array<string,mixed>>}
     */
    private function sensitivity(array $rows, float $rate): array
    {
        $step = 0.02;
        $rates = [];
        for ($index = -3; $index <= 3; $index++) {
            $candidate = $rate + ($index * $step);
            if ($candidate > -0.9999) $rates[] = $candidate;
        }

        $rateMatrix = [];
        foreach ($rates as $candidate) {
            $cells = [];
            foreach ($rows as $row) {
                $cells[] = [
                    'name' => $row['alternative']['name'],
                    'npv' => $row['flows'] === [] ? null : EeMath::npv($candidate, $row['flows']),
                ];
            }
            $rateMatrix[] = ['rate' => $candidate, 'cells' => $cells];
        }

        $years = [];
        $maxYear = 0;
        foreach ($rows as $row) {
            if ($row['flows'] === []) continue;
            $maxYear = max($maxYear, (int) array_key_last($row['flows']));
        }

        for ($year = 0; $year <= $maxYear; $year++) {
            $cells = [];
            $hasValue = false;
            foreach ($rows as $row) {
                if (!array_key_exists($year, $row['flows']) || abs((float) $row['flows'][$year]) < 0.0001) {
                    $cells[] = ['name' => $row['alternative']['name'], 'down' => null, 'up' => null, 'switching' => null];
                    continue;
                }
                $hasValue = true;
                $cells[] = [
                    'name' => $row['alternative']['name'],
                    'down' => EeMath::npvWithShock($row['flows'], $rate, $year, -0.10),
                    'up' => EeMath::npvWithShock($row['flows'], $rate, $year, 0.10),
                    'switching' => EeMath::switchingValue($row['flows'], $rate, $year),
                ];
            }
            if ($hasValue) $years[] = ['year' => $year, 'cells' => $cells];
        }

        return ['rates' => $rates, 'rateMatrix' => $rateMatrix, 'years' => $years];
    }

    /**
     * جایگزینی: EUAC سال‌به‌سال و عمر اقتصادی.
     *
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,array<string,mixed>>
     */
    private function replacement(array $rows, float $rate, int $studyPeriod): array
    {
        $results = [];

        foreach ($rows as $row) {
            if ($row['flows'] === []) continue;
            $firstCost = -(float) ($row['flows'][0] ?? 0.0);
            if ($firstCost <= 0.0) continue;

            $annualCosts = [];
            $salvageByYear = [];
            $limit = min($studyPeriod, max((int) $row['alternative']['life_years'], 1));
            for ($year = 1; $year <= $limit; $year++) {
                $annualCosts[$year] = -(float) ($row['flows'][$year] ?? 0.0);
                $salvageByYear[$year] = 0.0;
            }

            $euacRows = EeMath::euacByYear($firstCost, $annualCosts, $salvageByYear, $rate);
            $life = EeMath::economicLife($euacRows);
            $minimum = null;
            foreach ($euacRows as $candidate) {
                if ($life !== null && $candidate['year'] === $life) $minimum = $candidate['euac'];
            }

            $results[] = [
                'alternative' => $row['alternative'],
                'firstCost' => $firstCost,
                'rows' => $euacRows,
                'life' => $life,
                'minEuac' => $minimum,
                'declaredLife' => (int) $row['alternative']['life_years'],
            ];
        }

        return $results;
    }

    /** جلوگیری از تزریق فرمول در CSV */
    private function csvCell(string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    private function userId(): int
    {
        $this->requireAuth();

        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
