<?php

declare(strict_types=1);

namespace App\Software\TimeStudyAnalyzer\Controllers;

/**
 * مرکز آموزش Time Study: درس‌ها، واژه‌نامه و ماشین‌حساب‌های تعاملی.
 *
 * همهٔ فرمول‌ها با محاسبات ماژول هم‌خوان است؛ ابزارها فقط آموزشی‌اند و
 * دادهٔ مطالعه ذخیره نمی‌کنند.
 */
final class LearnController extends StudyControllerBase
{
    /** مقادیر بحرانی دوطرفهٔ توزیع t برای df ۱ تا ۳۰ */
    private const T = [
        '90' => [1 => 6.314, 2 => 2.920, 3 => 2.353, 4 => 2.132, 5 => 2.015, 6 => 1.943, 7 => 1.895, 8 => 1.860, 9 => 1.833, 10 => 1.812,
                 11 => 1.796, 12 => 1.782, 13 => 1.771, 14 => 1.761, 15 => 1.753, 16 => 1.746, 17 => 1.740, 18 => 1.734, 19 => 1.729, 20 => 1.725,
                 21 => 1.721, 22 => 1.717, 23 => 1.714, 24 => 1.711, 25 => 1.708, 26 => 1.706, 27 => 1.703, 28 => 1.701, 29 => 1.699, 30 => 1.697],
        '95' => [1 => 12.706, 2 => 4.303, 3 => 3.182, 4 => 2.776, 5 => 2.571, 6 => 2.447, 7 => 2.365, 8 => 2.306, 9 => 2.262, 10 => 2.228,
                 11 => 2.201, 12 => 2.179, 13 => 2.160, 14 => 2.145, 15 => 2.131, 16 => 2.120, 17 => 2.110, 18 => 2.101, 19 => 2.093, 20 => 2.086,
                 21 => 2.080, 22 => 2.074, 23 => 2.069, 24 => 2.064, 25 => 2.060, 26 => 2.056, 27 => 2.052, 28 => 2.048, 29 => 2.045, 30 => 2.042],
        '99' => [1 => 63.657, 2 => 9.925, 3 => 5.841, 4 => 4.604, 5 => 4.032, 6 => 3.707, 7 => 3.499, 8 => 3.355, 9 => 3.250, 10 => 3.169,
                 11 => 3.106, 12 => 3.055, 13 => 3.012, 14 => 2.977, 15 => 2.947, 16 => 2.921, 17 => 2.898, 18 => 2.878, 19 => 2.861, 20 => 2.845,
                 21 => 2.831, 22 => 2.819, 23 => 2.807, 24 => 2.797, 25 => 2.787, 26 => 2.779, 27 => 2.771, 28 => 2.763, 29 => 2.756, 30 => 2.750],
    ];

    private const Z = ['90' => 1.6449, '95' => 1.9600, '99' => 2.5758];

    /** ضرایب سیستم رتبه‌بندی وستینگهاوس (درصد افزودنی) */
    private const WESTINGHOUSE = [
        'skill' => ['A1' => 15, 'A2' => 13, 'B1' => 11, 'B2' => 8, 'C1' => 6, 'C2' => 3, 'D' => 0, 'E1' => -5, 'E2' => -10, 'F1' => -16, 'F2' => -22],
        'effort' => ['A1' => 13, 'A2' => 12, 'B1' => 10, 'B2' => 8, 'C1' => 5, 'C2' => 2, 'D' => 0, 'E1' => -4, 'E2' => -8, 'F1' => -12, 'F2' => -17],
        'conditions' => ['A' => 6, 'B' => 4, 'C' => 2, 'D' => 0, 'E' => -3, 'F' => -7],
        'consistency' => ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0, 'E' => -2, 'F' => -4],
    ];

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

        $this->renderPage('learn/index', [
            'pageTitle' => 'مرکز آموزش مطالعهٔ کار',
            'lessons' => $lessons,
            'levels' => $curriculum['levels'],
            'levelFilter' => $levelFilter,
            'totalLessons' => count($curriculum['lessons']),
            'glossaryCount' => count($curriculum['glossary']),
        ]);
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
            ts_flash_set('danger', 'درس درخواستی پیدا نشد.');
            $this->redirect(ts_url('learn', 'index'));
        }

        $ids = array_column($curriculum['lessons'], 'id');
        $position = array_search($lessonId, $ids, true);
        $previous = $position !== false && $position > 0 ? $curriculum['lessons'][$position - 1] : null;
        $next = $position !== false && $position < count($ids) - 1 ? $curriculum['lessons'][$position + 1] : null;

        $this->renderPage('learn/show', [
            'pageTitle' => $lesson['title'],
            'lesson' => $lesson,
            'previous' => $previous,
            'next' => $next,
            'levelLabel' => $curriculum['levels'][$lesson['level']] ?? $lesson['level'],
        ]);
    }

    public function glossary(): void
    {
        $curriculum = $this->curriculum();

        $this->renderPage('learn/glossary', [
            'pageTitle' => 'واژه‌نامهٔ مطالعهٔ کار',
            'terms' => $curriculum['glossary'],
        ]);
    }

    public function tools(): void
    {
        $result = null;
        $values = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->postOnly(); // بررسی توکن CSRF و روش درخواست

            try {
                [$result, $values] = $this->runTool((string) ($_POST['tool'] ?? ''));
            } catch (\InvalidArgumentException $e) {
                $result = ['tool' => (string) ($_POST['tool'] ?? ''), 'error' => $e->getMessage()];
                $values = $_POST;
            } catch (\Throwable $e) {
                error_log('Time Study learn tool failed: ' . $e->getMessage());
                $result = ['tool' => (string) ($_POST['tool'] ?? ''), 'error' => 'محاسبه انجام نشد؛ ورودی‌ها را بازبینی کنید.'];
                $values = $_POST;
            }
        }

        $this->renderPage('learn/tools', [
            'pageTitle' => 'ماشین‌حساب‌های آموزشی',
            'result' => $result,
            'values' => $values,
        ]);
    }

    /* ---------------------------------------------------------------
     * ابزارها
     * --------------------------------------------------------------- */

    /** @return array{0:array<string,mixed>,1:array<string,mixed>} */
    private function runTool(string $tool): array
    {
        return match ($tool) {
            'sample-size' => $this->toolSampleSize(),
            'standard-time' => $this->toolStandardTime(),
            'rating' => $this->toolRating(),
            'confidence' => $this->toolConfidence(),
            'sampling' => $this->toolSampling(),
            default => throw new \InvalidArgumentException('ماشین‌حساب درخواستی شناخته نشد.'),
        };
    }

    private function toolSampleSize(): array
    {
        $mean = $this->decimal((string) ($_POST['mean'] ?? ''), 'میانگین زمان', 0.0001, 36000, 4);
        $std = $this->decimal((string) ($_POST['std'] ?? ''), 'انحراف معیار', 0, 36000, 4);
        $precision = $this->decimal((string) ($_POST['precision'] ?? '5'), 'دقت هدف', 0.1, 50, 2) / 100;
        $confidence = $this->confidenceLevel();

        $required = $this->requiredSampleSize($confidence, $std, $mean, $precision);
        $cv = $mean > 0 ? 100 * $std / $mean : null;

        return [[
            'tool' => 'sample-size',
            'title' => 'تعداد چرخهٔ لازم',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'تعداد چرخهٔ لازم', 'value' => (string) $required],
                ['label' => 'ضریب تغییرات (CV)', 'value' => $cv === null ? '—' : ts_num($cv, 1) . '٪'],
                ['label' => 'دقت نسبی هدف', 'value' => ts_num($precision * 100, 1) . '٪'],
                ['label' => 'سطح اطمینان', 'value' => ts_num((float) $confidence, 0) . '٪'],
            ],
            'note' => $cv !== null && $cv >= 25
                ? 'پراکندگی بالاست (CV ≥ ۲۵٪)؛ پیش از افزایش تعداد چرخه، مرز عناصر و پایداری روش را بازبینی کنید.'
                : 'این عدد با مقدار بحرانی t و حل تکراری محاسبه شده است؛ نمونهٔ کمتر از آن، دقت هدف را تضمین نمی‌کند.',
        ], [
            'mean' => $mean,
            'std' => $std,
            'precision' => $precision * 100,
            'confidence' => $confidence,
        ]];
    }

    private function toolStandardTime(): array
    {
        $observed = $this->decimal((string) ($_POST['observed'] ?? ''), 'زمان مشاهده‌شده', 0.0001, 36000, 4);
        $rating = $this->decimal((string) ($_POST['rating'] ?? '100'), 'ارزیابی عملکرد', 50, 150, 2);
        $frequency = $this->decimal((string) ($_POST['frequency'] ?? '1'), 'تکرار در هر واحد', 0.0001, 10000, 4);
        $machine = $this->decimal((string) ($_POST['machine'] ?? '0'), 'زمان ماشین', 0, 36000, 4);
        $basis = (string) ($_POST['basis'] ?? 'standard');

        $allowances = [];
        foreach (['personal' => 'نیاز شخصی', 'fatigue' => 'خستگی', 'delay' => 'تأخیر', 'other' => 'سایر'] as $key => $label) {
            $allowances[$key] = $this->decimal((string) ($_POST[$key] ?? '0'), 'allowance ' . $label, 0, 79.99, 2);
        }

        $totalAllowance = array_sum($allowances);
        if ($totalAllowance >= 80) throw new \InvalidArgumentException('مجموع allowance باید کمتر از ۸۰٪ باشد.');
        if (!in_array($basis, ['standard', 'normal'], true)) throw new \InvalidArgumentException('مبنای allowance معتبر نیست.');

        $normalPerUnit = $observed * ($rating / 100) * $frequency;
        $allowanceFraction = $totalAllowance / 100;
        $manualStandard = $basis === 'standard'
            ? $normalPerUnit / (1 - $allowanceFraction)
            : $normalPerUnit * (1 + $allowanceFraction);
        $standardCycle = $manualStandard + $machine;
        $capacity = $standardCycle > 0 ? 3600 / $standardCycle : null;

        return [[
            'tool' => 'standard-time',
            'title' => 'زمان استاندارد',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'زمان نرمال (هر واحد)', 'value' => ts_num($normalPerUnit, 3) . ' ثانیه'],
                ['label' => 'allowance کل', 'value' => ts_num($totalAllowance, 2) . '٪'],
                ['label' => 'زمان استاندارد دستی', 'value' => ts_num($manualStandard, 3) . ' ثانیه'],
                ['label' => 'زمان استاندارد چرخه (با ماشین)', 'value' => ts_num($standardCycle, 3) . ' ثانیه'],
                ['label' => 'ظرفیت نظری ساعتی', 'value' => $capacity === null ? '—' : ts_num($capacity, 2) . ' واحد'],
                ['label' => 'مبنای allowance', 'value' => $basis === 'standard' ? 'درصدی از زمان استاندارد' : 'افزوده بر زمان نرمال'],
            ],
            'note' => 'allowance فقط بر زمان دستی اعمال شد؛ زمان ماشین جداگانه اضافه شده است. ظرفیت نظری، توقف‌ها و افت کیفیت را لحاظ نمی‌کند.',
        ], [
            'observed' => $observed,
            'rating' => $rating,
            'frequency' => $frequency,
            'machine' => $machine,
            'basis' => $basis,
            'personal' => $allowances['personal'],
            'fatigue' => $allowances['fatigue'],
            'delay' => $allowances['delay'],
            'other' => $allowances['other'],
        ]];
    }

    private function toolRating(): array
    {
        $selections = [];
        $total = 0;

        foreach (self::WESTINGHOUSE as $factor => $table) {
            $value = (string) ($_POST[$factor] ?? 'D');
            if (!array_key_exists($value, $table)) throw new \InvalidArgumentException('مقدار انتخاب‌شده برای ' . $factor . ' معتبر نیست.');
            $selections[$factor] = $value;
            $total += $table[$value];
        }

        $ratingPercent = 100 + $total;
        $observedRaw = trim((string) ($_POST['observed'] ?? ''));
        $normal = null;
        $observed = null;

        if ($observedRaw !== '') {
            $observed = $this->decimal($observedRaw, 'زمان مشاهده‌شده', 0.0001, 36000, 4);
            $normal = $observed * $ratingPercent / 100;
        }

        $metrics = [
            ['label' => 'مجموع مؤلفه‌ها', 'value' => ($total >= 0 ? '+' : '') . ts_num($total, 0) . '٪'],
            ['label' => 'نرخ ارزیابی عملکرد', 'value' => ts_num($ratingPercent, 0) . '٪'],
        ];

        if ($normal !== null) {
            $metrics[] = ['label' => 'زمان نرمال', 'value' => ts_num($normal, 3) . ' ثانیه'];
        }

        $labels = [
            'skill' => 'مهارت',
            'effort' => 'کوشش',
            'conditions' => 'شرایط',
            'consistency' => 'یکنواختی',
        ];

        $rows = [];
        foreach ($selections as $factor => $value) {
            $delta = self::WESTINGHOUSE[$factor][$value];
            $rows[] = [$labels[$factor], $value, ($delta >= 0 ? '+' : '') . ts_num($delta, 0) . '٪'];
        }

        return [[
            'tool' => 'rating',
            'title' => 'ارزیابی عملکرد با وستینگهاوس',
            'columns' => ['مؤلفه', 'سطح', 'اثر'],
            'rows' => $rows,
            'metrics' => $metrics,
            'note' => 'نرخ نهایی از جمع ۱۰۰٪ و چهار مؤلفه به‌دست می‌آید. ارزیابی باید با کار مرجع کالیبره و در گزارش مستند شود.',
        ], $selections + ['observed' => $observed ?? '']];
    }

    private function toolConfidence(): array
    {
        $mean = $this->decimal((string) ($_POST['mean'] ?? ''), 'میانگین', 0.0001, 36000, 4);
        $std = $this->decimal((string) ($_POST['std'] ?? ''), 'انحراف معیار', 0, 36000, 4);
        $count = $this->intInput('count', 2, 1000, 'تعداد چرخه');
        $confidence = $this->confidenceLevel();

        $critical = $this->criticalValue($confidence, $count - 1);
        $margin = $critical * $std / sqrt($count);

        return [[
            'tool' => 'confidence',
            'title' => 'بازهٔ اطمینان میانگین چرخه',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'میانگین', 'value' => ts_num($mean, 3) . ' ثانیه'],
                ['label' => 'حاشیهٔ خطا', 'value' => ts_num($margin, 3) . ' ثانیه'],
                ['label' => 'بازهٔ اطمینان', 'value' => ts_num(max(0, $mean - $margin), 3) . ' تا ' . ts_num($mean + $margin, 3) . ' ثانیه'],
                ['label' => 'دقت نسبی فعلی', 'value' => $mean > 0 ? ts_num(100 * $margin / $mean, 2) . '٪' : '—'],
                ['label' => 'مقدار بحرانی', 'value' => ($count - 1 <= 30 ? 't' : 'z') . ' = ' . ts_num($critical, 3)],
                ['label' => 'ضریب تغییرات', 'value' => $mean > 0 ? ts_num(100 * $std / $mean, 1) . '٪' : '—'],
            ],
            'note' => $count < 5 ? 'با کمتر از ۵ چرخه، برآورد پراکندگی ناپایدار است و بازهٔ اطمینان خوش‌بینانه می‌شود.' : 'برای نمونهٔ کوچک از t با درجهٔ آزادی n−۱ استفاده شده است.',
        ], ['mean' => $mean, 'std' => $std, 'count' => $count, 'confidence' => $confidence]];
    }

    private function toolSampling(): array
    {
        $total = $this->intInput('total', 1, 1000000, 'کل مشاهده‌های معتبر');
        $count = $this->intInput('count', 0, $total, 'مشاهده‌های دستهٔ مولد');
        $precision = $this->decimal((string) ($_POST['precision'] ?? '5'), 'دقت مطلق', 0.1, 30, 2) / 100;
        $confidence = $this->confidenceLevel();

        $z = (float) (self::Z[$confidence] ?? self::Z['95']);
        $p = $count / $total;
        $variance = $p * (1 - $p);
        $requiredWorst = (int) ceil(($z * $z * 0.25) / ($precision * $precision));
        $required = (int) ceil(($z * $z * $variance) / ($precision * $precision));
        $achieved = 100 * $z * sqrt($variance / $total);
        [$lower, $upper] = $this->wilson($count, $total, $z);

        return [[
            'tool' => 'sampling',
            'title' => 'برآورد نسبت و حجم نمونهٔ نمونه‌برداری',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'نسبت مشاهده‌شده (p̂)', 'value' => ts_num(100 * $p, 2) . '٪'],
                ['label' => 'بازهٔ اطمینان Wilson', 'value' => ts_num(100 * $lower, 2) . '٪ تا ' . ts_num(100 * $upper, 2) . '٪'],
                ['label' => 'دقت واقعی', 'value' => ts_num($achieved, 2) . ' واحد درصد'],
                ['label' => 'حجم نمونهٔ لازم (با p̂ فعلی)', 'value' => (string) $required],
                ['label' => 'حجم نمونهٔ بدترین حالت (p=0.5)', 'value' => (string) $requiredWorst],
                ['label' => 'کفایت نمونه', 'value' => $total >= $requiredWorst ? 'کافی (بدترین حالت)' : ($total >= $required ? 'کافی برای p̂ فعلی' : 'ناکافی')],
            ],
            'note' => 'نسبت‌ها فقط بر پایهٔ مشاهده‌های معتبر محاسبه می‌شوند؛ مشاهده‌های ازدست‌رفته و باطل باید جداگانه گزارش شوند.',
        ], ['total' => $total, 'count' => $count, 'precision' => $precision * 100, 'confidence' => $confidence]];
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

    private function confidenceLevel(): string
    {
        $value = self::latinDigits((string) ($_POST['confidence'] ?? '95'));
        if (!in_array($value, ['90', '95', '99'], true)) throw new \InvalidArgumentException('سطح اطمینان باید ۹۰، ۹۵ یا ۹۹ درصد باشد.');

        return $value;
    }

    private function intInput(string $field, int $min, int $max, string $label): int
    {
        $raw = str_replace([',', '٬', ' '], '', self::latinDigits(trim((string) ($_POST[$field] ?? ''))));
        if ($raw === '' || !preg_match('/^\d+$/D', $raw)) {
            throw new \InvalidArgumentException('«' . $label . '» باید عدد صحیح باشد.');
        }

        $value = (int) $raw;
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException('«' . $label . '» باید بین ' . $min . ' و ' . $max . ' باشد.');
        }

        return $value;
    }

    private function criticalValue(string $confidence, ?int $df): float
    {
        if ($df !== null && $df >= 1 && $df <= 30) {
            return (float) (self::T[$confidence][$df] ?? (self::Z[$confidence] ?? self::Z['95']));
        }

        return (float) (self::Z[$confidence] ?? self::Z['95']);
    }

    /**
     * کوچک‌ترین n که با مقدار t خودش شرط دقت را برآورده می‌کند.
     */
    private function requiredSampleSize(string $confidence, float $std, float $mean, float $precision): int
    {
        if ($mean <= 0 || $std <= 0 || $precision <= 0) return 1;

        $z = (float) (self::Z[$confidence] ?? self::Z['95']);
        $estimate = max(1, (int) ceil(($z * $std / ($precision * $mean)) ** 2));

        for ($candidate = $estimate; $candidate <= 10000; $candidate++) {
            $critical = $this->criticalValue($confidence, max(1, $candidate - 1));
            $needed = (int) ceil(($critical * $std / ($precision * $mean)) ** 2);
            if ($needed <= $candidate) return $candidate;
        }

        return 10000;
    }

    /** @return array{0:float,1:float} */
    private function wilson(int $successes, int $n, float $z): array
    {
        if ($n <= 0) return [0.0, 0.0];

        $p = $successes / $n;
        $z2 = $z * $z;
        $denominator = 1 + ($z2 / $n);
        $center = ($p + ($z2 / (2 * $n))) / $denominator;
        $radius = ($z / $denominator) * sqrt(($p * (1 - $p) / $n) + ($z2 / (4 * $n * $n)));

        return [max(0.0, $center - $radius), min(1.0, $center + $radius)];
    }
}
