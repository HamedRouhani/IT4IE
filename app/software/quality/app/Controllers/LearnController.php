<?php
declare(strict_types=1);

namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Services\SamplingService;

/** Center for guided Quality/SPC learning and non-persisting calculators. */
final class LearnController extends Controller
{
    private ?array $curriculum = null;

    public function index(): void
    {
        $this->requireAuth();
        $content = $this->curriculum();
        $level = (string) ($_GET['level'] ?? 'all');
        if (!array_key_exists($level, $content['levels'])) $level = 'all';
        $lessons = $level === 'all' ? $content['lessons'] : array_values(array_filter($content['lessons'], static fn(array $lesson): bool => $lesson['level'] === $level));
        $this->renderSoftware('learn/index', [
            'pageTitle' => 'مرکز آموزش تحلیل کیفیت', 'title' => 'مرکز آموزش Quality Analyzer', 'softwareName' => 'Quality Analyzer',
            'lessons' => $lessons, 'levels' => $content['levels'], 'levelFilter' => $level,
            'totalLessons' => count($content['lessons']), 'glossaryCount' => count($content['glossary']), 'flash' => qc_flash_get(),
        ], 'quality');
    }

    public function show(): void
    {
        $this->requireAuth();
        $content = $this->curriculum();
        $id = (string) ($_GET['lesson'] ?? '');
        $lesson = null;
        foreach ($content['lessons'] as $candidate) if ($candidate['id'] === $id) { $lesson = $candidate; break; }
        if ($lesson === null) { http_response_code(404); qc_flash_set('danger', 'درس درخواستی پیدا نشد.'); $this->redirect(qc_url('learn', 'index')); }
        $ids = array_column($content['lessons'], 'id');
        $position = array_search($id, $ids, true);
        $this->renderSoftware('learn/show', [
            'pageTitle' => $lesson['title'], 'title' => $lesson['title'] . ' - Quality Analyzer', 'softwareName' => 'Quality Analyzer',
            'lesson' => $lesson, 'levelLabel' => $content['levels'][$lesson['level']] ?? $lesson['level'],
            'previous' => $position !== false && $position > 0 ? $content['lessons'][$position - 1] : null,
            'next' => $position !== false && $position < count($ids) - 1 ? $content['lessons'][$position + 1] : null,
            'flash' => qc_flash_get(),
        ], 'quality');
    }

    public function glossary(): void
    {
        $this->requireAuth();
        $content = $this->curriculum();
        $this->renderSoftware('learn/glossary', [
            'pageTitle' => 'واژه‌نامهٔ تحلیل کیفیت', 'title' => 'واژه‌نامه - Quality Analyzer', 'softwareName' => 'Quality Analyzer',
            'terms' => $content['glossary'], 'flash' => qc_flash_get(),
        ], 'quality');
    }

    public function tools(): void
    {
        $this->requireAuth();
        $result = null;
        $values = [];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (function_exists('it4ie_require_csrf')) it4ie_require_csrf();
            try { [$result, $values] = $this->runTool((string) ($_POST['tool'] ?? '')); }
            catch (\InvalidArgumentException $e) { $result = ['error' => $e->getMessage()]; $values = $_POST; }
            catch (\Throwable $e) { error_log('Quality learning calculator failed: ' . $e->getMessage()); $result = ['error' => 'محاسبه انجام نشد؛ ورودی‌ها را بررسی کنید.']; $values = $_POST; }
        }
        $this->renderSoftware('learn/tools', [
            'pageTitle' => 'ماشین‌حساب‌های آموزشی کیفیت', 'title' => 'ماشین‌حساب‌های آموزشی - Quality Analyzer', 'softwareName' => 'Quality Analyzer',
            'result' => $result, 'values' => $values, 'flash' => qc_flash_get(),
        ], 'quality');
    }

    private function curriculum(): array
    {
        if ($this->curriculum === null) $this->curriculum = require MODULAR_APP_PATH . '/app/Content/Curriculum.php';
        return $this->curriculum;
    }

    private function runTool(string $tool): array
    {
        return match ($tool) {
            'capability' => $this->capabilityTool(),
            'p-chart' => $this->pChartTool(),
            'acceptance' => $this->acceptanceTool(),
            'grr' => $this->grrTool(),
            'pareto' => $this->paretoTool(),
            default => throw new \InvalidArgumentException('ماشین‌حساب درخواستی شناخته نشد.'),
        };
    }

    private function capabilityTool(): array
    {
        $mean = $this->number('mean', 'میانگین فرایند');
        $within = $this->number('within', 'انحراف معیار درون‌گروهی', 0.0000001);
        $overall = $this->number('overall', 'انحراف معیار کلی', 0.0000001);
        $lsl = $this->number('lsl', 'حد مشخصات پایین');
        $usl = $this->number('usl', 'حد مشخصات بالا');
        $target = $this->number('target', 'مقدار هدف');
        if ($usl <= $lsl) throw new \InvalidArgumentException('حد مشخصات بالا باید از حد پایین بزرگ‌تر باشد.');
        $cp = ($usl - $lsl) / (6 * $within);
        $cpk = min($usl - $mean, $mean - $lsl) / (3 * $within);
        $pp = ($usl - $lsl) / (6 * $overall);
        $ppk = min($usl - $mean, $mean - $lsl) / (3 * $overall);
        $cpm = ($usl - $lsl) / (6 * sqrt($overall ** 2 + ($mean - $target) ** 2));
        return [['title'=>'قابلیت و عملکرد فرایند','metrics'=>[
            ['label'=>'Cp (درون‌گروهی)','value'=>number_format($cp,4)], ['label'=>'Cpk (درون‌گروهی و مرکز)','value'=>number_format($cpk,4)],
            ['label'=>'Pp (پراکندگی کلی)','value'=>number_format($pp,4)], ['label'=>'Ppk (کلی و مرکز)','value'=>number_format($ppk,4)],
            ['label'=>'Cpm (با فاصله از هدف)','value'=>number_format($cpm,4)],
        ],'note'=>'فرض: فرایند پایدار و برآوردهای σ مناسب‌اند. برای توزیع غیرنرمال یا دادهٔ ناپایدار، این شاخص‌ها را با روش مناسب بازتحلیل کنید.'],
            ['mean'=>$mean,'within'=>$within,'overall'=>$overall,'lsl'=>$lsl,'usl'=>$usl,'target'=>$target]];
    }

    private function pChartTool(): array
    {
        $pbar = $this->number('pbar', 'نسبت مبنا', 0, 1);
        $n = $this->integer('n', 1, 1000000, 'حجم نمونه');
        $defectives = trim((string) ($_POST['defectives'] ?? '')) === '' ? null : $this->integer('defectives', 0, $n, 'تعداد معیوب');
        $sigma = sqrt($pbar * (1 - $pbar) / $n);
        $lcl = max(0, $pbar - 3 * $sigma);
        $ucl = min(1, $pbar + 3 * $sigma);
        $metrics = [['label'=>'خط مرکز p̄','value'=>number_format($pbar*100,3).'%'],['label'=>'حد پایین','value'=>number_format($lcl*100,3).'%'],['label'=>'حد بالا','value'=>number_format($ucl*100,3).'%']];
        if ($defectives !== null) { $p = $defectives / $n; $metrics[]=['label'=>'نسبت مشاهده‌شده','value'=>number_format($p*100,3).'%']; $metrics[]=['label'=>'سیگنال بیرون حدود','value'=>($p<$lcl || $p>$ucl)?'بله؛ بررسی شود':'خیر']; }
        return [['title'=>'حدود نمودار p','metrics'=>$metrics,'note'=>'p̄ باید از خط مبنای پایدار و کافی برآورد شده باشد؛ حدود نمودار، حدود مشخصات محصول نیستند.'],['pbar'=>$pbar,'n'=>$n,'defectives'=>$defectives ?? '']];
    }

    private function acceptanceTool(): array
    {
        $n = $this->integer('n', 1, 2000, 'حجم نمونه');
        $c = $this->integer('c', 0, $n, 'عدد پذیرش');
        $aql = $this->number('aql', 'AQL', 0, 1);
        $rql = $this->number('rql', 'RQL/LTPD', 0, 1);
        if ($rql <= $aql) throw new \InvalidArgumentException('RQL باید بزرگ‌تر از AQL باشد.');
        $service = new SamplingService();
        $paAql = $service->acceptanceProbability($n, $c, $aql);
        $paRql = $service->acceptanceProbability($n, $c, $rql);
        return [['title'=>'احتمال پذیرش و ریسک‌های طرح','metrics'=>[
            ['label'=>'Pa در AQL','value'=>number_format(100*$paAql,3).'%'],['label'=>'ریسک تولیدکننده α','value'=>number_format(100*(1-$paAql),3).'%'],
            ['label'=>'Pa در RQL','value'=>number_format(100*$paRql,3).'%'],['label'=>'ریسک مصرف‌کننده β','value'=>number_format(100*$paRql,3).'%'],
        ],'note'=>'مدل دوجمله‌ایِ نمونه‌گیری مستقل به کار می‌رود. برای محمولهٔ محدود و نمونه‌گیری بدون جایگذاری، احتمال فوق‌هندسی را بررسی کنید؛ محاسبهٔ آموزشی جای انتخاب طرح مطابق قرارداد/استاندارد را نمی‌گیرد.'],
            ['n'=>$n,'c'=>$c,'aql'=>$aql*100,'rql'=>$rql*100]];
    }

    private function grrTool(): array
    {
        $ev=$this->number('ev','انحراف معیار EV',0); $av=$this->number('av','انحراف معیار AV',0); $pv=$this->number('pv','انحراف معیار قطعه',0);
        $grr=sqrt($ev*$ev+$av*$av); $total=sqrt($grr*$grr+$pv*$pv);
        return [['title'=>'خلاصهٔ مؤلفه‌های تغییر Gage R&R','metrics'=>[
            ['label'=>'σGRR','value'=>number_format($grr,5)],['label'=>'σکل','value'=>number_format($total,5)],
            ['label'=>'%GRR از تغییر مطالعه','value'=>$total>0?number_format(100*$grr/$total,2).'%':'—'],
            ['label'=>'درصد سهم واریانس GRR','value'=>$total>0?number_format(100*$grr*$grr/($total*$total),2).'%':'—'],
            ['label'=>'ndc تقریبی','value'=>$grr>0?(string)floor(1.41*$pv/$grr):'—'],
        ],'note'=>'این ابزار مؤلفه‌های EV، AV و PV را ترکیب می‌کند؛ جایگزین مطالعهٔ متوازن و محاسبهٔ ANOVA در ماژول MSA نیست. %GRR تغییر مطالعه با درصد سهم واریانس فرق دارد.'],['ev'=>$ev,'av'=>$av,'pv'=>$pv]];
    }

    private function paretoTool(): array
    {
        $raw=trim((string)($_POST['items']??'')); if($raw==='') throw new \InvalidArgumentException('دسته‌ها و مقدار هرکدام را در سطرهای جدا وارد کنید.');
        $rows=[]; foreach(preg_split('/\r\n|\r|\n/',$raw) as $line){$line=trim($line);if($line==='')continue;$parts=explode(',', $line, 2);if(count($parts)!==2)throw new \InvalidArgumentException('هر سطر باید به شکل «دسته, مقدار» باشد.');$name=trim($parts[0]);$value=$this->parseNumber($parts[1],'مقدار دسته');if($name===''||$value<0)throw new \InvalidArgumentException('نام دسته الزامی است و مقدار نباید منفی باشد.');$rows[]=['name'=>$name,'value'=>$value];}
        if(!$rows)throw new \InvalidArgumentException('دادهٔ قابل محاسبه‌ای وارد نشده است.');
        usort($rows,static fn($a,$b)=>$b['value']<=>$a['value']);$total=array_sum(array_column($rows,'value'));$cum=0;$table=[];
        foreach($rows as $r){$share=$total>0?$r['value']/$total:0;$cum+=$share;$table[]=[$r['name'],number_format($r['value'],3),number_format(100*$share,2).'%',number_format(100*$cum,2).'%'];}
        return [['title'=>'جدول اولویت‌بندی پارتو','columns'=>['دسته','مقدار','سهم','سهم تجمعی'],'rows'=>$table,'metrics'=>[['label'=>'جمع مقدار','value'=>number_format($total,3)],['label'=>'تعداد دسته','value'=>(string)count($rows)]],'note'=>'مقادیر باید هم‌واحد و با مبنای یکسان باشند. پارتو اولویت تحلیل را نشان می‌دهد و علت ریشه‌ای را اثبات نمی‌کند.'],['items'=>$raw]];
    }

    private function integer(string $key,int $min,int $max,string $label): int
    {
        $raw=$this->digits((string)($_POST[$key]??''));if(!preg_match('/^\d+$/',$raw))throw new \InvalidArgumentException($label.' باید عدد صحیح نامنفی باشد.');$value=(int)$raw;if($value<$min||$value>$max)throw new \InvalidArgumentException($label.' باید بین '.$min.' و '.$max.' باشد.');return $value;
    }
    private function number(string $key,string $label,float $min=-INF,float $max=INF): float
    {
        $value=$this->parseNumber((string)($_POST[$key]??''),$label);if($value<$min||$value>$max)throw new \InvalidArgumentException($label.' خارج از دامنهٔ مجاز است.');return $value;
    }
    private function parseNumber(string $raw,string $label): float
    {
        $value=$this->digits(trim($raw));if($value===''||!is_numeric($value)||!is_finite((float)$value))throw new \InvalidArgumentException($label.' باید عدد متناهی باشد.');return (float)$value;
    }
    private function digits(string $value): string
    {
        return strtr(str_replace(['٬','،',' '],['','',''],$value),['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','٫'=>'.','−'=>'-']);
    }
}
