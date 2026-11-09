<?php
namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Helpers\AhpCalculator;
use App\Software\Mcdm\Helpers\SawCalculator;
use App\Software\Mcdm\Helpers\TopsisCalculator;
use App\Software\Mcdm\Helpers\VikorCalculator;

final class LearnController extends Controller
{
    private ?array $curriculum = null;

    public function index(): void
    {
        $this->requireAuth();
        $c = $this->curriculum();
        $level = (string)($_GET['level'] ?? 'all');
        if (!in_array($level, ['all','basic','middle','advanced'], true)) $level = 'all';
        $lessons = $c['lessons'];
        if ($level !== 'all') $lessons = array_values(array_filter($lessons, static fn(array $l): bool => $l['level'] === $level));
        $this->view('learn/index', ['pageTitle'=>'مرکز آموزش MCDM','lessons'=>$lessons,'levels'=>$c['levels'],'levelFilter'=>$level,'totalLessons'=>count($c['lessons']),'glossaryCount'=>count($c['glossary'])]);
    }

    public function show(): void
    {
        $this->requireAuth();
        $c = $this->curriculum();
        $ids = array_column($c['lessons'], 'id');
        $pos = array_search((string)($_GET['lesson'] ?? ''), $ids, true);
        if ($pos === false) { http_response_code(404); $this->redirect('controller=learn'); }
        $lesson = $c['lessons'][$pos];
        $this->view('learn/show', ['pageTitle'=>$lesson['title'],'lesson'=>$lesson,'levelLabel'=>$c['levels'][$lesson['level']],
            'previous'=>$pos > 0 ? $c['lessons'][$pos-1] : null,'next'=>$pos < count($ids)-1 ? $c['lessons'][$pos+1] : null]);
    }

    public function glossary(): void
    {
        $this->requireAuth();
        $this->view('learn/glossary', ['pageTitle'=>'واژه‌نامه MCDM','terms'=>$this->curriculum()['glossary']]);
    }

    public function tools(): void
    {
        $this->requireAuth();
        $result = null;
        $values = $_POST;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try { $result = $this->runTool((string)($_POST['tool'] ?? '')); }
            catch (\InvalidArgumentException $e) { $result = ['error'=>$e->getMessage()]; }
            catch (\Throwable $e) { error_log('MCDM learning calculator failed: '.$e->getMessage()); $result = ['error'=>'محاسبه انجام نشد؛ ابعاد ماتریس، وزن‌ها و جهت معیارها را بررسی کنید.']; }
        }
        $this->view('learn/tools', ['pageTitle'=>'محاسبه‌گرهای آموزشی MCDM','result'=>$result,'values'=>$values]);
    }

    private function runTool(string $tool): array
    {
        if ($tool === 'ahp') {
            $matrix = $this->matrix((string)($_POST['pairwise'] ?? ''), 'ماتریس مقایسه زوجی');
            $r = AhpCalculator::analyze($matrix);
            if (($r['status'] ?? '') !== 'success') throw new \InvalidArgumentException($r['message'] ?? 'ماتریس AHP معتبر نیست.');
            $labels = $this->labels(count($r['weights']));
            $metrics = [];
            foreach ($r['weights'] as $i=>$w) $metrics[] = [$labels[$i], number_format($w, 6)];
            $metrics[] = ['λmax', $r['consistency_metrics']['lambda_max']];
            $metrics[] = ['CI', $r['consistency_metrics']['CI']];
            $metrics[] = ['RI', $r['consistency_metrics']['RI']];
            $metrics[] = ['CR', $r['consistency_metrics']['CR']];
            $metrics[] = ['سازگاری (CR≤0.10)', $r['consistency_metrics']['is_consistent'] ? 'قابل‌قبول' : 'نیازمند بازبینی'];
            return ['title'=>'خروجی وزن‌دهی AHP','metrics'=>$metrics,'note'=>'وزن‌ها با نرمال‌سازی ستونی و میانگین سطری در موتور این ماژول محاسبه شده‌اند. برای n=2، RI صفر است و CR قدرت تشخیص ناسازگاری ندارد.'];
        }
        if (!in_array($tool, ['saw','topsis','vikor'], true)) throw new \InvalidArgumentException('روش محاسبه معتبر نیست.');
        $matrix = $this->matrix((string)($_POST['decision_matrix'] ?? ''), 'ماتریس تصمیم');
        $weights = $this->list((string)($_POST['weights'] ?? ''), 'وزن معیارها');
        $typesRaw = trim((string)($_POST['types'] ?? ''));
        $types = $typesRaw === '' ? [] : preg_split('/[\s,;|]+/u', $typesRaw);
        if (count($matrix[0]) !== count($weights)) throw new \InvalidArgumentException('تعداد وزن‌ها باید با تعداد ستون‌های ماتریس تصمیم برابر باشد.');
        if ($typesRaw === '') $types = array_fill(0, count($weights), 'benefit');
        $types = array_map(static function ($type): string { return match (strtolower(trim((string)$type))) { 'سودی','سود','benefit','b' => 'benefit', 'هزینه‌ای','هزینه','cost','c' => 'cost', default => (string)$type }; }, $types);
        if (count($types) !== count($weights) || count($matrix[0]) !== count($types)) throw new \InvalidArgumentException('تعداد نوع معیارها باید با تعداد ستون‌ها برابر باشد. از benefit/cost یا سودی/هزینه‌ای استفاده کنید.');
        $namesRaw = trim((string)($_POST['alternatives'] ?? ''));
        $names = $namesRaw === '' ? array_map(static fn($i)=>'گزینه '.($i+1), array_keys($matrix)) : preg_split('/\R/u', $namesRaw);
        $names = array_map('trim', $names);
        if (count($names) !== count($matrix) || in_array('', $names, true)) throw new \InvalidArgumentException('برای هر سطر دقیقاً یک نام گزینه وارد کنید یا این قسمت را خالی بگذارید.');
        $r = match ($tool) {
            'saw' => SawCalculator::calculate($matrix,$weights,$types),
            'topsis' => TopsisCalculator::calculate($matrix,$weights,$types),
            'vikor' => VikorCalculator::calculate($matrix,$weights,$types,$this->number('v','پارامتر v',0.5)),
        };
        if (($r['status'] ?? '') !== 'success') throw new \InvalidArgumentException($r['message'] ?? 'روش انتخاب‌شده نتوانست ماتریس را تحلیل کند.');
        $ranking = [];
        foreach ($r['ranking'] as $row) $ranking[] = [$names[$row['alternative_index']] ?? ('گزینه '.($row['alternative_index']+1)), $row['rank'], number_format((float)$row['score'], 6)];
        $notes = ['saw'=>'امتیاز بالاتر رتبه بهتر دارد؛ به‌دلیل جبرانی بودن جمع وزنی، محدودیت‌های غیرقابل‌جبران را جدا اعمال کنید.','topsis'=>'امتیاز نزدیکی نسبی به ایده‌آل است، نه احتمال انتخاب. رتبه به گزینه‌های حاضر در ماتریس وابسته است.','vikor'=>'Q کمتر رتبه بهتر دارد؛ شروط مزیت قابل‌قبول و پایداری را نیز بررسی کنید.'];
        $extra = [];
        if ($tool === 'vikor') {
            $extra[] = ['پارامتر v', $r['v']];
            $extra[] = ['شرط مزیت قابل‌قبول', !empty($r['conditions']['acceptable_advantage']) ? 'برقرار' : 'برقرار نیست'];
            $extra[] = ['شرط پایداری', !empty($r['conditions']['acceptable_stability']) ? 'برقرار' : 'برقرار نیست'];
            $extra[] = ['DQ', $r['conditions']['dq'] ?? '—'];
            if (!empty($r['compromise_alternative_indices'])) $extra[] = ['گزینه‌های سازشی', implode('، ', array_map(static fn($i)=>$names[$i] ?? ('گزینه '.($i+1)), $r['compromise_alternative_indices']))];
        }
        return ['title'=>'رتبه‌بندی '.$r['method'],'metrics'=>$extra,'ranking'=>$ranking,'note'=>$notes[$tool]];
    }

    private function matrix(string $text, string $label): array
    {
        $text = trim($this->normalize($text));
        if ($text === '') throw new \InvalidArgumentException($label.' را وارد کنید.');
        $rows = [];
        foreach (preg_split('/\R/u', $text) as $line) if (trim($line) !== '') $rows[] = $this->list($line, $label);
        if (!$rows) throw new \InvalidArgumentException($label.' خالی است.');
        $width = count($rows[0]);
        foreach ($rows as $row) if (count($row) !== $width) throw new \InvalidArgumentException('تعداد مقادیر همه سطرهای ماتریس باید برابر باشد.');
        return $rows;
    }

    private function list(string $text, string $label): array
    {
        $parts = preg_split('/[\s,;|]+/u', trim($this->normalize($text)));
        if (!$parts || $parts === ['']) throw new \InvalidArgumentException($label.' را وارد کنید.');
        $values=[];
        foreach ($parts as $part) { if (!is_numeric($part) || !is_finite((float)$part)) throw new \InvalidArgumentException($label.' باید فقط شامل عددهای معتبر باشد.'); $values[]=(float)$part; }
        return $values;
    }

    private function normalize(string $text): string
    {
        return strtr($text, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','٫'=>'.']);
    }

    private function number(string $key, string $label, float $default): float
    {
        $raw=trim($this->normalize((string)($_POST[$key] ?? '')));
        if ($raw==='') return $default;
        if (!is_numeric($raw) || !is_finite((float)$raw)) throw new \InvalidArgumentException($label.' باید عددی متناهی باشد.');
        $number=(float)$raw;
        if ($number<0 || $number>1) throw new \InvalidArgumentException($label.' باید بین صفر و یک باشد.');
        return $number;
    }

    private function labels(int $n): array { return array_map(static fn($i)=>'معیار '.($i+1), range(0,$n-1)); }
    private function curriculum(): array { if ($this->curriculum===null) $this->curriculum=require MODULAR_APP_PATH.'/app/Content/Curriculum.php'; return $this->curriculum; }
}
