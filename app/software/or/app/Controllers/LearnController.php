<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Helpers\QueueingEngine as QE;
use App\Software\Or\Helpers\SimplexSolver;

final class LearnController extends Controller
{
    private ?array $curriculum = null;

    public function index(): void
    {
        $this->requireAuth();
        $c = $this->curriculum();
        $level = (string) ($_GET['level'] ?? 'all');
        if (!in_array($level, ['all', 'basic', 'middle', 'advanced'], true)) $level = 'all';
        $lessons = $c['lessons'];
        if ($level !== 'all') $lessons = array_values(array_filter($lessons, static fn(array $lesson): bool => $lesson['level'] === $level));
        $this->view('learn/index', ['pageTitle'=>'مرکز آموزش OR','lessons'=>$lessons,'levels'=>$c['levels'],'levelFilter'=>$level,'totalLessons'=>count($c['lessons']),'glossaryCount'=>count($c['glossary'])]);
    }

    public function show(): void
    {
        $this->requireAuth();
        $c = $this->curriculum();
        $ids = array_column($c['lessons'], 'id');
        $position = array_search((string)($_GET['lesson'] ?? ''), $ids, true);
        if ($position === false) { http_response_code(404); $this->redirect('controller=learn'); }
        $lesson = $c['lessons'][$position];
        $this->view('learn/show', ['pageTitle'=>$lesson['title'],'lesson'=>$lesson,'levelLabel'=>$c['levels'][$lesson['level']],
            'previous'=>$position > 0 ? $c['lessons'][$position-1] : null,'next'=>$position < count($ids)-1 ? $c['lessons'][$position+1] : null]);
    }

    public function glossary(): void
    {
        $this->requireAuth();
        $this->view('learn/glossary', ['pageTitle'=>'واژه‌نامه OR','terms'=>$this->curriculum()['glossary']]);
    }

    public function tools(): void
    {
        $this->requireAuth();
        $result = null;
        $values = $_POST;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try { $result = $this->runTool((string)($_POST['tool'] ?? '')); }
            catch (\InvalidArgumentException $e) { $result = ['error'=>$e->getMessage()]; }
            catch (\Throwable $e) { error_log('OR learning calculator failed: '.$e->getMessage()); $result = ['error'=>'محاسبه انجام نشد؛ ورودی‌ها و فرض‌های مدل را بررسی کنید.']; }
        }
        $this->view('learn/tools', ['pageTitle'=>'محاسبه‌گرهای آموزشی OR','result'=>$result,'values'=>$values]);
    }

    private function runTool(string $tool): array
    {
        if ($tool === 'mm1') {
            $lambda = $this->number('lambda', 'نرخ ورود');
            $mu = $this->number('mu', 'نرخ خدمت');
            if ($lambda <= 0 || $mu <= 0) throw new \InvalidArgumentException('نرخ ورود و خدمت باید بزرگ‌تر از صفر باشند.');
            $r = QE::solve('MM1', ['lambda'=>$lambda,'mu'=>$mu]);
            if (($r['status'] ?? '') !== 'ok') return ['title'=>'مدل M/M/1','metrics'=>[['وضعیت','ناپایدار'],['بهره‌وری ρ',$r['rho'] ?? '—']], 'note'=>'برای صف نامحدود M/M/1، نرخ ورود باید از نرخ خدمت کمتر باشد.'];
            return ['title'=>'نتیجه مدل M/M/1','metrics'=>[
                ['بهره‌وری ρ',$r['rho']],['احتمال خالی بودن P₀',$r['P0']],['میانگین در سیستم L',$r['L']],['میانگین در صف Lq',$r['Lq']],
                ['زمان در سیستم W',$r['W']],['انتظار در صف Wq',$r['Wq']],['احتمال انتظار Pw',$r['Pw']],
            ],'note'=>'نرخ‌ها باید واحد زمانی یکسان داشته باشند؛ L و Lq بر حسب تعداد و W و Wq بر حسب همان واحد زمانی نرخ‌ها هستند.'];
        }
        if ($tool === 'simplex') {
            $c = $this->numbers('objective', 'ضرایب تابع هدف');
            $rows = preg_split('/\R/u', trim((string)($_POST['matrix'] ?? '')));
            $A = [];
            foreach ($rows as $i=>$row) {
                if (trim($row) === '') continue;
                $A[] = $this->parseList($row, 'ضرایب محدودیت '.($i+1));
            }
            $b = $this->numbers('rhs', 'سمت راست محدودیت‌ها');
            $typesRaw = trim((string)($_POST['types'] ?? ''));
            $typesRaw = str_replace(['≤', '≥'], ['<=', '>='], $typesRaw);
            $types = $typesRaw === '' ? array_fill(0, count($b), '<=') : preg_split('/[\s,;|]+/', $typesRaw);
            if (count($c) < 1 || count($c) > 12 || count($A) < 1 || count($A) > 20 || count($A) !== count($b) || count($A) !== count($types)) throw new \InvalidArgumentException('تعداد متغیرها و محدودیت‌ها باید معتبر و هماهنگ باشد (حداکثر ۱۲ متغیر و ۲۰ محدودیت).');
            foreach ($A as $i=>$row) if (count($row) !== count($c)) throw new \InvalidArgumentException('هر سطر ماتریس باید به تعداد ضرایب تابع هدف مقدار داشته باشد.');
            foreach ($types as $type) if (!in_array($type, ['<=','>=','='], true)) throw new \InvalidArgumentException('نوع هر محدودیت فقط می‌تواند <=، >= یا = باشد.');
            $sense = (string)($_POST['sense'] ?? 'maximize');
            if (!in_array($sense, ['maximize','minimize'], true)) throw new \InvalidArgumentException('جهت تابع هدف معتبر نیست.');
            $r = SimplexSolver::solve($c,$A,$b,$types,$sense);
            if (($r['status'] ?? '') !== 'optimal') return ['title'=>'نتیجه حل LP','metrics'=>[['وضعیت',$r['status'] ?? 'error']], 'note'=>$r['message'] ?? 'حل‌گر جواب بهینه گزارش نکرد.'];
            $vars = [];
            foreach ($r['solution'] as $i=>$value) $vars[] = ['x'.($i+1),$value];
            return ['title'=>'جواب بهینه مدل خطی','metrics'=>array_merge([['مقدار بهینه',$r['optimal_value']],['تعداد تکرار',$r['iterations']]],$vars), 'note'=>'این محاسبه‌گر متغیرها را نامنفی و پیوسته فرض می‌کند. برای تصمیم‌های صحیح از مدل ILP استفاده کنید.'];
        }
        throw new \InvalidArgumentException('محاسبه‌گر معتبر نیست.');
    }

    private function parseList(string $text, string $label): array
    {
        $text = strtr($text, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','٫'=>'.']);
        $parts = preg_split('/[\s,;|]+/u', trim($text));
        if (!$parts || $parts === ['']) throw new \InvalidArgumentException($label.' الزامی است.');
        $out = [];
        foreach ($parts as $part) { if (!is_numeric($part) || !is_finite((float)$part)) throw new \InvalidArgumentException($label.' باید فقط شامل اعداد معتبر باشد.'); $out[]=(float)$part; }
        return $out;
    }

    private function numbers(string $key, string $label): array { return $this->parseList((string)($_POST[$key] ?? ''), $label); }
    private function number(string $key, string $label): float { $v=$this->numbers($key,$label); if(count($v)!==1) throw new \InvalidArgumentException($label.' باید یک عدد باشد.'); return $v[0]; }
    private function curriculum(): array { if($this->curriculum===null) $this->curriculum=require MODULAR_APP_PATH.'/app/Content/Curriculum.php'; return $this->curriculum; }
}
