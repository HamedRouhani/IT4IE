<?php
namespace App\Software\Or\Helpers;

/**
 * ═══════════════════════════════════════════════════════════════
 * SmartModeler v2.0 - سیستم هوشمند تشخیص و مدلسازی مسائل OR
 * ═══════════════════════════════════════════════════════════════
 * 
 * این کلاس متن فارسی کاربر را تحلیل کرده و نوع مسئله، مدل ریاضی و راه‌حل را پیشنهاد می‌دهد
 * 
 * پشتیبانی از ۱۱ نوع مسئله:
 * 1. Transport (حمل و نقل)
 * 2. Assignment (تخصیص)
 * 3. Shortest Path (کوتاه‌ترین مسیر)
 * 4. LP (برنامه‌ریزی خطی)
 * 5. ILP (برنامه‌ریزی صحیح)
 * 6. Transshipment (ترانشیپمنت)
 * 7. Queueing (صف)
 * 8. Markov (زنجیره مارکوف)
 * 9. Game Theory (نظریه بازی‌ها)
 * 10. Monte Carlo (شبیه‌سازی مونت‌کارلو)
 * 11. Dual (نظریه دوگان)
 * 
 * @version 2.0.0
 */
class SmartModeler
{
    private $db;
    private $preprocessor;
    private $patterns = [];
    private $samples = [];
    
    /**
     * انواع مسئله پشتیبانی‌شده
     */
    private const PROBLEM_TYPES = [
        'TRANS'       => 'حمل و نقل',
        'ASSIGN'      => 'تخصیص',
        'SHORTEST'    => 'کوتاه‌ترین مسیر',
        'LP'          => 'برنامه‌ریزی خطی',
        'ILP'         => 'برنامه‌ریزی صحیح',
        'TRANSSHIP'   => 'ترانشیپمنت',
        'QUEUEING'    => 'صف (Queueing)',
        'MARKOV'      => 'زنجیره مارکوف',
        'GAME_THEORY' => 'نظریه بازی‌ها',
        'MONTE_CARLO' => 'شبیه‌سازی مونت‌کارلو',
        'DUAL'        => 'نظریه دوگان',
    ];

    /**
     * ═══════════════════════════════════════════════════════
     * لایه تشخیصی: الگوهای یکتا (Discriminative Patterns)
     * هر الگو فقط و فقط متعلق به یک نوع مسئله است
     * ═══════════════════════════════════════════════════════
     */
    private const DISCRIMINATIVE = [
        'ILP' => [
            ['pattern' => '/عدد\s+صحیح|اعداد\s+صحیح/u',                     'weight' => 60],
            ['pattern' => '/متغیر(?:های)?\s+صحیح/u',                         'weight' => 60],
            ['pattern' => '/صحیح\s+(?:باشد|باشند|است|هستند)/u',              'weight' => 50],
            ['pattern' => '/باینری|صفر\s+و\s+یک|دودویی/u',                   'weight' => 60],
            ['pattern' => '/integer|\bILP\b/ui',                             'weight' => 60],
            ['pattern' => '/گسسته/u',                                        'weight' => 40],
            ['pattern' => '/شاخه\s+و\s+کران|branch\s*(?:and|&)\s*bound/ui',  'weight' => 60],
            ['pattern' => '/کوله\s*پشتی/u',                                  'weight' => 50],
        ],
        'DUAL' => [
            ['pattern' => '/دوگان|دوگانی/u',                                 'weight' => 60],
            ['pattern' => '/قیمت\s+سایه|shadow\s*price/ui',                  'weight' => 60],
            ['pattern' => '/مسئله\s+اصلی\s+و\s+دوگان/u',                     'weight' => 70],
            ['pattern' => '/قضیه\s+دوگانگی|نظریه\s+دوگانگی/u',                'weight' => 60],
        ],
        'GAME_THEORY' => [
            ['pattern' => '/تعادل\s+نش|نقطه\s+زینی|نقطه\s+زینه/u',           'weight' => 70],
            ['pattern' => '/ماتریس\s+پرداخت/u',                              'weight' => 60],
            ['pattern' => '/بازیکن/u',                                       'weight' => 50],
            ['pattern' => '/استراتژی/u',                                     'weight' => 40],
            ['pattern' => '/حاصل\s*جمع\s+صفر|zero[\s-]*sum/ui',              'weight' => 60],
            ['pattern' => '/ارزش\s+بازی/u',                                  'weight' => 50],
            ['pattern' => '/رقیب|حریف/u',                                    'weight' => 30],
        ],
        'QUEUEING' => [
            ['pattern' => '/λ|μ|لامبدا/u',                                   'weight' => 60],
            ['pattern' => '/نرخ\s+ورود/u',                                   'weight' => 50],
            ['pattern' => '/نرخ\s+خدمت|نرخ\s+سرویس/u',                       'weight' => 50],
            ['pattern' => '/(?<![\p{Arabic}])صف(?![\p{Arabic}])/u',          'weight' => 30],
            ['pattern' => '/M\/M\/\d|M\/G\/1|M\/D\/1/u',                     'weight' => 70],
        ],
        'MARKOV' => [
            ['pattern' => '/مارکوف/u',                                       'weight' => 70],
            ['pattern' => '/ماتریس\s+انتقال/u',                              'weight' => 60],
            ['pattern' => '/احتمال\s+گذار/u',                                'weight' => 60],
            ['pattern' => '/حالت\s+پایدار|توزیع\s+پایدار/u',                 'weight' => 50],
            ['pattern' => '/نیمه\s*خراب/u',                                  'weight' => 40],
        ],
        'MONTE_CARLO' => [
            ['pattern' => '/مونت\s*کارلو|monte\s*carlo/ui',                  'weight' => 70],
            ['pattern' => '/توزیع\s+(?:نرمال|یکنواخت|مثلثی|نمایی|لگاریتمی)/u','weight' => 50],
            ['pattern' => '/شبیه\s*سازی\s+تصادفی/u',                         'weight' => 50],
            ['pattern' => '/انحراف\s+معیار/u',                               'weight' => 30],
        ],
        'TRANSSHIP' => [
            ['pattern' => '/ترانشیپ/u',                                      'weight' => 70],
            ['pattern' => '/گره\s+واسط|مرکز\s+واسط/u',                       'weight' => 60],
            ['pattern' => '/چند\s+مرحله/u',                                  'weight' => 40],
        ],
        'SHORTEST' => [
            ['pattern' => '/کوتاه\s*ترین\s+مسیر/u',                          'weight' => 60],
            ['pattern' => '/فاصله(?:\s+بین)?\s+شهر/u',                       'weight' => 50],
            ['pattern' => '/(?<![\p{Arabic}])یال(?![\p{Arabic}])/u',         'weight' => 30],
            ['pattern' => '/دایجسترا|dijkstra|floyd/ui',                     'weight' => 60],
        ],
        'ASSIGN' => [
            ['pattern' => '/یک\s+به\s+یک/u',                                 'weight' => 60],
            ['pattern' => '/تخصیص\s+بهینه|اختصاص\s+یاب/u',                   'weight' => 50],
            ['pattern' => '/مجارستانی|hungarian/ui',                         'weight' => 70],
            ['pattern' => '/اپراتور/u',                                      'weight' => 40],
        ],
        'TRANS' => [
            ['pattern' => '/عرضه.*تقاضا/su',                                 'weight' => 60],
            ['pattern' => '/مبدأ.*مقصد/su',                                  'weight' => 50],
            ['pattern' => '/هزینه\s+حمل/u',                                  'weight' => 50],
            ['pattern' => '/کارخانه.*انبار/su',                              'weight' => 50],
        ],
    ];
    
    /**
     * ضرایب اهمیت دسته‌بندی‌ها
     */
    private $categoryWeights = [
        'source'      => 1.2,
        'destination' => 1.2,
        'cost'        => 1.3,
        'constraint'  => 1.1,
        'objective'   => 1.4,
        'general'     => 1.0,
    ];

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->preprocessor = new PersianTextPreprocessor();
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

        foreach ($this->patterns as &$p) {
            $p['keyword_fa'] = $this->preprocessor->normalize($p['keyword_fa']);
        }
        unset($p);
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
            // ====== حمل و نقل (TRANS) ======
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'حمل', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'نقل', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'توزیع', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'انبار', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'مبدأ', 'weight' => 5, 'category' => 'source'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'مقصد', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'عرضه', 'weight' => 5, 'category' => 'source'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'تقاضا', 'weight' => 5, 'category' => 'destination'],
            ['problem_type_code' => 'TRANS', 'keyword_fa' => 'هزینه حمل', 'weight' => 6, 'category' => 'cost'],
            
            // ====== تخصیص (ASSIGN) ======
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'تخصیص', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'اختصاص', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'یک به یک', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'کارگر', 'weight' => 4, 'category' => 'source'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'وظیفه', 'weight' => 4, 'category' => 'destination'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'اپراتور', 'weight' => 4, 'category' => 'source'],
            ['problem_type_code' => 'ASSIGN', 'keyword_fa' => 'دستگاه', 'weight' => 4, 'category' => 'destination'],
            
            // ====== کوتاه‌ترین مسیر (SHORTEST) ======
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'مسیر', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'کوتاه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'فاصله', 'weight' => 5, 'category' => 'cost'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'شهر', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'جاده', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'SHORTEST', 'keyword_fa' => 'گره', 'weight' => 4, 'category' => 'general'],
            
            // ====== برنامه‌ریزی خطی (LP) ======
            ['problem_type_code' => 'LP', 'keyword_fa' => 'برنامه‌ریزی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'خطی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'سود', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'بیشینه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'حداکثر', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'کمینه', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'حداقل', 'weight' => 6, 'category' => 'objective'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'محدودیت', 'weight' => 5, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'قیود', 'weight' => 5, 'category' => 'constraint'],
            ['problem_type_code' => 'LP', 'keyword_fa' => 'تابع هدف', 'weight' => 7, 'category' => 'objective'],
            
            // ====== برنامه‌ریزی صحیح (ILP) ======
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'صحیح', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'عددی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'باینری', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'صفر و یک', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'integer', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'ILP', 'keyword_fa' => 'تعداد', 'weight' => 4, 'category' => 'general'],
            
            // ====== ترانشیپمنت (TRANSSHIP) ======
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'ترانشیپ', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'واسط', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'میانی', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'TRANSSHIP', 'keyword_fa' => 'چند مرحله', 'weight' => 6, 'category' => 'general'],
            
            // ====== صف (QUEUEING) ======
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'صف', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'انتظار', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'نوبت', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'مشتری', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'سرور', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'پنجره', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'نرخ ورود', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'نرخ خدمت', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'لامبدا', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'QUEUEING', 'keyword_fa' => 'مو', 'weight' => 5, 'category' => 'general'],
            
            // ====== زنجیره مارکوف (MARKOV) ======
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'مارکوف', 'weight' => 8, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'حالت', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'گذار', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'انتقال', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'سالم', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'خراب', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'احتمال', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'MARKOV', 'keyword_fa' => 'پایدار', 'weight' => 5, 'category' => 'general'],
            
            // ====== نظریه بازی‌ها (GAME_THEORY) ======
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'بازی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'بازیکن', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'استراتژی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'پرداخت', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'نش', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'تعادل', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'GAME_THEORY', 'keyword_fa' => 'رقابت', 'weight' => 4, 'category' => 'general'],
            
            // ====== شبیه‌سازی مونت‌کارلو (MONTE_CARLO) ======
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'مونت', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'کارلو', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'شبیه‌سازی', 'weight' => 6, 'category' => 'general'],
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'تصادفی', 'weight' => 5, 'category' => 'general'],
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'تکرار', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'MONTE_CARLO', 'keyword_fa' => 'توزیع', 'weight' => 5, 'category' => 'general'],
            
            // ====== نظریه دوگان (DUAL) ======
            ['problem_type_code' => 'DUAL', 'keyword_fa' => 'دوگان', 'weight' => 8, 'category' => 'general'],
            ['problem_type_code' => 'DUAL', 'keyword_fa' => 'اصلی', 'weight' => 4, 'category' => 'general'],
            ['problem_type_code' => 'DUAL', 'keyword_fa' => 'dual', 'weight' => 7, 'category' => 'general'],
            ['problem_type_code' => 'DUAL', 'keyword_fa' => 'متغیر دوگان', 'weight' => 7, 'category' => 'general'],
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

        // ۱. پاکسازی و نرمال‌سازی متن با PersianTextPreprocessor
        $cleanText = $this->preprocessor->normalize($text);
        
        // ۲. استخراج داده‌های ساختاری
        $extractedData = $this->extractStructuralData($cleanText);

        // ۳. امتیازدهی به هر نوع مسئله
        $scores = $this->calculateScores($cleanText, $extractedData);

        // ۴. مرتب‌سازی بر اساس امتیاز
        arsort($scores);

        // ۵. تشخیص بهترین تطابق
        $topType = array_key_first($scores);
        $topScore = $scores[$topType];
        $totalScore = array_sum($scores);
        
        // محاسبه درصد اطمینان با margin
        $confidence = $totalScore > 0 ? round(($topScore / $totalScore) * 100, 1) : 0;
        
        // محاسبه margin (فاصله بین اول و دوم)
        $sortedScores = array_values($scores);
        $margin = count($sortedScores) > 1 ? $sortedScores[0] - $sortedScores[1] : $sortedScores[0];
        $marginPercent = $totalScore > 0 ? round(($margin / $totalScore) * 100, 1) : 0;

        // ۶. استخراج پارامترها از متن
        $extractedParams = $this->extractParameters($cleanText, $topType, $extractedData);

        // ۷. یافتن نزدیک‌ترین نمونه واقعی
        $similarSample = $this->findSimilarSample($topType, $cleanText);

        // ۸. پیشنهاد روش حل
        $suggestedMethod = $this->suggestMethod($topType, $extractedParams);

        // ۹. تولید مدل ریاضی پیشنهادی
        $mathModel = $this->generateMathModel($topType, $extractedParams);

        // ۱۰. ذخیره در تاریخچه
        $this->saveAnalysis($text, $topType, $confidence, $suggestedMethod, $extractedParams);

        return [
            'success' => true,
            'detected_type' => $topType,
            'detected_type_name' => self::PROBLEM_TYPES[$topType] ?? $topType,
            'confidence' => $confidence,
            'margin' => $marginPercent,
            'all_scores' => $scores,
            'extracted_params' => $extractedParams,
            'extracted_data' => $extractedData,
            'suggested_method' => $suggestedMethod,
            'math_model' => $mathModel,
            'similar_sample' => $similarSample,
            'next_steps' => $this->getNextSteps($topType),
            'warnings' => $this->generateWarnings($topType, $extractedParams),
        ];
    }

    /**
     * استخراج داده‌های ساختاری از متن
     */
    private function extractStructuralData(string $text): array
    {
        return [
            'numbers' => $this->preprocessor->extractNumbers($text),
            'numbers_with_units' => $this->preprocessor->extractNumbersWithUnits($text),
            'matrices' => $this->preprocessor->extractMatrices($text),
            'stochastic_matrices' => $this->preprocessor->findStochasticMatrices(
                $this->preprocessor->extractMatrices($text)
            ),
            'queue_rates' => $this->preprocessor->extractQueueRates($text),
            'probabilities' => $this->preprocessor->extractProbabilities($text),
            'ranges' => $this->preprocessor->extractRanges($text),
            'coefficients' => $this->preprocessor->extractCoefficients($text),
        ];
    }

    /**
     * محاسبه امتیاز هر نوع مسئله
     */
    private function calculateScores(string $text, array $extractedData): array
    {
        $scores = array_fill_keys(array_keys(self::PROBLEM_TYPES), 0);

        // ۱. امتیازدهی کلمات کلیدی (با تطبیق مرزداری برای کلمات کوتاه)
        foreach ($this->patterns as $pattern) {
            $keyword  = $pattern['keyword_fa'];
            $weight   = (int)$pattern['weight'];
            $category = $pattern['category'] ?? 'general';
            $type     = $pattern['problem_type_code'];
            if (!isset($scores[$type])) continue;

            $count = $this->countKeywordOccurrences($text, $keyword);
            if ($count > 0) {
                $multiplier = $this->categoryWeights[$category] ?? 1.0;
                $scores[$type] += ($count * $weight * $multiplier);
            }
        }

        // ۲. امتیازهای ساختاری
        $scores = $this->addStructuralScores($scores, $text, $extractedData);

        // ۳. امتیازهای ترکیبی (قواعد قبلی)
        $scores = $this->addCombinationScores($scores, $text);

        // ۴. ✅ لایه تشخیصی + سرکوب واژگان مشترک
        $scores = $this->applyDiscriminativeLayer($scores, $text);

        return $scores;
    }

    /**
     * ✅ شمارش وقوع کلمه با رعایت مرز کلمه برای کلمات کوتاه (≤۴ حرف)
     * جلوگیری از شمارش «مو» داخل «مورد»، «نش» داخل «نشان»، «بازی» داخل «بازیابی»
     */
    private function countKeywordOccurrences(string $text, string $keyword): int
    {
        if ($keyword === '') return 0;
        if (mb_strlen($keyword) <= 4) {
            $pattern = '/(?<![\p{Arabic}])' . preg_quote($keyword, '/') . '(?![\p{Arabic}])/u';
            $n = preg_match_all($pattern, $text);
            return $n === false ? 0 : $n;
        }
        return mb_substr_count($text, $keyword);
    }

    /**
     * ✅ لایه تشخیصی: پاداش الگوهای یکتا + سرکوب واژگان مشترک بهینه‌سازی
     */
    private function applyDiscriminativeLayer(array $scores, string $text): array
    {
        $discScores = [];
        foreach (self::DISCRIMINATIVE as $type => $rules) {
            $bonus = 0;
            foreach ($rules as $rule) {
                if (preg_match($rule['pattern'], $text)) {
                    $bonus += $rule['weight'];
                }
            }
            $discScores[$type] = $bonus;
            if (isset($scores[$type])) {
                $scores[$type] += $bonus;
            }
        }

        // سرکوب LP وقتی یک نوع خاص با نشانه یکتا تشخیص داده شده
        // (چون «سود/بیشینه/محدودیت/تابع هدف» واژگان مشترک همه مسائل بهینه‌سازی‌اند)
        $maxNonLp = 0;
        foreach ($discScores as $t => $b) {
            if ($t !== 'LP' && $b > $maxNonLp) $maxNonLp = $b;
        }
        if ($maxNonLp >= 40) {
            $scores['LP'] = (int)round($scores['LP'] * 0.15);
        }

        return $scores;
    }

    /**
     * افزودن امتیازهای ساختاری
     */
    private function addStructuralScores(array $scores, string $text, array $extractedData): array
    {
        // MARKOV: وجود ماتریس stochastic
        if (!empty($extractedData['stochastic_matrices'])) {
            $scores['MARKOV'] += 20;
        }
        
        // QUEUEING: وجود λ و μ
        if ($extractedData['queue_rates']['lambda'] !== null && 
            $extractedData['queue_rates']['mu'] !== null) {
            $scores['QUEUEING'] += 25;
        }
        
        // GAME: وجود ماتریس‌های 2x2 یا بزرگتر
        foreach ($extractedData['matrices'] as $matrix) {
            if ($matrix['type'] !== 'array' && count($matrix['data']) >= 2) {
                $scores['GAME_THEORY'] += 10;
                break;
            }
        }
        
        // MONTECARLO: وجود توزیع‌های احتمالی
        if (count($extractedData['probabilities']) >= 2) {
            $scores['MONTE_CARLO'] += 15;
        }
        
        // LP/ILP: وجود ضرایب
        if (count($extractedData['coefficients']) >= 2) {
            $scores['LP'] += 10;
            // اگر کلمه "صحیح" یا "عددی" هم باشد، ILP
            if (preg_match('/(صحیح|عددی|integer|باینری)/ui', $text)) {
                $scores['ILP'] += 20;
            }
        }
        
        return $scores;
    }

    /**
     * امتیازدهی ترکیبی (الگوهای خاص)
     */
    private function addCombinationScores(array $scores, string $text): array
    {
        // LP با سود و محدودیت
        if (mb_strpos($text, 'سود') !== false && 
            (mb_strpos($text, 'محدودیت') !== false || mb_strpos($text, 'قیود') !== false)) {
            $scores['LP'] += 20;
        }
        
        // LP با بیشینه/کمینه
        if ((mb_strpos($text, 'بیشینه') !== false || mb_strpos($text, 'حداکثر') !== false) && 
            mb_strpos($text, 'سود') !== false) {
            $scores['LP'] += 15;
        }
        
        if ((mb_strpos($text, 'کمینه') !== false || mb_strpos($text, 'حداقل') !== false) && 
            mb_strpos($text, 'هزینه') !== false) {
            $scores['LP'] += 15;
        }
        
        // TRANS با همه عناصر
        if (mb_strpos($text, 'مبدأ') !== false && mb_strpos($text, 'مقصد') !== false && 
            mb_strpos($text, 'عرضه') !== false && mb_strpos($text, 'تقاضا') !== false) {
            $scores['TRANS'] += 25;
        }
        
        // ASSIGN با یک‌به‌یک
        if ((mb_strpos($text, 'یک به یک') !== false || mb_strpos($text, 'اختصاص') !== false) &&
            (mb_strpos($text, 'کارگر') !== false || mb_strpos($text, 'وظیفه') !== false)) {
            $scores['ASSIGN'] += 20;
        }
        
        // SHORTEST با شهر/فاصله
        if ((mb_strpos($text, 'کوتاه') !== false && mb_strpos($text, 'مسیر') !== false) ||
            (mb_strpos($text, 'فاصله') !== false && 
             (mb_strpos($text, 'شهر') !== false || mb_strpos($text, 'گره') !== false))) {
            $scores['SHORTEST'] += 20;
        }
        
        // TRANSSHIP با واسط
        if ((mb_strpos($text, 'ترانشیپ') !== false || mb_strpos($text, 'واسط') !== false) &&
            mb_strpos($text, 'چند مرحله') !== false) {
            $scores['TRANSSHIP'] += 25;
        }
        
        // DUAL با LP
        if (mb_strpos($text, 'دوگان') !== false && 
            (mb_strpos($text, 'برنامه‌ریزی') !== false || mb_strpos($text, 'خطی') !== false)) {
            $scores['DUAL'] += 30;
        }
        
        return $scores;
    }

    /**
     * استخراج پارامترها از متن
     */
    private function extractParameters(string $text, string $type, array $extractedData): array
    {
        $params = [
            'numbers' => $extractedData['numbers'],
            'potential_sources' => [],
            'potential_destinations' => [],
            'objectives' => [],
            'constraints' => [],
            'model_data' => null,
        ];

        // تشخیص مفاهیم کلیدی
        if (mb_strpos($text, 'کارخانه') !== false || mb_strpos($text, 'مبدأ') !== false) {
            $params['potential_sources'][] = 'کارخانه/مبدأ';
        }
        if (mb_strpos($text, 'انبار') !== false || mb_strpos($text, 'مقصد') !== false) {
            $params['potential_destinations'][] = 'انبار/مقصد';
        }
        if (mb_strpos($text, 'کارگر') !== false || mb_strpos($text, 'اپراتور') !== false) {
            $params['potential_sources'][] = 'نیروی کار';
        }
        if (mb_strpos($text, 'وظیفه') !== false || mb_strpos($text, 'دستگاه') !== false) {
            $params['potential_destinations'][] = 'وظیفه/دستگاه';
        }
        
        if (mb_strpos($text, 'حداکثر') !== false || mb_strpos($text, 'بیشینه') !== false || mb_strpos($text, 'سود') !== false) {
            $params['objectives'][] = 'maximize';
        }
        if (mb_strpos($text, 'حداقل') !== false || mb_strpos($text, 'کمینه') !== false || mb_strpos($text, 'هزینه') !== false) {
            $params['objectives'][] = 'minimize';
        }
        if (mb_strpos($text, 'محدودیت') !== false || mb_strpos($text, 'ظرفیت') !== false || mb_strpos($text, 'قیود') !== false) {
            $params['constraints'][] = 'capacity_limit';
        }

        // تولید model_data بر اساس نوع مسئله
        switch ($type) {
            case 'LP':
            case 'ILP':
                $params['model_data'] = $this->buildLPModel($text, $extractedData, $type === 'ILP');
                break;
            case 'TRANS':
                $params['model_data'] = $this->buildTransportModel($text, $extractedData);
                break;
            case 'ASSIGN':
                $params['model_data'] = $this->buildAssignmentModel($text, $extractedData);
                break;
            case 'SHORTEST':
                $params['model_data'] = $this->buildShortestModel($text, $extractedData);
                break;
            case 'TRANSSHIP':
                $params['model_data'] = $this->buildTransshipModel($text, $extractedData);
                break;
            case 'QUEUEING':
                $params['model_data'] = $this->buildQueueingModel($text, $extractedData);
                break;
            case 'MARKOV':
                $params['model_data'] = $this->buildMarkovModel($text, $extractedData);
                break;
            case 'GAME_THEORY':
                $params['model_data'] = $this->buildGameModel($text, $extractedData);
                break;
            case 'MONTE_CARLO':
                $params['model_data'] = $this->buildMonteCarloModel($text, $extractedData);
                break;
            case 'DUAL':
                $params['model_data'] = $this->buildDualModel($text, $extractedData);
                break;
        }

        return $params;
    }

    // ═══════════════════════════════════════════════════════
    // متدهای ساخت مدل برای هر نوع مسئله
    // ═══════════════════════════════════════════════════════

    private function buildLPModel(string $text, array $extractedData, bool $isInteger = false): array
    {
        $n = $extractedData['numbers'];

        // ═══════════════════════════════════════════════════════
        // ✅ حالت جدید: استخراج سطربه‌سطر «ضرایب X و Y و ظرفیت Z»
        // ═══════════════════════════════════════════════════════
        $rowConstraints = [];
        if (preg_match_all('/ضرایب\s+([\d\sو.,]+?)\s+و\s+ظرفیت\s+(\d+(?:\.\d+)?)/u', $text, $rm, PREG_SET_ORDER)) {
            foreach ($rm as $r) {
                preg_match_all('/\d+(?:\.\d+)?/', $r[1], $cn);
                if (!empty($cn[0])) {
                    $rowConstraints[] = [
                        'coeffs'   => array_map('floatval', $cn[0]),
                        'capacity' => (float)$r[2],
                    ];
                }
            }
        }

        // ✅ ضرایب تابع هدف: اعدادِ پس از عبارت «تابع هدف»
        $objCoeffs = null;
        if (preg_match('/تابع\s+هدف[^0-9]*?([\d\sو.,]+?)(?:\s+است|\s+می‌?باشد|[.،,;:]|$)/u', $text, $om)) {
            preg_match_all('/\d+(?:\.\d+)?/', $om[1], $on);
            if (!empty($on[0])) {
                $objCoeffs = array_map('floatval', $on[0]);
            }
        }

        // تشخیص نوع قیود
        $defaultConstraintType = '<=';
        if (preg_match('/حداقل|نباید\s+کمتر\s+از|بیشتر\s+مساوی/u', $text)) {
            $defaultConstraintType = '>=';
        }

        $objective = (preg_match('/بیشینه|حداکثر|ماکزیمم|maximize/ui', $text)) ? 'maximize' : 'minimize';

        // ─────────────────────────────────────────────────────────
        // مسیر A: قیود سطربه‌سطر یافت شد (دقیق‌ترین حالت)
        // ─────────────────────────────────────────────────────────
        if (!empty($rowConstraints)) {
            $numVariables = count($rowConstraints[0]['coeffs']);

            $variables = [];
            for ($i = 0; $i < $numVariables; $i++) {
                $variables[] = [
                    'name'       => "متغیر " . ($i + 1),
                    'coeff'      => $objCoeffs[$i] ?? ($n[$i] ?? (50 + $i * 20)),
                    'is_integer' => $isInteger,
                ];
            }

            $constraints = [];
            foreach ($rowConstraints as $i => $rc) {
                $constraints[] = [
                    'name'     => "محدودیت " . ($i + 1),
                    'coeffs'   => $rc['coeffs'],
                    'capacity' => $rc['capacity'],
                    'type'     => $defaultConstraintType,
                ];
            }

            return [
                'name'        => $isInteger ? 'پروژه برنامه‌ریزی صحیح (استخراج هوشمند)' : 'پروژه برنامه‌ریزی خطی (استخراج هوشمند)',
                'description' => mb_substr($text, 0, 2000),
                'objective'   => $objective,
                'variables'   => $variables,
                'constraints' => $constraints,
                'is_integer'  => $isInteger,
            ];
        }

        // ─────────────────────────────────────────────────────────
        // مسیر B: منطق قبلی (خواندن ترتیبی اعداد) — بدون رگرسیون
        // ─────────────────────────────────────────────────────────
        $numVariables = 2;
        $numConstraints = 2;

        if (preg_match('/(\d+)\s*(?:متغیر|محصول|کالا)/u', $text, $matches)) {
            $numVariables = (int)$matches[1];
        }
        if (preg_match('/(\d+)\s*(?:محدودیت|قید|منبع|ساعت)/u', $text, $matches)) {
            $numConstraints = (int)$matches[1];
        }

        $variables = [];
        for ($i = 0; $i < $numVariables; $i++) {
            $variables[] = [
                'name'       => "متغیر " . ($i + 1),
                'coeff'      => $objCoeffs[$i] ?? ($n[$i] ?? (50 + $i * 20)),
                'is_integer' => $isInteger,
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
                'name'     => "محدودیت " . ($i + 1),
                'coeffs'   => $coeffs,
                'capacity' => $n[$capacityIdx] ?? (60 + $i * 20),
                'type'     => $defaultConstraintType,
            ];
        }

        return [
            'name'        => $isInteger ? 'پروژه برنامه‌ریزی صحیح (استخراج هوشمند)' : 'پروژه برنامه‌ریزی خطی (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'objective'   => $objective,
            'variables'   => $variables,
            'constraints' => $constraints,
            'is_integer'  => $isInteger,
        ];
    }

    private function buildTransportModel(string $text, array $extractedData): array
    {
        $n = $extractedData['numbers'];
        $numSources = 2;
        $numDestinations = 2;
        
        if (preg_match('/(\d+)\s*(?:کارخانه|مبدأ|منبع)/u', $text, $matches)) {
            $numSources = (int)$matches[1];
        }
        if (preg_match('/(\d+)\s*(?:انبار|مقصد|مشتری)/u', $text, $matches)) {
            $numDestinations = (int)$matches[1];
        }
        
        $sources = [];
        for ($i = 0; $i < $numSources; $i++) {
            $sources[] = [
                'name' => "مبدأ " . ($i + 1),
                'capacity' => $n[1 + $i] ?? 100
            ];
        }
        
        $demandStartIdx = $numSources + 2;
        $destinations = [];
        for ($j = 0; $j < $numDestinations; $j++) {
            $destinations[] = [
                'name' => "مقصد " . ($j + 1),
                'demand' => $n[$demandStartIdx + $j] ?? 100
            ];
        }
        
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
        
        return [
            'name' => 'پروژه حمل و نقل (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'sources' => $sources,
            'destinations' => $destinations,
            'cost_matrix' => $costMatrix
        ];
    }

    private function buildAssignmentModel(string $text, array $extractedData): array
    {
        $n = $extractedData['numbers'];
        $numAgents = 2;
        $numTasks = 2;
        
        if (preg_match('/(\d+)\s*(?:کارگر|اپراتور|عامل|نیرو)/u', $text, $matches)) {
            $numAgents = (int)$matches[1];
        }
        if (preg_match('/(\d+)\s*(?:وظیفه|دستگاه|پروژه)/u', $text, $matches)) {
            $numTasks = (int)$matches[1];
        }
        
        $agents = [];
        for ($i = 0; $i < $numAgents; $i++) {
            $agents[] = ['name' => "عامل " . ($i + 1)];
        }
        
        $tasks = [];
        for ($j = 0; $j < $numTasks; $j++) {
            $tasks[] = ['name' => "وظیفه " . ($j + 1)];
        }
        
        $costMatrix = [];
        for ($i = 0; $i < $numAgents; $i++) {
            $row = [];
            for ($j = 0; $j < $numTasks; $j++) {
                $costIdx = ($i * $numTasks) + $j;
                $row[] = $n[$costIdx] ?? (5 + $i + $j);
            }
            $costMatrix[] = $row;
        }
        
        return [
            'name' => 'پروژه تخصیص (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'agents' => $agents,
            'tasks' => $tasks,
            'cost_matrix' => $costMatrix
        ];
    }

    private function buildShortestModel(string $text, array $extractedData): array
    {
        // استخراج یال‌ها
        $edgePattern = '/(?:از\s+)?([\p{Arabic}\x{200C}a-zA-Z]+)\s+(?:به|تا)\s+([\p{Arabic}\x{200C}a-zA-Z]+)(?:\s+(?:با\s+)?(?:وزن|فاصله|هزینه|طول))?\s+(\d+(?:\.\d+)?)/u';
        preg_match_all($edgePattern, $text, $edgeMatches, PREG_SET_ORDER);

        $nodeNames = [];
        $rawEdges = [];
        foreach ($edgeMatches as $m) {
            $from = $this->cleanNodeName($m[1]);
            $to = $this->cleanNodeName($m[2]);
            $w = (float)$m[3];
            if (mb_strlen($from) < 2 || mb_strlen($to) < 2) continue;
            if (!in_array($from, $nodeNames, true)) $nodeNames[] = $from;
            if (!in_array($to, $nodeNames, true)) $nodeNames[] = $to;
            $rawEdges[] = ['from' => $from, 'to' => $to, 'weight' => $w];
        }

        if (preg_match('/شامل\s+([^.!?؛;]+)/u', $text, $listMatch)) {
            $parts = preg_split('/[،,]+|\s+و\s+/u', $listMatch[1]);
            foreach ($parts as $p) {
                $name = $this->cleanNodeName($p);
                if (mb_strlen($name) >= 2 && !in_array($name, $nodeNames, true)) {
                    $nodeNames[] = $name;
                }
            }
        }

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

        $edges = [];
        foreach ($rawEdges as $re) {
            $fromIdx = array_search($re['from'], $nodeNames, true);
            $toIdx = array_search($re['to'], $nodeNames, true);
            if ($fromIdx !== false && $toIdx !== false) {
                $edges[] = ['from' => $fromIdx, 'to' => $toIdx, 'weight' => $re['weight']];
            }
        }

        if (empty($edges) && count($nodes) > 1) {
            $n = $extractedData['numbers'];
            for ($i = 0; $i < count($nodes) - 1; $i++) {
                $edges[] = [
                    'from' => $i,
                    'to' => $i + 1,
                    'weight' => $n[$i] ?? (10 + $i * 10),
                ];
            }
        }

        return [
            'name' => 'پروژه کوتاه‌ترین مسیر (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    private function buildTransshipModel(string $text, array $extractedData): array
    {
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
        
        return [
            'name' => 'پروژه ترانشیپمنت (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'sources' => array_map(fn($i) => ['name' => "مبدأ " . ($i + 1), 'capacity' => 100], range(0, $numSources - 1)),
            'transshipment' => array_map(fn($i) => ['name' => "گره واسط " . ($i + 1)], range(0, $numTransshipment - 1)),
            'destinations' => array_map(fn($i) => ['name' => "مقصد " . ($i + 1), 'demand' => 100], range(0, $numDestinations - 1))
        ];
    }

    private function buildQueueingModel(string $text, array $extractedData): array
    {
        $rates = $extractedData['queue_rates'];
        $lambda = $rates['lambda'] ?? 10;
        $mu = $rates['mu'] ?? 15;
        
        // تشخیص تعداد سرورها
        $servers = 1;
        if (preg_match('/(\d+)\s*(?:سرور|پنجره|صندوق|کارمند)/u', $text, $matches)) {
            $servers = (int)$matches[1];
        }
        
        // تشخیص ظرفیت
        $capacity = null;
        if (preg_match('/ظرفیت\s*(\d+)/u', $text, $matches)) {
            $capacity = (int)$matches[1];
        }
        
        // تشخیص مدل
        $modelCode = 'MM1';
        if ($servers > 1 && $capacity === null) {
            $modelCode = 'MMc';
        } elseif ($servers === 1 && $capacity !== null) {
            $modelCode = 'MM1K';
        } elseif ($servers > 1 && $capacity !== null) {
            $modelCode = 'MMcK';
        }
        
        return [
            'name' => 'پروژه صف (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'model_code' => $modelCode,
            'lambda' => $lambda,
            'mu' => $mu,
            'servers' => $servers,
            'capacity' => $capacity,
        ];
    }

    private function buildMarkovModel(string $text, array $extractedData): array
    {
        $states = ['سالم', 'خراب'];
        $transitionMatrix = [[0.9, 0.1], [0.4, 0.6]];
        $initialState = [1, 0];
        $steps = 5;
        
        // استفاده از ماتریس stochastic استخراج شده
        if (!empty($extractedData['stochastic_matrices'])) {
            $transitionMatrix = $extractedData['stochastic_matrices'][0]['data'];
            $n = count($transitionMatrix);
            $states = [];
            for ($i = 0; $i < $n; $i++) {
                $states[] = "حالت " . ($i + 1);
            }
            $initialState = array_fill(0, $n, 0);
            $initialState[0] = 1;
        }
        
        // استخراج تعداد گام‌ها
        if (preg_match('/(\d+)\s*(?:گام|مرحله|دوره)/u', $text, $matches)) {
            $steps = (int)$matches[1];
        }
        
        return [
            'name' => 'پروژه زنجیره مارکوف (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'states' => $states,
            'transition_matrix' => $transitionMatrix,
            'initial_state' => $initialState,
            'steps' => $steps,
        ];
    }

    private function buildGameModel(string $text, array $extractedData): array
    {
        $player1Strategies = ['استراتژی 1', 'استراتژی 2'];
        $player2Strategies = ['استراتژی 1', 'استراتژی 2'];
        $payoffMatrix = [[3, -2], [-1, 4]];
        
        // استفاده از ماتریس استخراج شده
        if (!empty($extractedData['matrices'])) {
            foreach ($extractedData['matrices'] as $matrix) {
                if ($matrix['type'] !== 'array' && count($matrix['data']) >= 2) {
                    $payoffMatrix = $matrix['data'];
                    $n = count($payoffMatrix);
                    $player1Strategies = [];
                    $player2Strategies = [];
                    for ($i = 0; $i < $n; $i++) {
                        $player1Strategies[] = "استراتژی " . ($i + 1);
                        $player2Strategies[] = "استراتژی " . ($i + 1);
                    }
                    break;
                }
            }
        }
        
        return [
            'name' => 'پروژه نظریه بازی‌ها (استخراج هوشمند)',
            'description' => mb_substr($text, 0, 2000),
            'player1_strategies' => $player1Strategies,
            'player2_strategies' => $player2Strategies,
            'payoff_matrix' => $payoffMatrix,
        ];
    }

    private function buildMonteCarloModel(string $text, array $extractedData): array
    {
        // استخراج تعداد تکرار
        $iterations = 10000;
        if (preg_match('/(\d+)\s*(?:تکرار|شبیه‌سازی|نمونه|iteration)/ui', $text, $matches)) {
            $iterations = max(100, min(1000000, (int)$matches[1])); // بین 100 و 1,000,000
        }
        
        // استخراج متغیرها از متن
        $rawVariables = $this->preprocessor->extractMonteCarloVariables($text);
        
        // اگر هیچ متغیری یافت نشد، یک متغیر پیش‌فرض بساز
        if (empty($rawVariables)) {
            $rawVariables = [
                [
                    'name_fa' => 'متغیر اصلی',
                    'dist'    => 'normal',
                    'params'  => ['mean' => 100, 'std' => 10]
                ]
            ];
        }
        
        // اختصاص نام انگلیسی به متغیرها (سازگار با MonteCarloEngine)
        $variables = $this->preprocessor->assignEnglishNames($rawVariables);
        
        // ساخت تابع هدف (پیش‌فرض: جمع همه متغیرها)
        $varNames = array_map(fn($v) => $v['name'], $variables);
        $functionExpr = implode(' + ', $varNames);
        
        // بررسی الگوی ضرب در متن
        if (preg_match('/(?:حاصل\s*ضرب|ضرب\s+در\s+یکدیگر|ضرب\s+هم)/u', $text)) {
            $functionExpr = implode(' * ', $varNames);
        }
        
        // بررسی الگوی تفریق در متن
        if (preg_match('/(?:هزینه\s+کل|سود\s+خالص|درآمد\s+منهای|تفاضل)/u', $text)) {
            if (count($varNames) === 2) {
                $functionExpr = $varNames[0] . ' - ' . $varNames[1];
            }
        }
        
        // ایجاد توصیف متغیرها برای نمایش
        $variablesDescription = [];
        foreach ($variables as $v) {
            $desc = $v['label'] . ' (' . $v['name'] . '): ';
            switch ($v['dist']) {
                case 'normal':
                    $desc .= "نرمال(μ={$v['params']['mean']}, σ={$v['params']['std']})";
                    break;
                case 'uniform':
                    $desc .= "یکنواخت[{$v['params']['min']}, {$v['params']['max']}]";
                    break;
                case 'triangular':
                    $desc .= "مثلثی[{$v['params']['min']}, {$v['params']['mode']}, {$v['params']['max']}]";
                    break;
                case 'exponential':
                    $desc .= "نمایی(λ={$v['params']['lambda']})";
                    break;
                case 'lognormal':
                    $desc .= "لگاریتمی-نرمال(μ={$v['params']['mean']}, σ={$v['params']['std']})";
                    break;
            }
            $variablesDescription[] = $desc;
        }
        
        return [
            'name'                 => 'پروژه شبیه‌سازی مونت‌کارلو (استخراج هوشمند)',
            'description'          => mb_substr($text, 0, 2000) . "\n\n--- متغیرهای استخراج‌شده ---\n" . implode("\n", $variablesDescription) . "\nتابع هدف: {$functionExpr}",
            'variables'            => $variables,
            'function_expr'        => $functionExpr,
            'iterations'           => $iterations,
            'variables_description'=> $variablesDescription,
        ];
    }

    /**
     * ✅ ساخت مدل دوگان: ابتدا مسئله اصلی (Primal) به‌صورت LP استخراج می‌شود
     * سپس موتور DualSimplexEngine آن را حل کرده و قیمت‌های سایه‌ای می‌دهد
     */
    private function buildDualModel(string $text, array $extractedData): array
    {
        // استخراج کامل مسئله اصلی (متغیرها + قیود + هدف)
        $primal = $this->buildLPModel($text, $extractedData, false);

        $primal['name'] = 'پروژه نظریه دوگان (استخراج هوشمند)';
        $primal['is_dual'] = true;

        return $primal;
    }

    /**
     * پاکسازی نام گره
     */
    private function cleanNodeName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[،,.؛;:!؟?()«»\[\]{}"-]+/u', '', $name);
        $name = preg_replace('/^شهر/u', '', $name);
        $name = preg_replace('/(?:هستند|است|می‌باشد|میباشد)$/u', '', $name);
        $name = trim($name);
        $stop = ['به', 'از', 'تا', 'با', 'وزن', 'فاصله', 'هزینه', 'کیلومتر', 'کیلومتری',
                'مبدأ', 'مقصد', 'و', 'در', 'را', 'که', 'این', 'ها', 'یال', 'شبکه', 'پیدا'];
        if (in_array($name, $stop, true)) return '';
        return $name;
    }

    /**
     * یافتن نمونه مشابه
     */
    private function findSimilarSample(string $type, string $text): ?array
    {
        $candidates = array_filter($this->samples, function($s) use ($type) {
            return $s['problem_type_code'] === $type;
        });

        if (empty($candidates)) return null;

        return array_values($candidates)[0];
    }

    /**
     * پیشنهاد روش حل
     */
    private function suggestMethod(string $type, array $params): ?array
    {
        $methods = [
            'TRANS' => [
                'primary' => ['code' => 'VAM', 'name' => 'تقریب ووگل (VAM)', 'reason' => 'دقیق‌ترین روش اولیه'],
                'alternative' => ['code' => 'NWC', 'name' => 'گوشه شمال غربی', 'reason' => 'ساده‌ترین روش'],
            ],
            'ASSIGN' => [
                'primary' => ['code' => 'HUNGARIAN', 'name' => 'الگوریتم مجارستانی', 'reason' => 'بهترین روش برای تخصیص'],
            ],
            'SHORTEST' => [
                'primary' => ['code' => 'DIJKSTRA', 'name' => 'Dijkstra', 'reason' => 'برای گراف با وزن‌های نامنفی'],
                'alternative' => ['code' => 'FLOYD', 'name' => 'Floyd-Warshall', 'reason' => 'برای همه جفت‌گره‌ها'],
            ],
            'LP' => [
                'primary' => ['code' => 'SIMPLEX', 'name' => 'سیمپلکس', 'reason' => 'روش استاندارد برای LP'],
                'alternative' => ['code' => 'BIG_M', 'name' => 'Big-M', 'reason' => 'اگر قیود نامساوی ≥ دارید'],
            ],
            'ILP' => [
                'primary' => ['code' => 'BRANCH_BOUND', 'name' => 'Branch and Bound', 'reason' => 'روش استاندارد برای ILP'],
            ],
            'TRANSSHIP' => [
                'primary' => ['code' => 'MIN_COST_FLOW', 'name' => 'Minimum Cost Flow', 'reason' => 'بهترین روش برای چندمرحله‌ای'],
            ],
            'QUEUEING' => [
                'primary' => ['code' => 'ANALYTICAL', 'name' => 'تحلیلی', 'reason' => 'محاسبه مستقیم با فرمول‌ها'],
            ],
            'MARKOV' => [
                'primary' => ['code' => 'MATRIX_MULT', 'name' => 'ضرب ماتریسی', 'reason' => 'محاسبه حالت پس از n گام'],
            ],
            'GAME_THEORY' => [
                'primary' => ['code' => 'NASH', 'name' => 'تعادل نش', 'reason' => 'یافتن استراتژی بهینه'],
            ],
            'MONTE_CARLO' => [
                'primary' => ['code' => 'SIMULATION', 'name' => 'شبیه‌سازی', 'reason' => 'تولید نمونه‌های تصادفی'],
            ],
            'DUAL' => [
                'primary' => ['code' => 'DUAL_SIMPLEX', 'name' => 'سیمپلکس دوگان', 'reason' => 'حل مسئله دوگان'],
            ],
        ];

        return $methods[$type] ?? null;
    }

    /**
     * تولید مدل ریاضی پیشنهادی
     */
    private function generateMathModel(string $type, array $params): ?array
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
            ],
            'ASSIGN' => [
                'title' => 'مدل ریاضی مسئله تخصیص',
                'variables' => 'xᵢⱼ = 1 اگر عامل i به وظیفه j اختصاص یابد، 0 در غیر این صورت',
                'objective' => 'Min Z = ΣΣ cᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'Σⱼ xᵢⱼ = 1  (هر عامل دقیقاً یک وظیفه)',
                    'Σᵢ xᵢⱼ = 1  (هر وظیفه دقیقاً یک عامل)',
                    'xᵢⱼ ∈ {0, 1}',
                ],
            ],
            'SHORTEST' => [
                'title' => 'مدل ریاضی کوتاه‌ترین مسیر',
                'variables' => 'xᵢⱼ = 1 اگر یال (i,j) در مسیر باشد، 0 در غیر این صورت',
                'objective' => 'Min Z = ΣΣ dᵢⱼ × xᵢⱼ',
                'constraints' => [
                    'جریان ورودی = جریان خروجی برای هر گره میانی',
                    'xᵢⱼ ∈ {0, 1}',
                ],
            ],
            'LP' => [
                'title' => 'مدل ریاضی برنامه‌ریزی خطی',
                'variables' => 'x₁, x₂, ..., xₙ = متغیرهای تصمیم',
                'objective' => 'Max/Min Z = Σ cⱼ × xⱼ',
                'constraints' => [
                    'Σ aᵢⱼ × xⱼ ≤/=/≥ bᵢ  (برای هر محدودیت i)',
                    'xⱼ ≥ 0  (غیرمنفی بودن)',
                ],
            ],
            'ILP' => [
                'title' => 'مدل ریاضی برنامه‌ریزی صحیح',
                'variables' => 'x₁, x₂, ..., xₙ = متغیرهای تصمیم صحیح',
                'objective' => 'Max/Min Z = Σ cⱼ × xⱼ',
                'constraints' => [
                    'Σ aᵢⱼ × xⱼ ≤/=/≥ bᵢ',
                    'xⱼ ∈ Z  (صحیح بودن)',
                ],
            ],
            'QUEUEING' => [
                'title' => 'مدل ریاضی صف (M/M/c/K)',
                'variables' => 'λ = نرخ ورود، μ = نرخ خدمت، c = تعداد سرورها',
                'objective' => 'محاسبه معیارهای عملکرد (L, Lq, W, Wq, ρ)',
                'constraints' => [
                    'ρ = λ / (c × μ) < 1  (پایداری سیستم)',
                ],
            ],
            'MARKOV' => [
                'title' => 'مدل ریاضی زنجیره مارکوف',
                'variables' => 'P = ماتریس انتقال، π = بردار حالت',
                'objective' => 'π(n) = π(0) × Pⁿ  (حالت پس از n گام)',
                'constraints' => [
                    'Σⱼ Pᵢⱼ = 1  (مجموع هر سطر = 1)',
                    'π × P = π  (حالت پایدار)',
                ],
            ],
            'GAME_THEORY' => [
                'title' => 'مدل ریاضی نظریه بازی‌ها',
                'variables' => 'A = ماتریس پرداخت بازیکن 1، B = ماتریس پرداخت بازیکن 2',
                'objective' => 'یافتن تعادل نش (Nash Equilibrium)',
                'constraints' => [
                    'استراتژی‌های ترکیبی: Σ pᵢ = 1',
                ],
            ],
            'MONTE_CARLO' => [
                'title' => 'مدل شبیه‌سازی مونت‌کارلو',
                'variables' => 'X₁, X₂, ... = متغیرهای تصادفی با توزیع مشخص',
                'objective' => 'E[f(X)] ≈ (1/N) Σ f(xᵢ)  (تخمین امید ریاضی)',
                'constraints' => [
                    'N = تعداد تکرارها (معمولاً > 10000)',
                ],
            ],
            'DUAL' => [
                'title' => 'مدل ریاضی نظریه دوگان',
                'variables' => 'y₁, y₂, ..., yₘ = متغیرهای دوگان',
                'objective' => 'Max W = Σ bᵢ × yᵢ',
                'constraints' => [
                    'Σ aᵢⱼ × yᵢ ≤ cⱼ  (برای هر متغیر اصلی j)',
                    'yᵢ ≥ 0',
                ],
            ],
        ];

        return $models[$type] ?? null;
    }

    /**
     * مراحل بعدی
     */
    private function getNextSteps(string $type): array
    {
        $steps = [
            'TRANS' => [
                '۱. مبادی و ظرفیت آن‌ها را مشخص کنید',
                '۲. مقاصد و تقاضای آن‌ها را وارد کنید',
                '۳. ماتریس هزینه حمل را تکمیل کنید',
                '۴. انتخاب روش حل (VAM، NWC، MODI)',
            ],
            'ASSIGN' => [
                '۱. عوامل (کارگران/اپراتورها) را مشخص کنید',
                '۲. وظایف (دستگاه‌ها/پروژه‌ها) را وارد کنید',
                '۳. ماتریس هزینه/زمان را تکمیل کنید',
                '۴. اجرای الگوریتم مجارستانی',
            ],
            'SHORTEST' => [
                '۱. گره‌ها (شهرها/نقاط) را مشخص کنید',
                '۲. یال‌ها و وزن آن‌ها را وارد کنید',
                '۳. مبدأ و مقصد نهایی را تعیین کنید',
                '۴. انتخاب الگوریتم (Dijkstra یا Floyd)',
            ],
            'LP' => [
                '۱. متغیرهای تصمیم را تعریف کنید',
                '۲. تابع هدف را بنویسید',
                '۳. قیود را مشخص کنید',
                '۴. انتخاب روش حل (سیمپلکس)',
            ],
            'ILP' => [
                '۱. متغیرهای صحیح را تعریف کنید',
                '۲. تابع هدف و قیود را مشخص کنید',
                '۳. انتخاب روش Branch and Bound',
            ],
            'TRANSSHIP' => [
                '۱. مبادی، گره‌های واسط و مقاصد را مشخص کنید',
                '۲. ظرفیت هر گره را تعیین کنید',
                '۳. هزینه حمل بین هر جفت گره را وارد کنید',
            ],
            'QUEUEING' => [
                '۱. نرخ ورود (λ) و نرخ خدمت (μ) را مشخص کنید',
                '۲. تعداد سرورها (c) را تعیین کنید',
                '۳. ظرفیت سیستم (K) را مشخص کنید (در صورت وجود)',
                '۴. محاسبه معیارهای عملکرد',
            ],
            'MARKOV' => [
                '۱. حالت‌های سیستم را مشخص کنید',
                '۲. ماتریس انتقال را وارد کنید',
                '۳. حالت اولیه را تعیین کنید',
                '۴. تعداد گام‌ها را مشخص کنید',
            ],
            'GAME_THEORY' => [
                '۱. بازیکنان و استراتژی‌های آن‌ها را مشخص کنید',
                '۲. ماتریس پرداخت را وارد کنید',
                '۳. یافتن تعادل نش',
            ],
            'MONTE_CARLO' => [
                '۱. متغیرهای تصادفی و توزیع آن‌ها را مشخص کنید',
                '۲. تابع هدف را تعریف کنید',
                '۳. تعداد تکرارها را تعیین کنید',
                '۴. اجرای شبیه‌سازی و تحلیل نتایج',
            ],
            'DUAL' => [
                '۱. مسئله اصلی (Primal) را مشخص کنید',
                '۲. تبدیل به مسئله دوگان',
                '۳. حل با سیمپلکس دوگان',
            ],
        ];

        return $steps[$type] ?? [];
    }

    /**
     * تولید هشدارها
     */
    private function generateWarnings(string $type, array $params): array
    {
        $warnings = [];

        if ($type === 'TRANS') {
            if (empty($params['potential_sources'])) {
                $warnings[] = 'منابع (مبادی) در متن شناسایی نشدند.';
            }
            if (empty($params['potential_destinations'])) {
                $warnings[] = 'مقاصد در متن شناسایی نشدند.';
            }
        }

        if ($type === 'ASSIGN') {
            $warnings[] = 'برای مسئله تخصیص، تعداد عوامل و وظایف باید برابر باشد.';
        }

        if ($type === 'QUEUEING') {
            if (!isset($params['model_data']['lambda']) || !isset($params['model_data']['mu'])) {
                $warnings[] = 'نرخ ورود (λ) و نرخ خدمت (μ) باید مشخص شوند.';
            }
        }

        if ($type === 'MARKOV') {
            if (empty($params['model_data']['transition_matrix'])) {
                $warnings[] = 'ماتریس انتقال باید مشخص شود.';
            }
        }

        if ($type === 'LP' || $type === 'ILP') {
            if (count($params['constraints']) === 0) {
                $warnings[] = 'هیچ محدودیتی در متن شناسایی نشد.';
            }
        }

        return $warnings;
    }

    /**
     * ذخیره تحلیل در تاریخچه
     */
    private function saveAnalysis(string $text, string $type, float $confidence, ?array $method, array $params): void
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
     * دریافت همه نمونه‌های واقعی
     */
    public function getSamples(?string $type = null): array
    {
        if ($type) {
            return array_filter($this->samples, function($s) use ($type) {
                return $s['problem_type_code'] === $type;
            });
        }
        return $this->samples;
    }
}