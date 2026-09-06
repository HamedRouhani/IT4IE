<?php
namespace App\Software\Statlab\Helpers;

/**
 * SmartStatistician - دستیار هوشمند تشخیص تحلیل آماری از متن فارسی
 */
class SmartStatistician
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    // ═══ نگاشت کد آزمون → مسیر ماژول ═══
    public static function routeFor(string $test): array
    {
        $hyp = ['one_sample_t','two_sample_t','paired_t','one_prop_z','chi2_gof','chi2_indep','f_var','mann_whitney'];
        $reg = ['correlation','simple','multiple'];
        if ($test === 'descriptive')   return ['controller' => 'descriptive', 'param' => ''];
        if ($test === 'distribution')  return ['controller' => 'distribution', 'param' => ''];
        if (in_array($test, $hyp))     return ['controller' => 'hypothesis', 'param' => 'test=' . $test];
        if (in_array($test, $reg))     return ['controller' => 'regression', 'param' => 'mode=' . $test];
        return ['controller' => 'descriptive', 'param' => ''];
    }

    public static function testNameFa(string $test): string
    {
        $names = [
            'descriptive'  => 'آمار توصیفی',
            'distribution' => 'توزیع‌های احتمال',
            'one_sample_t' => 'آزمون t میانگین یک نمونه',
            'two_sample_t' => 'آزمون t دو نمونه مستقل',
            'paired_t'     => 'آزمون t جفتی (وابسته)',
            'one_prop_z'   => 'آزمون Z نسبت',
            'chi2_gof'     => 'کای‌دو نیکویی برازش',
            'chi2_indep'   => 'کای‌دو استقلال',
            'f_var'        => 'آزمون F برابری واریانس‌ها',
            'mann_whitney' => 'آزمون U من-ویتنی',
            'correlation'  => 'همبستگی پیرسون/اسپیرمن',
            'simple'       => 'رگرسیون خطی ساده',
            'multiple'     => 'رگرسیون چندگانه',
        ];
        return $names[$test] ?? $test;
    }

    // ═══ تحلیل اصلی ═══
    public function analyze(string $text): array
    {
        if (mb_strlen(trim($text)) < 15) {
            return ['success' => false, 'error' => 'متن بسیار کوتاه است. لطفاً مسئله را کامل‌تر توضیح دهید.'];
        }

        $clean = $this->normalizeText($text);
        $scores = $this->calculateScores($clean);
        arsort($scores);

        $top = array_key_first($scores);
        $total = array_sum($scores);
        $confidence = $total > 0 ? round(($scores[$top] / $total) * 100, 1) : 0;

        $params = $this->extractParams($clean, $top);

        $result = [
            'success'         => true,
            'detected_test'   => $top,
            'detected_name'   => self::testNameFa($top),
            'category'        => $this->categoryOf($top),
            'confidence'      => $confidence,
            'all_scores'      => $scores,
            'extracted_params'=> $params,
            'suggested'       => $this->suggest($top),
            'next_steps'      => $this->nextSteps($top),
            'warnings'        => $this->warnings($top, $params),
            'route'           => self::routeFor($top),
            'numbers_found'   => $this->extractNumbers($clean),
        ];

        $this->saveAnalysis($text, $result);
        return $result;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $text
        );
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function extractNumbers(string $text): array
    {
        preg_match_all('/\d+(?:\.\d+)?/', $text, $m);
        return array_map('floatval', $m[0]);
    }

    private function categoryOf(string $test): string
    {
        if ($test === 'descriptive') return 'descriptive';
        if ($test === 'distribution') return 'distribution';
        if (in_array($test, ['correlation','simple','multiple'])) return 'regression';
        return 'hypothesis';
    }

    // ═══ امتیازدهی ═══
    private function calculateScores(string $t): array
    {
        $s = array_fill_keys([
            'descriptive','distribution','one_sample_t','two_sample_t','paired_t',
            'one_prop_z','chi2_gof','chi2_indep','f_var','mann_whitney',
            'correlation','simple','multiple'
        ], 0);

        $add = function (string $key, array $words, int $w) use (&$s, $t) {
            foreach ($words as $word) {
                $c = substr_count($t, $word);
                if ($c > 0) $s[$key] += $c * $w;
            }
        };

        $add('descriptive',  ['توصیف'=>5,'خلاصه'=>4,'میانگین'=>2,'میانه'=>2,'انحراف معیار'=>3,'پراکندگی'=>3,'چولگی'=>3,'کشیدگی'=>2,'چهارک'=>2], 1);
        $add('distribution', ['توزیع'=>4,'پواسون'=>6,'دوجمله‌ای'=>6,'وایبول'=>6,'نمایی'=>3,'صدک'=>4,'احتمال'=>3,'پیشامد'=>2], 1);
        $add('one_sample_t', ['یک نمونه'=>5,'مقدار فرض'=>6,'جامعه مشخص'=>3,'استاندارد'=>2,'آزمون'=>2,'فرض'=>2], 1);
        $add('two_sample_t', ['دو گروه'=>6,'دو نمونه'=>6,'مستقل'=>3,'مقایسه'=>3,'آزمون'=>2], 1);
        $add('paired_t',     ['جفتی'=>8,'قبل و بعد'=>8,'پیش و پس'=>7,'پس‌آزمون'=>6,'پیش‌آزمون'=>6,'وابسته'=>4,'همان افراد'=>5], 1);
        $add('one_prop_z',   ['نسبت'=>5,'درصد'=>4,'شیوع'=>4,'موفقیت'=>3,'آزمون'=>2], 1);
        $add('chi2_gof',     ['نیکویی'=>8,'برازش'=>6,'یکنواخت'=>4,'فراوانی'=>3], 1);
        $add('chi2_indep',   ['استقلال'=>6,'توافقی'=>8,'کیفی'=>4,'فراوانی'=>3], 1);
        $add('f_var',        ['واریانس'=>5,'برابری واریانس'=>8,'همگنی واریانس'=>6], 1);
        $add('mann_whitney', ['من-ویتنی'=>10,'ویلکاکسون'=>8,'ناپارامتری'=>7,'نرمال نیست'=>6,'رتبه‌ای'=>4,'رتبه ای'=>4], 1);
        $add('correlation',  ['همبستگی'=>8,'پیرسون'=>7,'اسپیرمن'=>8,'رابطه'=>3,'ضریب'=>3], 1);
        $add('simple',       ['رگرسیون'=>6,'پیش‌بینی'=>6,'پیش بینی'=>6,'تأثیر'=>4,'تاثیر'=>4,'مدل'=>2], 1);
        $add('multiple',     ['چندگانه'=>8,'چند متغیر'=>6,'چند پیشبین'=>7,'چند مستقل'=>6], 1);

        // ─── قوانین ترکیبی هوشمند ───
        $has = fn(...$ws) => array_reduce($ws, fn($c, $w) => $c || strpos($t, $w) !== false, false);

        $isTest = $has('آزمون','فرض','معنادار','فرضیه','H0','p-value');

        if ($has('جفتی','قبل و بعد','پیش و پس','پیش‌آزمون','پس‌آزمون')) $s['paired_t'] += 25;
        if ($isTest && $has('دو گروه','دو نمونه') && !$has('جفتی','وابسته')) $s['two_sample_t'] += 20;
        if ($isTest && $has('یک نمونه','مقدار فرض','جامعه مشخص')) $s['one_sample_t'] += 20;
        if ($isTest && $has('نسبت','درصد','شیوع')) $s['one_prop_z'] += 20;
        if ($has('ناپارامتری','نرمال نیست','رتبه‌ای','رتبه ای')) $s['mann_whitney'] += 25;
        if ($has('کیفی') && $has('رابطه','استقلال','تفاوت','مقایسه')) $s['chi2_indep'] += 25;
        if ($has('نیکویی','برازش')) $s['chi2_gof'] += 25;
        if ($has('برابری واریانس','همگنی واریانس')) $s['f_var'] += 25;
        if ($has('همبستگی','رابطه') && !$has('پیش‌بینی','پیش بینی','رگرسیون')) $s['correlation'] += 20;
        if ($has('پیش‌بینی','پیش بینی','رگرسیون','تأثیر','تاثیر') && $has('چندگانه','چند متغیر','چند مستقل')) $s['multiple'] += 25;
        elseif ($has('پیش‌بینی','پیش بینی','رگرسیون','تأثیر','تاثیر')) $s['simple'] += 20;
        if ($has('توزیع') && $has('احتمال','صدک','P(')) $s['distribution'] += 20;
        if ($has('توصیف','خلاصه') && !$isTest && !$has('رگرسیون','همبستگی','پیش‌بینی')) $s['descriptive'] += 25;

        return $s;
    }

    // ═══ استخراج پارامترها ═══
    private function extractParams(string $t, string $test): array
    {
        $p = ['alpha' => 0.05, 'alternative' => 'two', 'n' => null, 'confidence' => 95];

        // سطح معناداری
        if (preg_match('/سطح\s*(?:معناداری)?\s*\(?\s*0\.(\d+)/u', $t, $m)) {
            $p['alpha'] = (float)('0.' . $m[1]);
        } elseif (preg_match('/(\d{2})\s*درصد\s*اطمینان/u', $t, $m)) {
            $p['alpha'] = round(1 - ((float)$m[1] / 100), 3);
        }
        $p['confidence'] = round((1 - $p['alpha']) * 100);

        // جهت فرض جایگزین
        if (preg_match('/(بیشتر|بزرگتر|افزایش|بهبود یافته)/u', $t)) $p['alternative'] = 'greater';
        elseif (preg_match('/(کمتر|کوچکتر|کاهش)/u', $t)) $p['alternative'] = 'less';

        // حجم نمونه
        if (preg_match('/(\d+)\s*(?:نفر|مشاهده|نمونه|داده|بسته|قطعه)/u', $t, $m)) {
            $p['n'] = (int)$m[1];
        }

        return $p;
    }

    // ═══ پیشنهاد روش ═══
    private function suggest(string $test): array
    {
        $map = [
            'descriptive'  => ['reason' => 'هدف، توصیف و خلاصه‌سازی داده‌هاست؛ نیازی به استنتاج آماری نیست.'],
            'distribution' => ['reason' => 'سؤال درباره احتمال/صدک یک توزیع است؛ از ماشین‌حساب توزیع‌ها استفاده کنید.'],
            'one_sample_t' => ['reason' => 'مقایسه میانگین یک نمونه با یک مقدار فرضی؛ آزمون t تک‌نمونه‌ای مناسب است.'],
            'two_sample_t' => ['reason' => 'دو گروه مستقل کمی مقایسه می‌شوند؛ آزمون t ولچ (Welch) توصیه می‌شود.'],
            'paired_t'     => ['reason' => 'داده‌ها جفتی/وابسته‌اند (قبل-بعد)؛ آزمون t جفتی صحیح است.'],
            'one_prop_z'   => ['reason' => 'متغیر موردنظر نسبت/درصد است؛ آزمون Z تک‌نسبتی مناسب است.'],
            'chi2_gof'     => ['reason' => 'بررسی تطابق فراوانی‌های مشاهده با توزیع موردانتظار؛ کای‌دو نیکویی برازش.'],
            'chi2_indep'   => ['reason' => 'دو متغیر کیفی در جدول توافقی؛ کای‌دو استقلال مناسب است.'],
            'f_var'        => ['reason' => 'مقایسه پراکندگی دو گروه؛ آزمون F برابری واریانس‌ها.'],
            'mann_whitney' => ['reason' => 'فرض نرمال بودن برقرار نیست یا داده رتبه‌ای است؛ آزمون ناپارامتری من-ویتنی.'],
            'correlation'  => ['reason' => 'هدف سنجش شدت و جهت رابطه است، نه پیش‌بینی؛ همبستگی پیرسون/اسپیرمن.'],
            'simple'       => ['reason' => 'یک متغیر مستقل برای پیش‌بینی متغیر وابسته؛ رگرسیون خطی ساده.'],
            'multiple'     => ['reason' => 'چند متغیر مستقل برای پیش‌بینی؛ رگرسیون چندگانه (OLS).'],
        ];
        return ['name' => self::testNameFa($test), 'reason' => $map[$test]['reason'] ?? ''];
    }

    // ═══ گام‌های بعدی ═══
    private function nextSteps(string $test): array
    {
        switch ($this->categoryOf($test)) {
            case 'descriptive':
                return ['داده‌ها را وارد کنید','خلاصه آماری + نمودار هیستوگرام و BoxPlot را بررسی کنید','داده‌های پرت را بازبینی کنید'];
            case 'distribution':
                return ['توزیع و پارامترها را انتخاب کنید','نوع محاسبه (دم چپ/راست/بازه/صدک) را مشخص کنید','نتیجه و نمودار را تفسیر کنید'];
            case 'regression':
                return ['داده‌های Y و X را وارد یا از پروژه بارگذاری کنید','ضرایب، R² و p-value را بررسی کنید','فروض (خطی بودن، همگنی واریانس) را بازبینی کنید'];
            default: // hypothesis
                return [
                    'فرض‌های H0 و H1 را بنویسید',
                    'سطح α را تعیین کنید (پیش‌فرض 0.05)',
                    'داده‌ها را وارد کنید',
                    'آماره آزمون و p-value را محاسبه کنید',
                    'اگر p < α: رد H0 (معنادار)',
                ];
        }
    }

    // ═══ هشدارها ═══
    private function warnings(string $test, array $p): array
    {
        $w = [];
        if (in_array($test, ['one_sample_t','two_sample_t','paired_t']) && ($p['n'] !== null && $p['n'] < 30)) {
            $w[] = '⚠️ حجم نمونه کمتر از ۳۰ است؛ پیش از آزمون t، نرمال بودن داده‌ها را بررسی کنید.';
        }
        if ($test === 'mann_whitney') {
            $w[] = 'ℹ️ من-ویتنی فرض نرمال بودن نیاز ندارد؛ برای مقایسه میانه‌ها مناسب است.';
        }
        if (in_array($test, ['chi2_gof','chi2_indep'])) {
            $w[] = '⚠️ مطمئن شوید فراوانی موردانتظار هر خانه ≥ ۵ باشد.';
        }
        if ($test === 'multiple') {
            $w[] = '⚠️ تعداد مشاهدات باید بیشتر از (تعداد پیشبین‌ها + ۱) باشد؛ هم‌خطی را بررسی کنید.';
        }
        if ($test === 'paired_t') {
            $w[] = 'ℹ️ تعداد مشاهدات دو گروه جفتی باید برابر باشد.';
        }
        return $w;
    }

    // ═══ ذخیره تاریخچه ═══
    private function saveAnalysis(string $text, array $result): void
    {
        if (!$this->db) return;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stat_smart_analyses
                (user_id, input_text, detected_analysis_type, detected_test, confidence, all_scores, extracted_params, suggested_method)
                VALUES (?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                mb_substr($text, 0, 2000),
                $result['category'],
                $result['detected_test'],
                $result['confidence'],
                json_encode($result['all_scores'], JSON_UNESCAPED_UNICODE),
                json_encode($result['extracted_params'], JSON_UNESCAPED_UNICODE),
                json_encode($result['suggested'], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            error_log('StatLab Smart Save Error: ' . $e->getMessage());
        }
    }

    // ═══ نمونه‌های آماده ═══
    public static function samples(): array
    {
        return [
            ['id' => 1, 'label' => 'آزمون t یک نمونه', 'test' => 'one_sample_t',
             'text' => 'میانگین وزن بسته‌های یک محصول باید 500 گرم باشد. از خط تولید یک نمونه 25 تایی برداشته‌ایم و می‌خواهیم در سطح 0.05 آزمون کنیم آیا میانگین با 500 تفاوت معنادار دارد یا خیر.'],
            ['id' => 2, 'label' => 'آزمون t جفتی', 'test' => 'paired_t',
             'text' => 'نمره 20 کارآموز قبل و بعد از دوره آموزشی ثبت شده است. می‌خواهیم بدانیم آیا دوره اثر معنادار داشته است؟ داده‌ها جفتی و وابسته‌اند.'],
            ['id' => 3, 'label' => 'مقایسه دو گروه مستقل', 'test' => 'two_sample_t',
             'text' => 'می‌خواهیم میانگین تولید روزانه دو خط تولید مستقل A و B را مقایسه کنیم تا ببینیم تفاوت معنادار است یا نه.'],
            ['id' => 4, 'label' => 'آزمون نسبت', 'test' => 'one_prop_z',
             'text' => 'ادعا می‌شود درصد رضایت مشتریان 80٪ است. در نظرسنجی از 200 نفر، 150 نفر رضایت داشتند. آیا این نسبت با ادعا تفاوت معنادار دارد؟'],
            ['id' => 5, 'label' => 'کای‌دو استقلال', 'test' => 'chi2_indep',
             'text' => 'در یک جدول توافقی می‌خواهیم استقلال دو متغیر کیفی جنسیت و ترجیح محصول را با آزمون فرض بررسی کنیم.'],
            ['id' => 6, 'label' => 'داده غیرنرمال', 'test' => 'mann_whitney',
             'text' => 'داده‌های ما نرمال نیست و مقیاس رتبه‌ای دارد. می‌خواهیم دو گروه را با آزمون ناپارامتری مقایسه کنیم.'],
            ['id' => 7, 'label' => 'همبستگی', 'test' => 'correlation',
             'text' => 'می‌خواهیم شدت و جهت رابطه بین هزینه تبلیغات و میزان فروش را با همبستگی پیرسون بسنجیم.'],
            ['id' => 8, 'label' => 'رگرسیون/پیش‌بینی', 'test' => 'simple',
             'text' => 'می‌خواهیم میزان فروش را بر اساس هزینه تبلیغات پیش‌بینی کنیم و مدل رگرسیون بسازیم.'],
        ];
    }
}