<?php
declare(strict_types=1);

namespace App\Software\Pdm\Controllers;

use App\Core\Controller;

/** مرکز یادگیری PdM، با مثال‌ها و ابزارهای آموزشی بدون ذخیره‌سازی. */
final class LearnController extends Controller
{
    private ?array $curriculum = null;

    public function index(): void
    {
        $this->requireAuth();
        $course = $this->curriculum();
        $level = (string) ($_GET['level'] ?? 'all');
        if (!isset($course['levels'][$level])) $level = 'all';
        $lessons = $level === 'all' ? $course['lessons'] : array_values(array_filter($course['lessons'], static fn(array $lesson): bool => $lesson['level'] === $level));
        $this->renderSoftware('learn/index', [
            'pageTitle'=>'مرکز آموزش نگهداری و تعمیرات', 'title'=>'مرکز آموزش PdM Analyzer', 'softwareName'=>'PdM Analyzer',
            'lessons'=>$lessons, 'levels'=>$course['levels'], 'levelFilter'=>$level,
            'totalLessons'=>count($course['lessons']), 'glossaryCount'=>count($course['glossary']), 'flash'=>pdm_flash_get(),
        ], 'pdm');
    }

    public function show(): void
    {
        $this->requireAuth();
        $course = $this->curriculum();
        $id = (string) ($_GET['lesson'] ?? '');
        $lesson = null;
        foreach ($course['lessons'] as $item) if ($item['id'] === $id) { $lesson = $item; break; }
        if ($lesson === null) { http_response_code(404); pdm_flash_set('danger', 'درس درخواستی پیدا نشد.'); $this->redirect(pdm_url('learn')); }
        $ids = array_column($course['lessons'], 'id');
        $position = array_search($id, $ids, true);
        $this->renderSoftware('learn/show', [
            'pageTitle'=>$lesson['title'], 'title'=>$lesson['title'].' - آموزش PdM', 'softwareName'=>'PdM Analyzer',
            'lesson'=>$lesson, 'levelLabel'=>$course['levels'][$lesson['level']] ?? $lesson['level'],
            'previous'=>$position !== false && $position > 0 ? $course['lessons'][$position - 1] : null,
            'next'=>$position !== false && $position < count($ids)-1 ? $course['lessons'][$position + 1] : null,
            'flash'=>pdm_flash_get(),
        ], 'pdm');
    }

    public function glossary(): void
    {
        $this->requireAuth();
        $this->renderSoftware('learn/glossary', [
            'pageTitle'=>'واژه‌نامهٔ نگهداری و تعمیرات', 'title'=>'واژه‌نامه PdM Analyzer', 'softwareName'=>'PdM Analyzer',
            'terms'=>$this->curriculum()['glossary'], 'flash'=>pdm_flash_get(),
        ], 'pdm');
    }

    public function tools(): void
    {
        $this->requireAuth();
        $result = null; $values = [];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (function_exists('it4ie_require_csrf')) it4ie_require_csrf();
            try { [$result, $values] = $this->runTool((string) ($_POST['tool'] ?? '')); }
            catch (\InvalidArgumentException $e) { $result = ['error'=>$e->getMessage()]; $values = $_POST; }
            catch (\Throwable $e) { error_log('PdM learning calculator failed: '.$e->getMessage()); $result = ['error'=>'محاسبه انجام نشد؛ ورودی‌ها را بررسی کنید.']; $values = $_POST; }
        }
        $this->renderSoftware('learn/tools', [
            'pageTitle'=>'ماشین‌حساب‌های آموزشی PdM', 'title'=>'ماشین‌حساب‌ها - PdM Analyzer', 'softwareName'=>'PdM Analyzer',
            'result'=>$result, 'values'=>$values, 'flash'=>pdm_flash_get(),
        ], 'pdm');
    }

    private function curriculum(): array
    {
        if ($this->curriculum === null) $this->curriculum = require MODULAR_APP_PATH.'/app/Content/Curriculum.php';
        return $this->curriculum;
    }

    private function runTool(string $tool): array
    {
        return match ($tool) {
            'reliability' => $this->toolReliability(),
            'exponential' => $this->toolExponential(),
            'fmea' => $this->toolFmea(),
            'stock' => $this->toolStock(),
            'rop' => $this->toolRop(),
            default => throw new \InvalidArgumentException('ماشین‌حساب درخواستی شناخته نشد.'),
        };
    }

    /** Mirrors the module convention: mean positive inter-event CM gaps and elapsed completed-WO durations. */
    private function toolReliability(): array
    {
        $intervals = $this->numberList('intervals', 'فاصلهٔ کارکرد بین خرابی‌ها', 1);
        $repairs = $this->numberList('repairs', 'مدت سپری‌شدهٔ تعمیر', 0);
        $mtbf = array_sum($intervals) / count($intervals);
        $mttr = array_sum($repairs) / count($repairs);
        $availability = $mtbf + $mttr > 0 ? 100 * $mtbf / ($mtbf + $mttr) : null;
        $metrics = [
            ['label'=>'MTBF میانگین فاصله‌ها','value'=>number_format($mtbf,2).' ساعت'],
            ['label'=>'MTTR میانگین زمان سپری‌شده','value'=>number_format($mttr,2).' ساعت'],
            ['label'=>'دسترس‌پذیری مدل ساده','value'=>$availability === null ? '—' : number_format($availability,2).'%'],
            ['label'=>'تعداد فاصله‌های خرابی','value'=>(string) count($intervals)],
        ];
        return [['title'=>'شاخص‌های قابلیت اطمینان و تعمیرپذیری','metrics'=>$metrics,
            'note'=>'برای هم‌خوانی با داشبورد PdM، فاصله‌ها را از پایان CM قبلی تا شروع CM بعدی وارد کنید. مدت تعمیر نیز بر اساس زمان سپری‌شدهٔ شروع تا تکمیل دستورکار است، نه نفرساعت. همهٔ داده‌ها باید برای جمعیت و واحد زمانی یکسان باشند.'],
            ['intervals'=>implode("\n",$intervals),'repairs'=>implode("\n",$repairs)]];
    }

    private function toolExponential(): array
    {
        $mtbf = $this->number('mtbf','MTBF',0.0000001);
        $hours = $this->number('hours','افق کارکرد t',0);
        $reliability = exp(-$hours / $mtbf);
        return [['title'=>'قابلیت اطمینان با فرض نرخ خرابی ثابت','metrics'=>[
            ['label'=>'R(t)','value'=>number_format(100*$reliability,3).'%'],
            ['label'=>'احتمال خرابی تا t','value'=>number_format(100*(1-$reliability),3).'%'],
            ['label'=>'نرخ خرابی λ','value'=>number_format(1/$mtbf,6).' بر ساعت'],
        ],'note'=>'مدل نمایی R(t)=exp(−t/MTBF) فرض نرخ خطر ثابت و زمان کارکردی را دارد. مقدار Reliability داخلی PdM برای افق ثابت ۱۰۰ ساعت محاسبه می‌شود.'],
            ['mtbf'=>$mtbf,'hours'=>$hours]];
    }

    private function toolFmea(): array
    {
        $severity = $this->integer('severity',1,10,'شدت S');
        $occurrence = $this->integer('occurrence',1,10,'وقوع O');
        $detection = $this->integer('detection',1,10,'کشف‌پذیری D');
        $rpn = $severity * $occurrence * $detection;
        [$label,$note] = match (true) {
            $rpn >= 200 => ['بحرانی','طبق آستانهٔ داخلی مدل PdM: RPN حداقل ۲۰۰.'],
            $rpn >= 100 => ['بالا','طبق آستانهٔ داخلی مدل PdM: RPN از ۱۰۰ تا ۱۹۹.'],
            $rpn >= 50 => ['متوسط','طبق آستانهٔ داخلی مدل PdM: RPN از ۵۰ تا ۹۹.'],
            default => ['پایین','طبق آستانهٔ داخلی مدل PdM: RPN کمتر از ۵۰.'],
        };
        return [['title'=>'RPN و سطح نمایش PdM','metrics'=>[
            ['label'=>'RPN = S × O × D','value'=>(string)$rpn],['label'=>'سطح آستانه‌ای ماژول','value'=>$label],
        ],'note'=>$note.' RPN ابزار غربالگری است؛ شدت پیامدهای ایمنی/قانونی را جداگانه بررسی کنید و آستانه‌ها را استاندارد عمومی تلقی نکنید.'],
            ['severity'=>$severity,'occurrence'=>$occurrence,'detection'=>$detection]];
    }

    private function toolStock(): array
    {
        $stock = $this->number('stock','موجودی فعلی',0);
        $minimum = $this->number('minimum','حداقل موجودی',0);
        $low = $stock <= $minimum;
        return [['title'=>'بررسی آستانهٔ کمبود قطعه در PdM','metrics'=>[
            ['label'=>'وضعیت مطابق قاعدهٔ ماژول','value'=>$low?'کم‌موجودی':'بالاتر از حداقل'],
            ['label'=>'فاصله تا حداقل','value'=>number_format(max(0,$minimum-$stock),2)],
            ['label'=>'مازاد بر حداقل','value'=>number_format(max(0,$stock-$minimum),2)],
        ],'note'=>'قاعدهٔ فهرست کمبود PdM دقیقاً موجودی ≤ حداقل موجودی است. این نتیجه مقدار سفارش را تعیین نمی‌کند و سفارش در راه یا رزرو را لحاظ نمی‌کند.'],
            ['stock'=>$stock,'minimum'=>$minimum]];
    }

    private function toolRop(): array
    {
        $demand = $this->number('demand','میانگین تقاضای روزانه',0);
        $lead = $this->number('lead','زمان تأمین به روز',0);
        $safety = $this->number('safety','موجودی اطمینان',0);
        $rop = $demand * $lead + $safety;
        return [['title'=>'برآورد آموزشی نقطهٔ سفارش','metrics'=>[
            ['label'=>'تقاضا طی زمان تأمین','value'=>number_format($demand*$lead,2)],
            ['label'=>'نقطهٔ سفارش ROP','value'=>number_format($rop,2)],
        ],'note'=>'ROP = تقاضای متوسط در زمان تأمین + موجودی اطمینان. ابزار آموزشی است و در سفارش یا رکورد قطعات PdM چیزی ذخیره نمی‌کند؛ عدم‌قطعیت تقاضا و زمان تأمین را جدا تحلیل کنید.'],
            ['demand'=>$demand,'lead'=>$lead,'safety'=>$safety]];
    }

    private function numberList(string $key,string $label,float $min): array
    {
        $raw = trim((string)($_POST[$key] ?? ''));
        if ($raw === '') throw new \InvalidArgumentException($label.' را هر مقدار در یک سطر وارد کنید.');
        $values = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            if (trim($line) === '') continue;
            $value = $this->parseNumber($line,$label);
            if ($value < $min) throw new \InvalidArgumentException($label.' باید '.($min > 0 ? 'بزرگ‌تر از صفر' : 'نامنفی').' باشد.');
            $values[] = $value;
        }
        if (!$values) throw new \InvalidArgumentException($label.' معتبر وارد نشده است.');
        return $values;
    }

    private function integer(string $key,int $min,int $max,string $label): int
    {
        $raw = $this->digits((string)($_POST[$key] ?? ''));
        if (!preg_match('/^\d+$/',$raw)) throw new \InvalidArgumentException($label.' باید عدد صحیح باشد.');
        $value = (int)$raw;
        if ($value < $min || $value > $max) throw new \InvalidArgumentException($label.' باید بین '.$min.' و '.$max.' باشد.');
        return $value;
    }

    private function number(string $key,string $label,float $min=0): float
    {
        $value = $this->parseNumber((string)($_POST[$key] ?? ''),$label);
        if ($value < $min) throw new \InvalidArgumentException($label.' خارج از دامنهٔ مجاز است.');
        return $value;
    }

    private function parseNumber(string $raw,string $label): float
    {
        $raw = $this->digits(trim($raw));
        if ($raw === '' || !is_numeric($raw) || !is_finite((float)$raw)) throw new \InvalidArgumentException($label.' باید عدد متناهی باشد.');
        return (float)$raw;
    }

    private function digits(string $value): string
    {
        return strtr(str_replace(['٬','،',' '],['','',''],$value),[
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','٫'=>'.','−'=>'-',
        ]);
    }
}
