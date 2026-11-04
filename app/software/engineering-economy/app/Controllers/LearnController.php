<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Controllers;

use App\Core\Controller;
use App\Software\EngineeringEconomy\Services\EeMath;

/**
 * مرکز آموزش اقتصاد مهندسی: درس‌ها، واژه‌نامه و ماشین‌حساب‌های تعاملی.
 */
final class LearnController extends Controller
{
    /** @var array<string,mixed>|null */
    private ?array $curriculum = null;

    public function index(): void
    {
        $curriculum = $this->curriculum();
        $levelFilter = (string) ($_GET['level'] ?? 'all');
        if (!array_key_exists($levelFilter, $curriculum['levels'])) $levelFilter = 'all';

        $lessons = $curriculum['lessons'];
        if ($levelFilter !== 'all') {
            $lessons = array_values(array_filter($lessons, static fn(array $lesson): bool => $lesson['level'] === $levelFilter));
        }

        $this->renderSoftware('learn/index', [
            'pageTitle' => 'مرکز آموزش اقتصاد مهندسی',
            'title' => 'آموزش اقتصاد مهندسی - IT4IE',
            'softwareName' => 'Engineering Economy Analyzer',
            'lessons' => $lessons,
            'levels' => $curriculum['levels'],
            'levelFilter' => $levelFilter,
            'totalLessons' => count($curriculum['lessons']),
            'glossaryCount' => count($curriculum['glossary']),
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function show(): void
    {
        $curriculum = $this->curriculum();
        $lessonId = (string) ($_GET['lesson'] ?? '');
        $lesson = null;

        foreach ($curriculum['lessons'] as $candidate) {
            if ($candidate['id'] === $lessonId) {
                $lesson = $candidate;
                break;
            }
        }

        if ($lesson === null) {
            http_response_code(404);
            ee_flash_set('danger', 'درس درخواستی پیدا نشد.');
            $this->redirect(ee_url('learn', 'index'));
        }

        $ids = array_column($curriculum['lessons'], 'id');
        $position = array_search($lessonId, $ids, true);
        $previous = $position !== false && $position > 0 ? $curriculum['lessons'][$position - 1] : null;
        $next = $position !== false && $position < count($ids) - 1 ? $curriculum['lessons'][$position + 1] : null;

        $this->renderSoftware('learn/show', [
            'pageTitle' => $lesson['title'],
            'title' => $lesson['title'] . ' - آموزش اقتصاد مهندسی',
            'softwareName' => 'Engineering Economy Analyzer',
            'lesson' => $lesson,
            'previous' => $previous,
            'next' => $next,
            'levelLabel' => $curriculum['levels'][$lesson['level']] ?? $lesson['level'],
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function glossary(): void
    {
        $curriculum = $this->curriculum();

        $this->renderSoftware('learn/glossary', [
            'pageTitle' => 'واژه‌نامهٔ اقتصاد مهندسی',
            'title' => 'واژه‌نامه - آموزش اقتصاد مهندسی',
            'softwareName' => 'Engineering Economy Analyzer',
            'terms' => $curriculum['glossary'],
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function tools(): void
    {
        $result = null;
        $values = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $tool = (string) ($_POST['tool'] ?? '');
                [$result, $values] = $this->runTool($tool);
            } catch (\InvalidArgumentException $e) {
                $result = ['tool' => (string) ($_POST['tool'] ?? ''), 'error' => $e->getMessage()];
                $values = $_POST;
            } catch (\Throwable $e) {
                error_log('Engineering Economy learn tool failed: ' . $e->getMessage());
                $result = ['tool' => (string) ($_POST['tool'] ?? ''), 'error' => 'محاسبه انجام نشد؛ ورودی‌ها را بازبینی کنید.'];
                $values = $_POST;
            }
        }

        $this->renderSoftware('learn/tools', [
            'pageTitle' => 'ماشین‌حساب‌های آموزشی',
            'title' => 'ماشین‌حساب‌ها - آموزش اقتصاد مهندسی',
            'softwareName' => 'Engineering Economy Analyzer',
            'result' => $result,
            'values' => $values,
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    /* ---------------------------------------------------------------
     * ماشین‌حساب‌ها
     * --------------------------------------------------------------- */

    /**
     * @return array{0:array<string,mixed>,1:array<string,mixed>}
     */
    private function runTool(string $tool): array
    {
        return match ($tool) {
            'factors' => $this->toolFactors(),
            'npv' => $this->toolNpv(),
            'depreciation' => $this->toolDepreciation(),
            'inflation' => $this->toolInflation(),
            'switching' => $this->toolSwitching(),
            default => throw new \InvalidArgumentException('ماشین‌حساب درخواستی شناخته نشد.'),
        };
    }

    private function toolFactors(): array
    {
        $rate = $this->percent('rate', 'نرخ بهره');
        $periods = $this->integer('periods', 1, EeMath::MAX_PERIODS, 'تعداد دوره');
        $values = ['rate' => $rate * 100, 'periods' => $periods];

        $rows = [];
        $factors = [
            ['(P/F)', 'ارزش فعلی یک مبلغ آینده', EeMath::factorPF($rate, $periods)],
            ['(F/P)', 'ارزش آتی یک مبلغ امروز', EeMath::factorFP($rate, $periods)],
            ['(P/A)', 'ارزش فعلی رشتهٔ یکنواخت', EeMath::factorPA($rate, $periods)],
            ['(A/P)', 'قسط یکنواخت از ارزش فعلی', EeMath::factorAP($rate, $periods)],
            ['(F/A)', 'ارزش آتی رشتهٔ یکنواخت', EeMath::factorFA($rate, $periods)],
            ['(A/F)', 'قسط صندوق استهلاک', EeMath::factorAF($rate, $periods)],
            ['(P/G)', 'ارزش فعلی گرادیان حسابی', EeMath::factorPG($rate, $periods)],
            ['(A/G)', 'معادل سالانهٔ گرادیان', EeMath::factorAG($rate, $periods)],
        ];

        foreach ($factors as [$symbol, $label, $value]) {
            $rows[] = ['symbol' => $symbol, 'label' => $label, 'value' => ee_num($value, 6)];
        }

        return [[
            'tool' => 'factors',
            'title' => 'فاکتورهای ارزش زمانی پول',
            'columns' => ['فاکتور', 'معنا', 'مقدار'],
            'rows' => array_map(static fn(array $row): array => [$row['symbol'], $row['label'], $row['value']], $rows),
            'note' => 'نرخ ' . ee_num($rate * 100, 3) . '٪ و ' . $periods . ' دوره.',
        ], $values];
    }

    private function toolNpv(): array
    {
        $rate = $this->percent('rate', 'نرخ تنزیل');
        $flows = $this->flows('flows');

        $issues = EeMath::validate($flows);
        $rows = [];
        foreach ($flows as $year => $flow) {
            $rows[] = [
                (string) $year,
                ee_money($flow),
                ee_money($flow * EeMath::factorPF($rate, (int) $year)),
                ee_money($this->cumulative($flows, (int) $year)),
            ];
        }

        $roots = EeMath::irrRoots($flows);

        return [[
            'tool' => 'npv',
            'title' => 'تحلیل جریان نقدی',
            'columns' => ['سال', 'جریان خالص', 'ارزش فعلی', 'تجمعی'],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'NPV', 'value' => ee_money(EeMath::npv($rate, $flows))],
                ['label' => 'IRR', 'value' => $roots === [] ? '—' : ee_pct($roots[0])],
                ['label' => 'تعداد ریشهٔ IRR', 'value' => (string) count($roots)],
                ['label' => 'ERR (سرمایه‌گذاری مجدد با همان نرخ)', 'value' => ee_pct(EeMath::err($flows, $rate))],
                ['label' => 'MIRR', 'value' => ee_pct(EeMath::mirr($flows, $rate))],
                ['label' => 'بازگشت ساده', 'value' => EeMath::paybackSimple($flows) === null ? '—' : ee_num(EeMath::paybackSimple($flows), 2) . ' سال'],
                ['label' => 'شاخص سودآوری', 'value' => ee_ratio(EeMath::profitabilityIndex($flows, $rate))],
            ],
            'note' => $issues === [] ? 'جریان نقدی متعارف است؛ IRR یکتاست.' : implode(' ', $issues),
        ], ['rate' => $rate * 100, 'flows' => implode("\n", $flows)]];
    }

    private function toolDepreciation(): array
    {
        $cost = $this->amount('cost', 'بهای دارایی');
        $salvage = $this->amount('salvage', 'ارزش اسقاط');
        $life = $this->integer('life', 1, EeMath::MAX_PERIODS, 'عمر دارایی');
        $method = (string) ($_POST['method'] ?? 'straight');
        $rate = $this->percent('declining_rate', 'نرخ نزولی', true);

        if ($salvage > $cost) throw new \InvalidArgumentException('ارزش اسقاط نمی‌تواند از بهای دارایی بیشتر باشد.');

        $schedule = EeMath::depreciationSchedule($method, $cost, $salvage, $life, $rate);

        $rows = [];
        foreach ($schedule as $row) {
            $rows[] = [(string) $row['year'], ee_money($row['depreciation']), ee_money($row['bookValue'])];
        }

        $total = array_sum(array_column($schedule, 'depreciation'));

        return [[
            'tool' => 'depreciation',
            'title' => 'جدول استهلاک',
            'columns' => ['سال', 'استهلاک', 'ارزش دفتری'],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'جمع استهلاک', 'value' => ee_money($total)],
                ['label' => 'روش', 'value' => ee_method_label($method)],
            ],
        ], ['cost' => $cost, 'salvage' => $salvage, 'life' => $life, 'method' => $method, 'declining_rate' => $rate * 100]];
    }

    private function toolInflation(): array
    {
        $nominal = $this->percent('nominal', 'نرخ اسمی', true);
        $real = $this->percent('real', 'نرخ حقیقی', true);
        $inflation = $this->percent('inflation', 'تورم', true);
        $compoundings = $this->integer('compoundings', 1, 365, 'تعداد مرکب‌شدن در سال', true);

        $metrics = [];

        if ($inflation > -1.0) {
            if ($real > -1.0) {
                $metrics[] = ['label' => 'نرخ اسمی از حقیقی و تورم', 'value' => ee_pct(EeMath::marketRateFromReal($real, $inflation))];
            }
            if ($nominal > -1.0) {
                $metrics[] = ['label' => 'نرخ حقیقی از اسمی و تورم', 'value' => ee_pct(EeMath::realRateFromMarket($nominal, $inflation))];
            }
        }

        if ($nominal > -1.0 && $compoundings > 0) {
            $metrics[] = ['label' => 'نرخ مؤثر سالانه', 'value' => ee_pct(EeMath::effectiveAnnualRate($nominal, $compoundings))];
        }

        return [[
            'tool' => 'inflation',
            'title' => 'تبدیل نرخ‌ها',
            'columns' => [],
            'rows' => [],
            'metrics' => $metrics,
            'note' => 'رابطهٔ فیشر: (۱ + اسمی) = (۱ + حقیقی)(۱ + تورم).',
        ], [
            'nominal' => $nominal * 100,
            'real' => $real * 100,
            'inflation' => $inflation * 100,
            'compoundings' => $compoundings,
        ]];
    }

    private function toolSwitching(): array
    {
        $rate = $this->percent('rate', 'نرخ تنزیل');
        $flows = $this->flows('flows');
        $year = $this->integer('year', 0, max(0, (int) array_key_last($flows) ?: 0), 'سال مورد بررسی');

        $base = EeMath::npv($rate, $flows);
        $switching = EeMath::switchingValue($flows, $rate, $year);

        return [[
            'tool' => 'switching',
            'title' => 'ارزش تغییر یک سال',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'NPV فعلی', 'value' => ee_money($base)],
                ['label' => 'ارزش تغییر سال ' . $year, 'value' => ee_pct($switching)],
                ['label' => 'NPV با تغییر −۱۰٪', 'value' => ee_money(EeMath::npvWithShock($flows, $rate, $year, -0.10))],
                ['label' => 'NPV با تغییر +۱۰٪', 'value' => ee_money(EeMath::npvWithShock($flows, $rate, $year, 0.10))],
            ],
            'note' => $switching === null
                ? 'برای این سال جریان غیرصفر ثبت نشده است.'
                : 'اگر جریان سال ' . $year . ' حدود ' . ee_num(abs($switching) * 100, 1) . '٪ ' . ($switching < 0 ? 'کاهش' : 'افزایش') . ' یابد، NPV صفر می‌شود.',
        ], ['rate' => $rate * 100, 'flows' => implode("\n", $flows), 'year' => $year]];
    }

    /* ---------------------------------------------------------------
     * کمکی‌ها
     * --------------------------------------------------------------- */

    /** @return array<string,mixed> */
    private function curriculum(): array
    {
        if ($this->curriculum === null) {
            $this->curriculum = require MODULAR_APP_PATH . '/app/Content/Curriculum.php';
        }

        return $this->curriculum;
    }

    private function cumulative(array $flows, int $upTo): float
    {
        $sum = 0.0;
        foreach ($flows as $year => $flow) {
            if ((int) $year > $upTo) break;
            $sum += (float) $flow;
        }

        return $sum;
    }

    private function latin(string $value): string
    {
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => '.', '،' => '',
        ]);

        return str_replace([',', '٬', ' '], '', $value);
    }

    /** درصد از ورودی کاربر؛ خروجی کسری (0.12) */
    private function percent(string $field, string $label, bool $optional = false): float
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        if ($raw === '') {
            if ($optional) return 0.0;
            throw new \InvalidArgumentException($label . ' الزامی است.');
        }

        $normalized = $this->latin(str_replace('%', '', $raw));
        if ($normalized === '' || !is_numeric($normalized)) {
            throw new \InvalidArgumentException($label . ' باید عدد باشد.');
        }

        $value = (float) $normalized;
        if ($value < -99.999 || $value > 100) throw new \InvalidArgumentException($label . ' باید بین −۱۰۰ و ۱۰۰ درصد باشد.');

        return $value / 100.0;
    }

    private function amount(string $field, string $label): float
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        $normalized = $this->latin($raw);

        if ($normalized === '' || !is_numeric($normalized)) {
            throw new \InvalidArgumentException($label . ' باید عدد باشد.');
        }

        $value = (float) $normalized;
        if ($value < 0) throw new \InvalidArgumentException($label . ' نمی‌تواند منفی باشد.');

        return $value;
    }

    private function integer(string $field, int $min, int $max, string $label, bool $optional = false): int
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        if ($raw === '') {
            if ($optional) return min($max, max($min, 1));
            throw new \InvalidArgumentException($label . ' الزامی است.');
        }

        $normalized = $this->latin($raw);
        if (!preg_match('/^-?\d+$/', $normalized)) {
            throw new \InvalidArgumentException($label . ' باید عدد صحیح باشد.');
        }

        $value = (int) $normalized;
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException($label . ' باید بین ' . $min . ' و ' . $max . ' باشد.');
        }

        return $value;
    }

    /**
     * خواندن رشتهٔ جریان نقدی: هر عدد در یک خط یا جدا‌شده با کاما/سمی‌کولون.
     *
     * @return array<int,float>
     */
    private function flows(string $field): array
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        if ($raw === '') throw new \InvalidArgumentException('جریان نقدی را وارد کنید (هر عدد در یک خط).');

        $parts = preg_split('/[\n\r,;]+/', $raw) ?: [];
        $flows = [];
        $year = 0;

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $value = $this->latin($part);
            $negative = false;
            if (str_starts_with($value, '(') && str_ends_with($value, ')')) {
                $negative = true;
                $value = trim($value, '()');
            }

            if ($value === '' || !is_numeric($value)) {
                throw new \InvalidArgumentException('مقدار «' . $part . '» عدد معتبر نیست.');
            }

            $flows[$year] = ($negative ? -1 : 1) * (float) $value;
            $year++;
            if ($year > EeMath::MAX_PERIODS) {
                throw new \InvalidArgumentException('حداکثر ' . EeMath::MAX_PERIODS . ' دوره قابل محاسبه است.');
            }
        }

        if ($flows === []) throw new \InvalidArgumentException('جریان نقدی خالی است.');
        if (EeMath::validate($flows) !== [] && count($flows) < 2) {
            throw new \InvalidArgumentException('دست‌کم دو دوره لازم است.');
        }

        return $flows;
    }
}
