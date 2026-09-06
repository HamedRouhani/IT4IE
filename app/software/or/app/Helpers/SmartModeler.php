<?php
namespace App\Software\Or\Helpers;

/**
 * SmartModeler - سیستم هوشمند تشخیص و مدلسازی مسائل OR
 * این کلاس متن فارسی کاربر را تحلیل کرده و نوع مسئله، مدل ریاضی و راه‌حل را پیشنهاد می‌دهد
 */
class SmartModeler
{
    private $db;
    private $patterns = [];
    private $samples = [];
    
    // ضرایب اهمیت دسته‌بندی‌ها
    private $categoryWeights = [
        'source'      => 1.2,  // کلمات مربوط به مبدأ/منبع
        'destination' => 1.2,  // کلمات مربوط به مقصد
        'cost'        => 1.3,  // کلمات مربوط به هزینه/فاصله
        'constraint'  => 1.1,  // کلمات مربوط به محدودیت
        'objective'   => 1.4,  // کلمات مربوط به هدف (بیشینه/کمینه)
        'general'     => 1.0,  // کلمات عمومی
    ];

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->loadPatterns();
        $this->loadSamples();
    }

    /**
     * بارگذاری الگوهای کلمات کلیدی از دیتابیس
     */
    private function loadPatterns()
    {
        try {
            if ($this->db) {
                $stmt = $this->db->query("SELECT * FROM or_smart_patterns");
                $this->patterns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $this->patterns = $this->getDefaultPatterns();
            }
        } catch (\Exception $e) {
            $this->patterns = $this->getDefaultPatterns();
        }
    }

    /**
     * بارگذاری نمونه‌های واقعی
     */
    private function loadSamples()
    {
        try {
            if ($this->db) {
                $stmt = $this->db->query("SELECT * FROM or_sample_problems WHERE is_active = 1");
                $this->samples = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $this->samples = [];
            }
        } catch (\Exception $e) {
            $this->samples = [];
        }
    }

    /**
     * الگوهای پیش‌فرض (در صورت عدم دسترسی به دیتابیس)
     */
    private function getDefaultPatterns()
    {
        return [
            // ====== حمل و نقل (TRANS) - کلمات خاص ======
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'حمل', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'نقل', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'توزیع', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'انبار', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'مبدأ', 'weight' => 5, 'category' => 'source'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'مقصد', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'عرضه', 'weight' => 5, 'category' => 'source'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'تقاضا', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'هزینه حمل', 'weight' => 6, 'category' => 'cost'],
            
            // ====== تخصیص (ASSIGN) - کلمات خاص ======
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'تخصیص', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'اختصاص', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'یک به یک', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'کارگر', 'weight' => 4, 'category' => 'source'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'وظیفه', 'weight' => 4, 'category' => 'destination'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'اپراتور', 'weight' => 4, 'category' => 'source'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'دستگاه', 'weight' => 4, 'category' => 'destination'],
            
            // ====== کوتاه‌ترین مسیر (SHORTEST) - کلمات خاص ======
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'مسیر', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'کوتاه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'فاصله', 'weight' => 5, 'category' => 'cost'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'شهر', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'جاده', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'گره', 'weight' => 4, 'category' => 'general'],
            
            // ====== برنامه‌ریزی خطی (LP) - کلمات خاص و قوی ======
            ['problem_type_code' => 'LP', 'keyword_fa' => 'برنامه‌ریزی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'خطی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'سود', 'weight' => 6, 'category' => 'objective'], // ✅ کلیدی!
            ['problem_type_code' => 'LP', 'keyword_fa' => 'بیشینه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'حداکثر', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'کمینه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'حداقل', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'محدودیت', 'weight' => 5, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'قیود', 'weight' => 5, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'منابع', 'weight' => 4, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'ظرفیت', 'weight' => 3, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'تابع هدف', 'weight' => 7, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'متغیر', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'تولید', 'weight' => 3, 'category' => 'general'],
            
            // ====== ترانشیپمنت (TRANSSHIP) - کلمات خاص ======
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'ترانشیپ', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'واسط', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'میانی', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'چند مرحله', 'weight' => 6, 'category' => 'general'],
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════
     * متد اصلی: تحلیل متن و تشخیص نوع مسئله
     * ═══════════════════════════════════════════════════════
     */
    public function analyze($text)
    {
        if (empty($text) || strlen(trim($text)) < 10) {
            return [
                'success' => false,
                'error' => 'متن وارد شده بسیار کوتاه است. لطفاً حداقل یک پاراگراف درباره مسئله خود بنویسید.',
            ];
        }

        // ۱. پاکسازی و نرمال‌سازی متن
        $cleanText = $this->normalizeText($text);

        // ۲. امتیازدهی به هر نوع مسئله
        $scores = $this->calculateScores($cleanText);

        // ۳. مرتب‌سازی بر اساس امتیاز
        arsort($scores);

        // ۴. تشخیص بهترین تطابق
        $topType = array_key_first($scores);
        $topScore = $scores[$topType];
        $totalScore = array_sum($scores);
        
        // محاسبه درصد اطمینان
        $confidence = $totalScore > 0 ? round(($topScore / $totalScore) * 100, 1) : 0;

        // ۵. استخراج پارامترها از متن
        $extractedParams = $this->extractParameters($cleanText, $topType);

        // ۶. یافتن نزدیک‌ترین نمونه واقعی
        $similarSample = $this->findSimilarSample($topType, $cleanText);

        // ۷. پیشنهاد روش حل
        $suggestedMethod = $this->suggestMethod($topType, $extractedParams);

        // ۸. تولید مدل ریاضی پیشنهادی
        $mathModel = $this->generateMathModel($topType, $extractedParams);

        // ۹. ذخیره در تاریخچه (اگر دیتابیس موجود باشد)
        $this->saveAnalysis($text, $topType, $confidence, $suggestedMethod, $extractedParams);

        return [
            'success' => true,
            'detected_type' => $topType,
            'detected_type_name' => $this->getTypeName($topType),
            'confidence' => $confidence,
            'all_scores' => $scores,
            'extracted_params' => $extractedParams,
            'suggested_method' => $suggestedMethod,
            'math_model' => $mathModel,
            'similar_sample' => $similarSample,
            'next_steps' => $this->getNextSteps($topType),
            'warnings' => $this->generateWarnings($topType, $extractedParams),
        ];
    }

    /**
     * نرمال‌سازی متن فارسی
     */
    private function normalizeText($text)
    {
        // ✅ آرایه صحیح و کامل تبدیل اعداد فارسی به انگلیسی
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        $text = str_replace($persianNumbers, $englishNumbers, $text);
        $text = str_replace(['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $englishNumbers, $text); // اعداد عربی
        
        // حذف کاراکترهای اضافی
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * ✅ پاکسازی نام گره/شهر از کلمات مزاحم و علائم نگارشی
     */
    private function cleanNodeName($name)
    {
        $name = trim($name);
        // حذف علائم نگارشی
        $name = preg_replace('/[،,.؛;:!؟?()«»\[\]{}"-]+/u', '', $name);
        // حذف پیشوند «شهر»
        $name = preg_replace('/^شهر/u', '', $name);
        // حذف افعال مزاحم انتهای نام («شیراز هستند» → «شیراز»)
        $name = preg_replace('/(?:هستند|است|می‌باشد|میباشد)$/u', '', $name);
        $name = trim($name);
        // حذف کلمات توقف
        $stop = ['به', 'از', 'تا', 'با', 'وزن', 'فاصله', 'هزینه', 'کیلومتر', 'کیلومتری',
                'مبدأ', 'مقصد', 'و', 'در', 'را', 'که', 'این', 'ها', 'یال', 'شبکه', 'پیدا'];
        if (in_array($name, $stop, true)) return '';
        return $name;
    }

    /**
     * محاسبه امتیاز هر نوع مسئله
     */
    private function calculateScores($text)
    {
        $scores = [
            'TRANS' => 0,
            'ASSIGN' => 0,
            'SHORTEST' => 0,
            'LP' => 0,
            'TRANSSHIP' => 0,
        ];

        // . امتیازدهی بر اساس کلمات کلیدی
        foreach ($this->patterns as $pattern) {
            $keyword = $pattern['keyword_fa'];
            $weight = (int)$pattern['weight'];
            $category = $pattern['category'] ?? 'general';
            $type = $pattern['problem_type_code'];

            $count = substr_count($text, $keyword);
            
            if ($count > 0) {
                $categoryMultiplier = $this->categoryWeights[$category] ?? 1.0;
                $scores[$type] += ($count * $weight * $categoryMultiplier);
            }
        }

        // ۲. ✅ تشخیص هوشمند ترکیبی (الگوهای خاص)
        
        // اگر "سود" + "محدودیت" یا "قیود" بود → قطعاً LP
        if (strpos($text, 'سود') !== false && (strpos($text, 'محدودیت') !== false || strpos($text, 'قیود') !== false)) {
            $scores['LP'] += 20; // امتیاز قوی
        }
        
        // اگر "بیشینه‌سازی" یا "حداکثر" + "سود" بود → LP با هدف maximize
        if ((strpos($text, 'بیشینه') !== false || strpos($text, 'حداکثر') !== false) && strpos($text, 'سود') !== false) {
            $scores['LP'] += 15;
        }
        
        // اگر "کمینه‌سازی" یا "حداقل" + "هزینه" بود → LP با هدف minimize
        if ((strpos($text, 'کمینه') !== false || strpos($text, 'حداقل') !== false) && strpos($text, 'هزینه') !== false) {
            $scores['LP'] += 15;
        }
        
        // اگر "مبدأ" + "مقصد" + "عرضه" + "تقاضا" بود → حمل و نقل
        if (strpos($text, 'مبدأ') !== false && strpos($text, 'مقصد') !== false && 
            strpos($text, 'عرضه') !== false && strpos($text, 'تقاضا') !== false) {
            $scores['TRANS'] += 25; // امتیاز خیلی قوی
        }
        
        // اگر "یک به یک" یا "اختصاص" + "کارگر/وظیفه" بود → تخصیص
        if ((strpos($text, 'یک به یک') !== false || strpos($text, 'اختصاص') !== false) &&
            (strpos($text, 'کارگر') !== false || strpos($text, 'وظیفه') !== false)) {
            $scores['ASSIGN'] += 20;
        }
        
        // اگر "کوتاه‌ترین مسیر" یا "فاصله" + "شهر/گره" بود → Shortest Path
        if ((strpos($text, 'کوتاه') !== false && strpos($text, 'مسیر') !== false) ||
            (strpos($text, 'فاصله') !== false && (strpos($text, 'شهر') !== false || strpos($text, 'گره') !== false))) {
            $scores['SHORTEST'] += 20;
        }
        
        // اگر "ترانشیپ" یا "واسط" + "چند مرحله" بود → Transshipment
        if ((strpos($text, 'ترانشیپ') !== false || strpos($text, 'واسط') !== false) &&
            strpos($text, 'چند مرحله') !== false) {
            $scores['TRANSSHIP'] += 25;
        }

        return $scores;
    }

    /**
     * استخراج پارامترها از متن (اعداد، نام‌ها، محدودیت‌ها)
     */
    private function extractParameters($text, $type)
    {
        $params = [
            'numbers' => [],
            'potential_sources' => [],
            'potential_destinations' => [],
            'objectives' => [],
            'constraints' => [],
            'model_data' => null,
        ];

        // تبدیل اعداد فارسی به انگلیسی
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $textForNumbers = str_replace($persianNumbers, $englishNumbers, $text);
        $textForNumbers = str_replace(['٠','١','٢','٣','٤','٥','٦','','٨','٩'], $englishNumbers, $textForNumbers);
        
        // استخراج تمام اعداد به ترتیب
        preg_match_all('/\d+(?:\.\d+)?/', $textForNumbers, $matches);
        $params['numbers'] = array_map('floatval', $matches[0]);
        $n = $params['numbers'];

        // تشخیص مفاهیم کلیدی
        if (strpos($text, 'کارخانه') !== false || strpos($text, 'مبدأ') !== false) $params['potential_sources'][] = 'کارخانه/مبدأ';
        if (strpos($text, 'انبار') !== false || strpos($text, 'مقصد') !== false) $params['potential_destinations'][] = 'انبار/مقصد';
        if (strpos($text, 'کارگر') !== false || strpos($text, 'اپراتور') !== false || strpos($text, 'عامل') !== false) $params['potential_sources'][] = 'نیروی کار';
        if (strpos($text, 'وظیفه') !== false || strpos($text, 'دستگاه') !== false || strpos($text, 'پروژه') !== false) $params['potential_destinations'][] = 'وظیفه/دستگاه';
        
        if (strpos($text, 'حداکثر') !== false || strpos($text, 'بیشینه') !== false || strpos($text, 'سود') !== false) {
            $params['objectives'][] = 'maximize';
        }
        if (strpos($text, 'حداقل') !== false || strpos($text, 'کمینه') !== false || strpos($text, 'هزینه') !== false) {
            $params['objectives'][] = 'minimize';
        }
        if (strpos($text, 'محدودیت') !== false || strpos($text, 'ظرفیت') !== false || strpos($text, 'قیود') !== false) {
            $params['constraints'][] = 'capacity_limit';
        }

        // =====================================================
        // تولید خودکار model_data بر اساس نوع مسئله
        // =====================================================
        
        if ($type === 'LP') {
            $numVariables = 2;
            $numConstraints = 2;
            
            if (preg_match('/(\d+)\s*(?:متغیر|محصول|کالا)/u', $text, $matches)) {
                $numVariables = (int)$matches[1];
            }
            if (preg_match('/(\d+)\s*(?:محدودیت|قید|منبع|ساعت)/u', $text, $matches)) {
                $numConstraints = (int)$matches[1];
            }
            
            // ✅ تشخیص هوشمند نوع محدودیت‌ها
            $defaultConstraintType = '<=';
            if (preg_match('/حداکثر|نباید\s+بیشتر\s+از|کمتر\s+مساوی|حداکثر\s+مصرف|حداکثر\s+تولید|حداکثر\s+ظرفیت|نمی‌تواند\s+بیشتر/u', $text)) {
                $defaultConstraintType = '<=';
            } elseif (preg_match('/حداقل|نباید\s+کمتر\s+از|بیشتر\s+مساوی|حداقل\s+مصرف|حداقل\s+تولید|حداقل\s+ظرفیت|نمی‌تواند\s+کمتر/u', $text)) {
                $defaultConstraintType = '>=';
            }
            
            $variables = [];
            for ($i = 0; $i < $numVariables; $i++) {
                $variables[] = [
                    'name' => "متغیر " . ($i + 1),
                    'coeff' => $n[$i] ?? (50 + $i * 20)
                ];
            }
            
            $constraints = [];
            $constraintStartIdx = $numVariables;
            for ($i = 0; $i < $numConstraints; $i++) {
                $coeffs = [];
                for ($j = 0; $j < $numVariables; $j++) {
                    $coeffs[] = $n[$constraintStartIdx + ($i * $numVariables) + $j] ?? (1 + $j);
                }
                $capacityIdx = $constraintStartIdx + ($numConstraints * $numVariables) + $i;
                $constraints[] = [
                    'name' => "محدودیت " . ($i + 1),
                    'coeffs' => $coeffs,
                    'capacity' => $n[$capacityIdx] ?? (60 + $i * 20),
                    'type' => $defaultConstraintType // ← اضافه شد
                ];
            }
            
            $params['model_data'] = [
                'name' => 'پروژه برنامه‌ریزی خطی (استخراج هوشمند)',
                'description' => $text,
                'objective' => in_array('maximize', $params['objectives']) ? 'maximize' : 'minimize',
                'variables' => $variables,
                'constraints' => $constraints
            ];
        } 
        elseif ($type === 'TRANS') {
            $numSources = 2;
            $numDestinations = 2;
            
            if (preg_match('/(\d+)\s*(?:کارخانه|مبدأ|منبع)/u', $text, $matches)) {
                $numSources = (int)$matches[1];
            }
            if (preg_match('/(\d+)\s*(?:انبار|مقصد|مشتری)/u', $text, $matches)) {
                $numDestinations = (int)$matches[1];
            }
            
            // ساخت مبدأها (ایندکس 0 تعداد است، ظرفیت‌ها از 1 شروع می‌شوند)
            $sources = [];
            for ($i = 0; $i < $numSources; $i++) {
                $sources[] = [
                    'name' => "مبدأ " . ($i + 1),
                    'capacity' => $n[1 + $i] ?? 100
                ];
            }
            
            // ساخت مقصدها (بعد از ظرفیت‌ها و عدد "تعداد انبار")
            $demandStartIdx = $numSources + 2;
            $destinations = [];
            for ($j = 0; $j < $numDestinations; $j++) {
                $destinations[] = [
                    'name' => "مقصد " . ($j + 1),
                    'demand' => $n[$demandStartIdx + $j] ?? 100
                ];
            }
            
            // ساخت ماتریس هزینه
            $costStartIdx = $demandStartIdx + $numDestinations;
            $costMatrix = [];
            for ($i = 0; $i < $numSources; $i++) {
                $row = [];
                for ($j = 0; $j < $numDestinations; $j++) {
                    $costIdx = $costStartIdx + ($i * $numDestinations) + $j;
                    $row[] = $n[$costIdx] ?? (5 + $i + $j);
                }
                $costMatrix[] = $row;
            }
            
            $params['model_data'] = [
                'name' => 'پروژه حمل و نقل (استخراج هوشمند)',
                'description' => $text,
                'sources' => $sources,
                'destinations' => $destinations,
                'cost_matrix' => $costMatrix
            ];
        }
        elseif ($type === 'ASSIGN') {
            $numAgents = 2;
            $numTasks = 2;
            
            if (preg_match('/(\d+)\s*(?:کارگر|اپراتور|عامل|نیرو)/u', $text, $matches)) {
                $numAgents = (int)$matches[1];
            }
            if (preg_match('/(\d+)\s*(?:وظیفه|دستگاه|پروژه)/u', $text, $matches)) {
                $numTasks = (int)$matches[1];
            }
            
            // ساخت عوامل
            $agents = [];
            for ($i = 0; $i < $numAgents; $i++) {
                $agents[] = ['name' => "عامل " . ($i + 1)];
            }
            
            // ساخت وظایف
            $tasks = [];
            for ($j = 0; $j < $numTasks; $j++) {
                $tasks[] = ['name' => "وظیفه " . ($j + 1)];
            }
            
            // ساخت ماتریس هزینه/زمان
            $costMatrix = [];
            for ($i = 0; $i < $numAgents; $i++) {
                $row = [];
                for ($j = 0; $j < $numTasks; $j++) {
                    $costIdx = ($i * $numTasks) + $j;
                    $row[] = $n[$costIdx] ?? (5 + $i + $j);
                }
                $costMatrix[] = $row;
            }
            
            $params['model_data'] = [
                'name' => 'پروژه تخصیص (استخراج هوشمند)',
                'description' => $text,
                'agents' => $agents,
                'tasks' => $tasks,
                'cost_matrix' => $costMatrix
            ];
        } 
        elseif ($type === 'SHORTEST') {
            // ۱. استخراج یال‌ها با الگوی «X به Y عدد» یا «X تا Y با وزن عدد»
            $edgePattern = '/(?:از\s+)?([\p{Arabic}\x{200C}a-zA-Z]+)\s+(?:به|تا)\s+([\p{Arabic}\x{200C}a-zA-Z]+)(?:\s+(?:با\s+)?(?:وزن|فاصله|هزینه|طول))?\s+(\d+(?:\.\d+)?)/u';
            preg_match_all($edgePattern, $textForNumbers, $edgeMatches, PREG_SET_ORDER);

            // ۲. ساخت فهرست گره‌ها از نام‌های داخل یال‌ها
            $nodeNames = [];
            $rawEdges  = [];
            foreach ($edgeMatches as $m) {
                $from = $this->cleanNodeName($m[1]);
                $to   = $this->cleanNodeName($m[2]);
                $w    = (float)$m[3];
                if (mb_strlen($from) < 2 || mb_strlen($to) < 2) continue;
                if (!in_array($from, $nodeNames, true)) $nodeNames[] = $from;
                if (!in_array($to,   $nodeNames, true)) $nodeNames[] = $to;
                $rawEdges[] = ['from' => $from, 'to' => $to, 'weight' => $w];
            }

            // ۳. افزودن شهرهایی که به صورت لیست آمده‌اند («شامل قم، اصفهان و شیراز»)
            if (preg_match('/شامل\s+([^\.!؟?؛;]+)/u', $text, $listMatch)) {
                $parts = preg_split('/[،,]+|\s+و\s+/u', $listMatch[1]);
                foreach ($parts as $p) {
                    $name = $this->cleanNodeName($p);
                    if (mb_strlen($name) >= 2 && !in_array($name, $nodeNames, true)) {
                        $nodeNames[] = $name;
                    }
                }
            }

            // ۴. fallback: اگر هیچ یالی پیدا نشد، گره‌های عددی بساز
            if (empty($nodeNames)) {
                $numNodes = 3;
                if (preg_match('/(\d+)\s*(?:شهر|گره|نقطه)/u', $text, $mm)) {
                    $numNodes = max(2, (int)$mm[1]);
                }
                for ($i = 0; $i < $numNodes; $i++) {
                    $nodeNames[] = "گره " . ($i + 1);
                }
            }

            $nodes = [];
            foreach ($nodeNames as $name) {
                $nodes[] = ['name' => $name];
            }

            // ۵. تبدیل یال‌ها به ایندکس گره‌ها
            $edges = [];
            foreach ($rawEdges as $re) {
                $fromIdx = array_search($re['from'], $nodeNames, true);
                $toIdx   = array_search($re['to'],   $nodeNames, true);
                if ($fromIdx !== false && $toIdx !== false) {
                    $edges[] = ['from' => $fromIdx, 'to' => $toIdx, 'weight' => $re['weight']];
                }
            }

            // ۶. fallback یال‌ها: زنجیره خطی با اعداد متن
            if (empty($edges) && count($nodes) > 1 && count($n) >= 1) {
                for ($i = 0; $i < count($nodes) - 1; $i++) {
                    $edges[] = [
                        'from'   => $i,
                        'to'     => $i + 1,
                        'weight' => $n[$i] ?? (10 + $i * 10),
                    ];
                }
            }

            $params['model_data'] = [
                'name'        => 'پروژه کوتاه‌ترین مسیر (استخراج هوشمند)',
                'description' => mb_substr($text, 0, 2000),
                'nodes'       => $nodes,
                'edges'       => $edges,
            ];
        }
        elseif ($type === 'TRANSSHIP') {
            // مشابه حمل و نقل اما با گره‌های واسط
            $numSources = 2;
            $numTransshipment = 1;
            $numDestinations = 2;
            
            if (preg_match('/(\d+)\s*(?:کارخانه|مبدأ)/u', $text, $matches)) {
                $numSources = (int)$matches[1];
            }
            if (preg_match('/(\d+)\s*(?:واسط|میانی|مرکز\s+توزیع)/u', $text, $matches)) {
                $numTransshipment = (int)$matches[1];
            }
            if (preg_match('/(\d+)\s*(?:انبار|مقصد)/u', $text, $matches)) {
                $numDestinations = (int)$matches[1];
            }
            
            $params['model_data'] = [
                'name' => 'پروژه ترانشیپمنت (استخراج هوشمند)',
                'description' => $text,
                'sources' => array_map(fn($i) => ['name' => "مبدأ " . ($i + 1), 'capacity' => 100], range(0, $numSources - 1)),
                'transshipment' => array_map(fn($i) => ['name' => "گره واسط " . ($i + 1)], range(0, $numTransshipment - 1)),
                'destinations' => array_map(fn($i) => ['name' => "مقصد " . ($i + 1), 'demand' => 100], range(0, $numDestinations - 1))
            ];
        }

        return $params;
    }

    /**
     * یافتن نمونه مشابه
     */
    private function findSimilarSample($type, $text)
    {
        $candidates = array_filter($this->samples, function($s) use ($type) {
            return $s['problem_type_code'] === $type;
        });

        if (empty($candidates)) return null;

        // ساده‌ترین انتخاب: اولین نمونه از همان نوع
        return array_values($candidates)[0];
    }

    /**
     * پیشنهاد روش حل بر اساس نوع مسئله و پارامترها
     */
    private function suggestMethod($type, $params)
    {
        $methods = [
            'TRANS' => [
                'primary' => ['code' => 'VAM', 'name' => 'تقریب ووگل (VAM)', 'reason' => 'دقیق‌ترین روش اولیه برای مسائل حمل‌ونقل'],
                'alternative' => ['code' => 'NWC', 'name' => 'گوشه شمال غربی', 'reason' => 'ساده‌ترین روش برای شروع'],
                'optimization' => ['code' => 'MODI', 'name' => 'MODI (u-v)', 'reason' => 'برای بهینه‌سازی جواب اولیه'],
            ],
            'ASSIGN' => [
                'primary' => ['code' => 'HUNGARIAN', 'name' => 'الگوریتم مجارستانی', 'reason' => 'بهترین روش برای مسائل تخصیص'],
            ],
            'SHORTEST' => [
                'primary' => ['code' => 'DIJKSTRA', 'name' => 'Dijkstra', 'reason' => 'برای گراف با وزن‌های نامنفی'],
                'alternative' => ['code' => 'FLOYD', 'name' => 'Floyd-Warshall', 'reason' => 'برای کوتاه‌ترین مسیر بین همه جفت‌گره‌ها'],
            ],
            'LP' => [
                'primary' => ['code' => 'SIMPLEX', 'name' => 'سیمپلکس', 'reason' => 'روش استاندارد برای LP'],
                'alternative' => ['code' => 'BIG_M', 'name' => 'Big-M', 'reason' => 'اگر قیود نامساوی ≥ دارید'],
            ],
            'TRANSSHIP' => [
                'primary' => ['code' => 'MIN_COST_FLOW', 'name' => 'Minimum Cost Flow', 'reason' => 'بهترین روش برای مسائل چندمرحله‌ای'],
            ],
        ];

        return $methods[$type] ?? null;
    }

    /**
     * تولید مدل ریاضی پیشنهادی
     */
    private function generateMathModel($type, $params)
    {
        $models = [
            'TRANS' => [
                'title' => 'مدل ریاضی مسئله حمل‌ونقل',
                'variables' => 'xᵢⱼ = مقدار حمل از مبدأ i به مقصد j',
                'objective' => 'Min Z = ΣΣ cᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'Σⱼ xᵢⱼ = supplyᵢ  (برای هر مبدأ i)',
                    'Σᵢ xᵢⱼ = demandⱼ  (برای هر مقصد j)',
                    'xᵢⱼ ≥ 0',
                ],
                'explanation' => 'هدف کمینه‌سازی کل هزینه حمل با رعایت محدودیت‌های عرضه و تقاضا است.',
            ],
            'ASSIGN' => [
                'title' => 'مدل ریاضی مسئله تخصیص',
                'variables' => 'xᵢⱼ = 1 اگر عامل i به وظیفه j اختصاص یابد، در غیر این صورت 0',
                'objective' => 'Min Z = ΣΣ cᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'Σⱼ xᵢⱼ = 1  (هر عامل دقیقاً به یک وظیفه)',
                    'Σᵢ xᵢⱼ = 1  (هر وظیفه دقیقاً به یک عامل)',
                    'xᵢⱼ ∈ {0, 1}',
                ],
                'explanation' => 'مسئله تخصیص یک حالت خاص از حمل‌ونقل است که در آن عرضه و تقاضا همگی برابر 1 هستند.', // ✅ علامت => اینجا اضافه شد
            ],
            'SHORTEST' => [
                'title' => 'مدل ریاضی کوتاه‌ترین مسیر',
                'variables' => 'xᵢⱼ = 1 اگر یال (i,j) در مسیر باشد، در غیر این صورت 0',
                'objective' => 'Min Z = ΣΣ dᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'Σⱼ xᵢⱼ - Σₖ xₖᵢ = 1  (برای مبدأ)',
                    'Σⱼ xᵢⱼ - Σₖ xₖᵢ = -1  (برای مقصد)',
                    'Σⱼ xᵢⱼ - Σₖ xₖᵢ = 0  (برای گره‌های میانی)',
                ],
                'explanation' => 'جریان خالص در مبدأ 1+، در مقصد 1- و در سایر گره‌ها 0 است.',
            ],
            'LP' => [
                'title' => 'مدل ریاضی برنامه‌ریزی خطی',
                'variables' => 'xⱼ = مقدار تولید/تصمیم برای متغیر j',
                'objective' => 'Max/Min Z = Σ cⱼ × xⱼ',
                'constraints' => [
                    'Σ aᵢⱼ × xⱼ ≤ bᵢ  (برای هر محدودیت i)',
                    'xⱼ ≥ 0',
                ],
                'explanation' => 'تابع هدف خطی با قیود خطی. ناحیه موجه یک چندوجهی محدب است.',
            ],
            'TRANSSHIP' => [
                'title' => 'مدل ریاضی ترانشیپمنت',
                'variables' => 'xᵢⱼ = مقدار جریان از گره i به گره j',
                'objective' => 'Min Z = ΣΣ cᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'جریان خروجی - جریان ورودی = supply (برای مبادی)',
                    'جریان خروجی - جریان ورودی = -demand (برای مقاصد)',
                    'جریان خروجی = جریان ورودی (برای گره‌های واسط)',
                ],
                'explanation' => 'تعادل جریان در هر گره باید برقرار باشد.',
            ],
        ];

        return $models[$type] ?? null;
    }

    /**
     * مراحل بعدی پیشنهادی
     */
    private function getNextSteps($type)
    {
        $steps = [
            'TRANS' => [
                '۱. تعداد مبادی (منابع) و مقاصد را مشخص کنید',
                '۲. ظرفیت عرضه هر مبدأ و تقاضای هر مقصد را تعیین کنید',
                '۳. ماتریس هزینه حمل را تکمیل کنید',
                '. بررسی توازن مسئله (عرضه کل = تقاضای کل)',
                '۵. انتخاب روش حل (VAM + MODI پیشنهاد می‌شود)',
            ],
            'ASSIGN' => [
                '. لیست عوامل (کارگران/ماشین‌ها) را مشخص کنید',
                '۲. لیست وظایف (پروژه‌ها) را مشخص کنید',
                '. ماتریس هزینه/زمان را تکمیل کنید',
                '۴. بررسی مربعی بودن ماتریس (در صورت نیاز، Dummy اضافه کنید)',
                '. اجرای الگوریتم مجارستانی',
            ],
            'SHORTEST' => [
                '۱. گره‌ها (شهرها/نقاط) را مشخص کنید',
                '۲. یال‌ها (جاده‌ها/اتصالات) و وزن آن‌ها را وارد کنید',
                '۳. مبدأ و مقصد نهایی را تعیین کنید',
                '۴. انتخاب الگوریتم (Dijkstra برای یک مبدأ، Floyd برای همه جفت‌ها)',
                '۵. بررسی وجود چرخه منفی (در صورت وجود، Bellman-Ford استفاده شود)',
            ],
            'LP' => [
                '۱. متغیرهای تصمیم را تعریف کنید',
                '. تابع هدف (بیشینه‌سازی سود یا کمینه‌سازی هزینه) را بنویسید',
                '۳. قیود (محدودیت‌های منابع) را مشخص کنید',
                '۴. شرایط غیرمنفی بودن متغیرها را اضافه کنید',
                '۵. انتخاب روش حل (سیمپلکس، Big-M یا دو مرحله‌ای)',
            ],
            'TRANSSHIP' => [
                '۱. مبادی، گره‌های واسط و مقاصد را مشخص کنید',
                '۲. ظرفیت هر گره را تعیین کنید',
                '۳. هزینه حمل بین هر جفت گره را وارد کنید',
                '۴. تبدیل به مدل حمل‌ونقل استاندارد',
                '۵. حل با روش کمینه هزینه جریان',
            ],
        ];

        return $steps[$type] ?? [];
    }

    /**
     * تولید هشدارها
     */
    private function generateWarnings($type, $params)
    {
        $warnings = [];

        if ($type === 'TRANS') {
            if (empty($params['potential_sources'])) {
                $warnings[] = 'منابع (مبادی) در متن شناسایی نشدند. لطفاً کارخانه‌ها یا مراکز تأمین را مشخص کنید.';
            }
            if (empty($params['potential_destinations'])) {
                $warnings[] = 'مقاصد در متن شناسایی نشدند. لطفاً انبارها یا مشتریان را مشخص کنید.';
            }
        }

        if ($type === 'ASSIGN') {
            $warnings[] = 'برای مسئله تخصیص، تعداد عوامل و وظایف باید برابر باشد (ماتریس مربعی).';
        }

        if ($type === 'SHORTEST') {
            if (in_array('minimize', $params['objectives']) || in_array('minimize_cost', $params['objectives'])) {
                // خوب است
            } else {
                $warnings[] = 'هدف معمولاً کمینه‌سازی فاصله/هزینه است. مطمئن شوید هدف را مشخص کرده‌اید.';
            }
        }

        if ($type === 'LP') {
            if (count($params['constraints']) === 0) {
                $warnings[] = 'هیچ محدودیتی در متن شناسایی نشد. LP بدون قید معنا ندارد.';
            }
        }

        return $warnings;
    }

    /**
     * ذخیره تحلیل در تاریخچه
     */
    private function saveAnalysis($text, $type, $confidence, $method, $params)
    {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO or_smart_analyses 
                (user_id, input_text, detected_type, confidence, suggested_method, extracted_params) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                mb_substr($text, 0, 500),
                $type,
                $confidence,
                $method['primary']['code'] ?? null,
                json_encode($params, JSON_UNESCAPED_UNICODE)
            ]);
        } catch (\Exception $e) {
            error_log("SmartModeler Save Error: " . $e->getMessage());
        }
    }

    /**
     * دریافت نام فارسی نوع مسئله
     */
    private function getTypeName($code)
    {
        $names = [
            'TRANS' => 'حمل و نقل (Transportation)',
            'ASSIGN' => 'تخصیص (Assignment)',
            'SHORTEST' => 'کوتاه‌ترین مسیر (Shortest Path)',
            'LP' => 'برنامه‌ریزی خطی (Linear Programming)',
            'TRANSSHIP' => 'ترانشیپمنت (Transshipment)',
        ];
        return $names[$code] ?? 'نامشخص';
    }

    /**
     * دریافت همه نمونه‌های واقعی
     */
    public function getSamples($type = null)
    {
        if ($type) {
            return array_filter($this->samples, function($s) use ($type) {
                return $s['problem_type_code'] === $type;
            });
        }
        return $this->samples;
    }
}