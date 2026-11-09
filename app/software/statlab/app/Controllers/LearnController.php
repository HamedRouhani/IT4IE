<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Helpers\DistributionLibrary as DL;
use App\Software\Statlab\Helpers\HypothesisTester as HT;
use App\Software\Statlab\Helpers\InputValidator as IV;
use App\Software\Statlab\Helpers\RegressionEngine as RE;
use App\Software\Statlab\Helpers\StatEngine as SE;

final class LearnController extends Controller
{
    private ?array $curriculum = null;

    public function index(): void
    {
        $this->requireAuth();
        $curriculum = $this->curriculum();
        $filter = (string) ($_GET['level'] ?? 'all');
        if (!in_array($filter, ['all', 'basic', 'middle', 'advanced'], true)) $filter = 'all';
        $lessons = $curriculum['lessons'];
        if ($filter !== 'all') $lessons = array_values(array_filter($lessons, static fn(array $lesson): bool => $lesson['level'] === $filter));
        $this->view('learn/index', [
            'pageTitle' => 'مرکز آموزش StatLab', 'lessons' => $lessons,
            'levels' => $curriculum['levels'], 'levelFilter' => $filter,
            'totalLessons' => count($curriculum['lessons']), 'glossaryCount' => count($curriculum['glossary']),
        ]);
    }

    public function show(): void
    {
        $this->requireAuth();
        $curriculum = $this->curriculum();
        $id = (string) ($_GET['lesson'] ?? '');
        $ids = array_column($curriculum['lessons'], 'id');
        $position = array_search($id, $ids, true);
        if ($position === false) {
            http_response_code(404);
            $this->redirect('controller=learn');
        }
        $this->view('learn/show', [
            'pageTitle' => $curriculum['lessons'][$position]['title'], 'lesson' => $curriculum['lessons'][$position],
            'previous' => $position > 0 ? $curriculum['lessons'][$position - 1] : null,
            'next' => $position < count($ids) - 1 ? $curriculum['lessons'][$position + 1] : null,
            'levelLabel' => $curriculum['levels'][$curriculum['lessons'][$position]['level']],
        ]);
    }

    public function glossary(): void
    {
        $this->requireAuth();
        $this->view('learn/glossary', ['pageTitle' => 'واژه‌نامه StatLab', 'terms' => $this->curriculum()['glossary']]);
    }

    public function tools(): void
    {
        $this->requireAuth();
        $result = null;
        $values = $_POST;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                [$result, $values] = $this->runTool((string) ($_POST['tool'] ?? ''));
            } catch (\InvalidArgumentException $e) {
                $result = ['error' => $e->getMessage()];
            } catch (\Throwable $e) {
                error_log('StatLab learning calculator failed: ' . $e->getMessage());
                $result = ['error' => 'محاسبه انجام نشد؛ ورودی‌ها و فرض‌های روش را بررسی کنید.'];
            }
        }
        $this->view('learn/tools', ['pageTitle' => 'محاسبه‌گرهای آموزشی StatLab', 'result' => $result, 'values' => $values]);
    }

    private function runTool(string $tool): array
    {
        $values = $_POST;
        $alpha = in_array($tool, ['welch', 'regression'], true)
            ? IV::alpha($this->number('alpha', 'سطح معنی‌داری'))
            : 0.05;
        switch ($tool) {
            case 'descriptive':
                $x = IV::parseNumbers((string) ($_POST['data'] ?? ''));
                $d = SE::describe($x);
                $q = $d['quartiles'];
                $metrics = [
                    ['حجم نمونه', $d['count']], ['میانگین', $d['mean']], ['میانه', $d['median']],
                    ['انحراف معیار نمونه (n−1)', $d['std']], ['واریانس نمونه (n−1)', $d['variance']],
                    ['کمینه / بیشینه', $d['min'] . ' / ' . $d['max']], ['دامنه', $d['range']],
                    ['Q1 / Q3', $q['q1'] . ' / ' . $q['q3']], ['IQR', $q['q3'] - $q['q1']],
                    ['تعداد نقاط علامت‌گذاری‌شده با 1.5×IQR', count($d['outliers'])],
                ];
                return [['title' => 'خلاصه توصیفی', 'metrics' => $metrics, 'note' => 'چارک‌ها با درون‌یابی خطی موقعیت (n−1)p محاسبه شده‌اند. نقاط علامت‌گذاری‌شده لزوماً خطا نیستند.'], $values];
            case 'normal':
                $mu = $this->number('mu', 'میانگین');
                $sigma = $this->number('sigma', 'انحراف معیار');
                if ($sigma <= 0) throw new \InvalidArgumentException('انحراف معیار باید بزرگ‌تر از صفر باشد.');
                $x1 = $this->number('x1', 'کران اول');
                $x2 = $this->number('x2', 'کران دوم', true);
                $p1 = ['mu' => $mu, 'sigma' => $sigma];
                $cdf1 = DL::cdf('normal', $p1, $x1);
                if ($x2 !== null) {
                    if ($x2 < $x1) throw new \InvalidArgumentException('کران دوم باید بزرگ‌تر یا مساوی کران اول باشد.');
                    $probability = DL::cdf('normal', $p1, $x2) - $cdf1;
                    $label = 'P(کران اول < X ≤ کران دوم)';
                } else {
                    $probability = 1 - $cdf1;
                    $label = 'P(X > کران اول)';
                }
                return [['title' => 'احتمال توزیع نرمال', 'metrics' => [[$label, number_format($probability, 6)], ['درصد', number_format($probability * 100, 3) . '%']], 'note' => 'محاسبه با CDF توزیع نرمال در موتور توزیع StatLab انجام شد.'], $values];
            case 'welch':
                $a = IV::parseNumbers((string) ($_POST['data1'] ?? ''));
                $b = IV::parseNumbers((string) ($_POST['data2'] ?? ''));
                $test = HT::run('two_sample_t', ['data1' => $a, 'data2' => $b, 'alternative' => (string) ($_POST['alternative'] ?? 'two')], $alpha);
                return [['title' => 'آزمون t دو نمونه مستقل Welch', 'metrics' => [
                    ['آماره ' . $test['statistic_name'], $test['statistic']], ['درجه آزادی', is_array($test['df']) ? json_encode($test['df'], JSON_UNESCAPED_UNICODE) : $test['df']],
                    ['p-value', $test['p_value']], ['سطح معنی‌داری α', $test['alpha']],
                    ['نتیجه', $test['conclusion'] === 'reject' ? 'رد فرض صفر' : 'شواهد کافی برای رد فرض صفر نیست'],
                ], 'note' => 'آزمون Welch برابری واریانس‌های دو جامعه را فرض نمی‌کند؛ استقلال مشاهدات و فرض‌های نمونه‌گیری باید جداگانه ارزیابی شوند.', 'warnings' => $test['warnings'], 'interpretation' => $test['interpretation_fa']], $values];
            case 'regression':
                $x = IV::parseNumbers((string) ($_POST['x'] ?? ''));
                $y = IV::parseNumbers((string) ($_POST['y'] ?? ''));
                $fit = RE::simple($x, $y, $alpha);
                return [['title' => 'رگرسیون خطی ساده', 'metrics' => [
                    ['معادله برازش‌شده', 'ŷ = ' . number_format($fit['intercept'], 4) . ' + ' . number_format($fit['slope'], 4) . 'x'],
                    ['r', $fit['r']], ['R²', $fit['r2']], ['p شیب', $fit['p_slope']],
                    ['تعداد مشاهده', count($x)], ['نتیجه آزمون شیب', $fit['conclusion'] === 'reject' ? 'معنی‌دار در α انتخاب‌شده' : 'شواهد کافی برای معنی‌داری شیب نیست'],
                ], 'note' => 'R² کیفیت علّی مدل یا عملکرد پیش‌بینی خارج از نمونه را تضمین نمی‌کند. نمودار پراکنش و باقیمانده‌ها را بررسی کنید.'], $values];
            default:
                throw new \InvalidArgumentException('محاسبه‌گر انتخاب‌شده معتبر نیست.');
        }
    }

    private function number(string $key, string $label, bool $optional = false): ?float
    {
        $raw = IV::normalizeDigits(trim((string) ($_POST[$key] ?? '')));
        if ($raw === '' && $optional) return null;
        return IV::finiteNumber($raw, $label);
    }

    private function curriculum(): array
    {
        if ($this->curriculum === null) $this->curriculum = require MODULAR_APP_PATH . '/app/Content/Curriculum.php';
        return $this->curriculum;
    }
}
