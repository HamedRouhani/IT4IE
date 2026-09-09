<?php
namespace App\Software\Or\Helpers;

/**
 * ═══════════════════════════════════════════════════════════════
 * PersianTextPreprocessor - پیش‌پردازش حرفه‌ای متن فارسی
 * ═══════════════════════════════════════════════════════════════
 * 
 * این کلاس مسئولیت‌های زیر را بر عهده دارد:
 * 1. نرمال‌سازی کامل متن فارسی (اعداد، کاراکترها، نیم‌فاصله)
 * 2. استخراج اعداد فارسی/عربی/انگلیسی
 * 3. تشخیص و استخراج ساختارهای ریاضی (ماتریس، آرایه)
 * 4. حفظ علائم ریاضی مهم (λ, μ, α, β, ≤, ≥, ≠, Σ)
 * 5. استخراج واحدها و بازه‌ها
 * 6. توکن‌بندی هوشمند و حذف کلمات توقف
 * 
 * @version 2.0.0
 * @author IT4IE Smart OR Team
 */
class PersianTextPreprocessor
{
    /**
     * علائم ریاضی که نباید حذف شوند
     */
    private const MATH_SYMBOLS = [
        'λ', 'μ', 'α', 'β', 'γ', 'δ', 'ε', 'θ', 'π', 'σ', 'φ', 'ω',
        'Λ', 'Μ', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Θ', 'Π', 'Σ', 'Φ', 'Ω',
        '≤', '≥', '≠', '±', '∞', '√', '∑', '∏', '∫', '∂',
        '×', '÷', '∈', '∉', '⊆', '⊇', '∪', '∩', '∀', '∃'
    ];
    
    /**
     * نقشه تبدیل کاراکترهای مشابه عربی/فارسی
     */
    private const CHAR_MAP = [
        'ي' => 'ی',  // ی عربی
        'ك' => 'ک',  // ک عربی
        'ة' => 'ه',  // تاء مربوطه
        'ى' => 'ی',  // ی عربی دیگر
        'ؤ' => 'و',  // واو با همزه
        'إ' => 'ا',  // الف با همزه زیر
        'أ' => 'ا',  // الف با همزه بالا
        'آ' => 'ا',  // الف ممدوده
        'ـ' => '',   // Tatweel
        'ٔ' => '',   // Hamza above
        'ٕ' => '',   // Hamza below
    ];
    
    /**
     * نقشه تبدیل اعداد فارسی/عربی به انگلیسی
     */
    private const NUMBER_MAP = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];
    
    /**
     * کلمات توقف فارسی (Stop Words)
     * این کلمات در تشخیص نوع مسئله تأثیری ندارند و می‌توانند حذف شوند
     */
    private const STOP_WORDS = [
        'است', 'هست', 'بود', 'شد', 'شده', 'بوده', 'می‌باشد', 'میباشد',
        'هستند', 'بودند', 'شده‌اند', 'شده اند', 'می‌شوند', 'میشوند',
        'این', 'آن', 'اینکه', 'آنکه', 'که', 'چیست', 'کجا', 'چگونه',
        'را', 'به', 'از', 'با', 'بر', 'برای', 'در', 'تا', 'روی', 'روی',
        'بین', 'میان', 'پس', 'قبل', 'بعد', 'اگر', 'اگه', 'ولی', 'اما',
        'و', 'یا', 'هم', 'نیز', 'فقط', 'حتی', 'چون', 'زیرا', 'چرا',
        'بسیار', 'خیلی', 'کم', 'زیاد', 'تقریباً', 'تقریبا',
        'یک', 'دو', 'سه', 'چند', 'هر', 'همه', 'هیچ', 'بعضی', 'برخی',
        'ما', 'من', 'تو', 'او', 'ایشان', 'شما', 'آنها', 'آنان',
        'که', 'چیز', 'کس', 'جا', 'زمان', 'موقع',
        'همچنین', 'اضافه', 'برعکس', 'بنابراین', 'پس', 'سرانجام',
        'ها', 'های', 'هایِ', 'ای', 'یِ',
    ];
    
    /**
     * واحدهای اندازه‌گیری رایج در مسائل OR
     */
    private const UNITS = [
        'تومان', 'ریال', 'دلار', 'یورو',
        'میلیون', 'میلیارد', 'هزار',
        'کیلوگرم', 'گرم', 'تن', 'لیتر', 'متر', 'کیلومتر',
        'ساعت', 'دقیقه', 'ثانیه', 'روز', 'هفته', 'ماه', 'سال',
        'عدد', 'عدد', 'نفر', 'دستگاه', 'کارخانه', 'انبار', 'مشتری',
        'درصد', 'در صد',
        'سال', 'سالانه', 'ماهانه', 'روزانه', 'ساعتی',
    ];
    
    // ═══════════════════════════════════════════════════════
    // متدهای اصلی
    // ═══════════════════════════════════════════════════════
    
    /**
     * نرمال‌سازی کامل متن فارسی
     * 
     * این متد تمام مراحل زیر را به ترتیب انجام می‌دهد:
     * 1. یکسان‌سازی کاراکترهای مشابه
     * 2. تبدیل اعداد فارسی/عربی به انگلیسی
     * 3. نرمال‌سازی نیم‌فاصله‌ها
     * 4. حذف کاراکترهای اضافی (با حفظ علائم ریاضی)
     * 5. تبدیل فاصله‌های چندتایی به یکی
     * 
     * @param string $text متن ورودی
     * @return string متن نرمال‌سازی شده
     */
    public function normalize(string $text): string
    {
        if (empty($text)) return '';
        
        // 1. یکسان‌سازی کاراکترها
        $text = $this->normalizeCharacters($text);
        
        // 2. تبدیل اعداد
        $text = $this->convertNumbers($text);
        
        // 3. نرمال‌سازی نیم‌فاصله‌ها
        $text = $this->normalizeHalfSpaces($text);
        
        // 4. تمیزکاری عمومی (با حفظ علائم ریاضی)
        $text = $this->cleanText($text);
        
        // 5. تبدیل فاصله‌های چندتایی
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * یکسان‌سازی کاراکترهای مشابه
     */
    public function normalizeCharacters(string $text): string
    {
        return str_replace(
            array_keys(self::CHAR_MAP),
            array_values(self::CHAR_MAP),
            $text
        );
    }
    
    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی
     */
    public function convertNumbers(string $text): string
    {
        return str_replace(
            array_keys(self::NUMBER_MAP),
            array_values(self::NUMBER_MAP),
            $text
        );
    }
    
    /**
     * نرمال‌سازی نیم‌فاصله‌ها
     */
    public function normalizeHalfSpaces(string $text): string
    {
        // ZWNJ, NBSP, RLM, LRM, RTL Mark, LTR Mark
        $text = preg_replace('/[\x{200C}\x{00A0}\x{200F}\x{200E}\x{202B}\x{202A}]/u', ' ', $text);
        
        // حفظ نیم‌فاصله در کلمات ترکیبی مهم (مثل "می‌رود" → "می‌رود")
        // در واقع نیم‌فاصله واقعی را نگه می‌داریم، نه کاراکترهای نامرئی
        return $text;
    }
    
    /**
     * تمیزکاری عمومی متن (با حفظ علائم ریاضی و ساختارهای مهم)
     */
    public function cleanText(string $text): string
    {
        // ساخت رشته‌ای از علائم ریاضی برای استثنا کردن
        $mathSymbolsPattern = implode('', array_map(function($s) {
            return preg_quote($s, '/');
        }, self::MATH_SYMBOLS));
        
        // کاراکترهای مجاز: حروف فارسی/عربی، انگلیسی، اعداد، علائم ریاضی، و علائم ساختاری
        $allowedPattern = '/[^\p{Arabic}\p{Latin}\p{N}\s' . $mathSymbolsPattern . '\.\,\;\:\!\?\(\)\[\]\{\}\-\+\=\/\*\<\>\%\@\#\$\&\|]/u';
        
        return preg_replace($allowedPattern, ' ', $text);
    }
    
    /**
     * توکن‌بندی هوشمند متن فارسی
     * 
     * @param string $text متن ورودی
     * @param bool $removeStopWords آیا کلمات توقف حذف شوند؟
     * @return array آرایه‌ای از توکن‌ها
     */
    public function tokenize(string $text, bool $removeStopWords = true): array
    {
        $text = $this->normalize($text);
        
        // جدا کردن بر اساس فاصله و علائم نگارشی
        $tokens = preg_split('/[\s،,.؛;:!؟?\(\)\[\]\{\}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if ($removeStopWords) {
            $tokens = array_filter($tokens, function($token) {
                return !in_array(mb_strtolower($token), self::STOP_WORDS);
            });
        }
        
        return array_values($tokens);
    }
    
    /**
     * حذف کلمات توقف
     */
    public function removeStopWords(string $text): string
    {
        $tokens = $this->tokenize($text, false);
        $filtered = array_filter($tokens, function($token) {
            return !in_array(mb_strtolower($token), self::STOP_WORDS);
        });
        return implode(' ', $filtered);
    }
    
    // ═══════════════════════════════════════════════════════
    // متدهای استخراج داده
    // ═══════════════════════════════════════════════════════
    
    /**
     * استخراج تمام اعداد از متن (صحیح و اعشاری)
     * 
     * @param string $text
     * @return array آرایه‌ای از اعداد به صورت float
     */
    public function extractNumbers(string $text): array
    {
        $text = $this->convertNumbers($text);
        
        // الگوی شناسایی اعداد صحیح و اعشاری (با کاما یا نقطه)
        preg_match_all('/-?\d+(?:[.,]\d+)?/', $text, $matches);
        
        $numbers = [];
        foreach ($matches[0] as $num) {
            // تبدیل کاما به نقطه (برای اعداد فارسی)
            $num = str_replace(',', '.', $num);
            $numbers[] = (float)$num;
        }
        
        return $numbers;
    }
    
    /**
     * استخراج اعداد با برچسب واحد (مثلاً: "۲۰۰ تن" → [200, 'تن'])
     * 
     * @param string $text
     * @return array آرایه‌ای از جفت‌های [عدد, واحد]
     */
    public function extractNumbersWithUnits(string $text): array
    {
        $text = $this->normalize($text);
        $unitsPattern = implode('|', array_map(function($u) {
            return preg_quote($u, '/');
        }, self::UNITS));
        
        $pattern = '/(-?\d+(?:[.,]\d+)?)\s*(' . $unitsPattern . ')/u';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        
        $results = [];
        foreach ($matches as $m) {
            $num = str_replace(',', '.', $m[1]);
            $results[] = [
                'value' => (float)$num,
                'unit' => $m[2],
                'raw' => $m[0]
            ];
        }
        
        return $results;
    }
    
    /**
     * استخراج ماتریس‌ها و آرایه‌های عددی از متن
     * مثال: [[1,2],[3,4]] یا (1, 2, 3)
     * 
     * @param string $text
     * @return array آرایه‌ای از ماتریس‌ها
     */
    public function extractMatrices(string $text): array
    {
        $text = $this->convertNumbers($text);
        $matrices = [];
        
        // الگوی 1: ماتریس‌های استاندارد [[a,b],[c,d]]
        if (preg_match_all('/\[\s*\[.*?\]\s*(?:,\s*\[.*?\]\s*)*\]/s', $text, $matches)) {
            foreach ($matches[0] as $matrixStr) {
                $matrix = $this->parseMatrix($matrixStr);
                if ($matrix) {
                    $matrices[] = [
                        'type' => 'standard',
                        'data' => $matrix,
                        'raw' => $matrixStr
                    ];
                }
            }
        }
        
        // الگوی 2: آرایه‌های تک‌بعدی [1, 2, 3, 4]
        if (preg_match_all('/\[\s*-?\d+(?:\s*,\s*-?\d+(?:\.\d+)?)*\s*\]/', $text, $matches)) {
            foreach ($matches[0] as $arrayStr) {
                preg_match_all('/-?\d+(?:\.\d+)?/', $arrayStr, $nums);
                if (!empty($nums[0])) {
                    $matrices[] = [
                        'type' => 'array',
                        'data' => array_map('floatval', $nums[0]),
                        'raw' => $arrayStr
                    ];
                }
            }
        }
        
        // الگوی 3: ماتریس‌های پرانتزی (1 2; 3 4)
        if (preg_match_all('/\(\s*\d+(?:\s+\d+)*\s*(?:;\s*\d+(?:\s+\d+)*\s*)*\)/', $text, $matches)) {
            foreach ($matches[0] as $matrixStr) {
                $rows = explode(';', trim($matrixStr, '() '));
                $matrix = [];
                foreach ($rows as $row) {
                    preg_match_all('/\d+(?:\.\d+)?/', $row, $nums);
                    if (!empty($nums[0])) {
                        $matrix[] = array_map('floatval', $nums[0]);
                    }
                }
                if (!empty($matrix)) {
                    $matrices[] = [
                        'type' => 'parentheses',
                        'data' => $matrix,
                        'raw' => $matrixStr
                    ];
                }
            }
        }
        
        return $matrices;
    }
    
    /**
     * تجزیه رشته ماتریس استاندارد به آرایه PHP
     */
    private function parseMatrix(string $matrixStr): ?array
    {
        // استخراج سطرهای داخل کروشه
        preg_match_all('/\[\s*(-?\d+(?:\s*,\s*-?\d+(?:\.\d+)?)*)\s*\]/', $matrixStr, $rowMatches);
        
        if (empty($rowMatches[1])) {
            return null;
        }
        
        $matrix = [];
        foreach ($rowMatches[1] as $rowStr) {
            preg_match_all('/-?\d+(?:\.\d+)?/', $rowStr, $numMatches);
            $matrix[] = array_map('floatval', $numMatches[0]);
        }
        
        return $matrix;
    }
    
    /**
     * استخراج نرخ‌های λ (لامبدا) و μ (مو) برای صف
     * 
     * @param string $text
     * @return array ['lambda' => float|null, 'mu' => float|null, 'details' => []]
     */
    public function extractQueueRates(string $text): array
    {
        $text = $this->normalize($text);
        $result = ['lambda' => null, 'mu' => null, 'details' => []];
        
        // الگوی λ: "λ=10" یا "لامبدا=10" یا "نرخ ورود=10"
        $lambdaPatterns = [
            '/λ\s*[=:]\s*(\d+(?:\.\d+)?)/iu',
            '/لامبدا\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/لاندا\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/نرخ\s*ورود\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/نرخ\s*رسیدن\s*[=:]\s*(\d+(?:\.\d+)?)/u',
        ];
        
        foreach ($lambdaPatterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                $result['lambda'] = (float)$match[1];
                $result['details'][] = ['type' => 'lambda', 'value' => $result['lambda'], 'raw' => $match[0]];
                break;
            }
        }
        
        // الگوی μ: "μ=15" یا "مو=15" یا "نرخ خدمت=15"
        $muPatterns = [
            '/μ\s*[=:]\s*(\d+(?:\.\d+)?)/iu',
            '/مو\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/نرخ\s*خدمت\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/نرخ\s*سرویس\s*[=:]\s*(\d+(?:\.\d+)?)/u',
            '/نرخ\s*خروج\s*[=:]\s*(\d+(?:\.\d+)?)/u',
        ];
        
        foreach ($muPatterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                $result['mu'] = (float)$match[1];
                $result['details'][] = ['type' => 'mu', 'value' => $result['mu'], 'raw' => $match[0]];
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * تشخیص ماتریس‌های احتمالاتی (Stochastic Matrices) برای مارکوف
     * 
     * @param array $matrices ماتریس‌های استخراج شده
     * @return array آرایه‌ای از ماتریس‌های stochastic
     */
    public function findStochasticMatrices(array $matrices): array
    {
        $stochastic = [];
        
        foreach ($matrices as $matrix) {
            if ($matrix['type'] === 'array') continue; // آرایه‌های تک‌بعدی رد می‌شوند
            
            $data = $matrix['data'];
            if (!$this->isSquareMatrix($data)) continue;
            
            // بررسی stochastic: مجموع هر سطر ≈ 1
            $isStochastic = true;
            foreach ($data as $row) {
                $sum = array_sum($row);
                if (abs($sum - 1.0) > 0.01) {
                    $isStochastic = false;
                    break;
                }
                // بررسی اینکه همه مقادیر بین 0 و 1 باشند
                foreach ($row as $val) {
                    if ($val < 0 || $val > 1) {
                        $isStochastic = false;
                        break 2;
                    }
                }
            }
            
            if ($isStochastic) {
                $stochastic[] = $matrix;
            }
        }
        
        return $stochastic;
    }
    
    /**
     * بررسی مربع بودن ماتریس
     */
    private function isSquareMatrix(array $matrix): bool
    {
        $rowCount = count($matrix);
        if ($rowCount < 2) return false;
        
        foreach ($matrix as $row) {
            if (count($row) !== $rowCount) return false;
        }
        
        return true;
    }
    
    /**
     * استخراج احتمال‌ها و درصدها از متن
     * 
     * @param string $text
     * @return array آرایه‌ای از احتمال‌ها (بین 0 و 1)
     */
    public function extractProbabilities(string $text): array
    {
        $text = $this->normalize($text);
        $probs = [];
        
        // الگوی درصد: "50%" یا "۵۰ درصد"
        if (preg_match_all('/(\d+(?:\.\d+)?)\s*[%٪]/u', $text, $matches)) {
            foreach ($matches[1] as $p) {
                $probs[] = ['value' => (float)$p / 100, 'raw' => $p . '%'];
            }
        }
        
        // الگوی کلمه "درصد" یا "احتمال": "احتمال 0.3" یا "۰.۳ احتمال"
        if (preg_match_all('/احتمال\s+(\d+(?:\.\d+)?)/u', $text, $matches)) {
            foreach ($matches[1] as $p) {
                $val = (float)$p;
                if ($val > 1) $val /= 100;
                $probs[] = ['value' => $val, 'raw' => $matches[0][0]];
            }
        }
        
        return $probs;
    }
    
    /**
     * استخراج بازه‌های عددی (min, max)
     * مثال: "بین 100 تا 150" یا "از 5 الی 10"
     * 
     * @param string $text
     * @return array
     */
    public function extractRanges(string $text): array
    {
        $text = $this->normalize($text);
        $ranges = [];
        
        $patterns = [
            '/بین\s+(\d+(?:\.\d+)?)\s+(?:تا|و|الی|لغایت)\s+(\d+(?:\.\d+)?)/u',
            '/از\s+(\d+(?:\.\d+)?)\s+(?:تا|الی|لغایت)\s+(\d+(?:\.\d+)?)/u',
            '/(\d+(?:\.\d+)?)\s*[-–]\s*(\d+(?:\.\d+)?)/u',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $ranges[] = [
                        'min' => (float)$m[1],
                        'max' => (float)$m[2],
                        'raw' => $m[0]
                    ];
                }
            }
        }
        
        return $ranges;
    }
    
    /**
     * استخراج ضرایب از عبارات ریاضی
     * مثال: "5x + 3y = 10" → [5, 3, 10]
     * 
     * @param string $text
     * @return array
     */
    public function extractCoefficients(string $text): array
    {
        $text = $this->normalize($text);
        $coeffs = [];
        
        // الگوی شناسایی ضرایب قبل از متغیرها
        preg_match_all('/(-?\d+(?:\.\d+)?)\s*[a-zآ-ی]/ui', $text, $matches);
        if (!empty($matches[1])) {
            $coeffs = array_map('floatval', $matches[1]);
        }
        
        return $coeffs;
    }
    
    // ═══════════════════════════════════════════════════════
    // متدهای کمکی
    // ═══════════════════════════════════════════════════════
    
    /**
     * تشخیص جهت متن (RTL یا LTR)
     */
    public function detectDirection(string $text): string
    {
        $persianCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        $latinCount = preg_match_all('/[a-zA-Z]/', $text);
        
        return $persianCount > $latinCount ? 'rtl' : 'ltr';
    }
    
    /**
     * محاسبه طول متن (بدون در نظر گرفتن کدپوینت‌های چندبایتی)
     */
    public function length(string $text): int
    {
        return mb_strlen($text, 'UTF-8');
    }
    
    /**
     * بررسی وجود حداقل یکی از کلمات کلیدی در متن
     * 
     * @param string $text
     * @param array $keywords
     * @return bool
     */
    public function hasAnyKeyword(string $text, array $keywords): bool
    {
        $text = $this->normalize($text);
        foreach ($keywords as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * شمارش تعداد تکرار کلمات کلیدی در متن
     * 
     * @param string $text
     * @param array $keywords
     * @return array [keyword => count]
     */
    public function countKeywords(string $text, array $keywords): array
    {
        $text = $this->normalize($text);
        $counts = [];
        foreach ($keywords as $keyword) {
            $counts[$keyword] = mb_substr_count($text, $keyword);
        }
        return $counts;
    }

    /**
     * ═══════════════════════════════════════════════════════
     * استخراج متغیرهای تصادفی برای شبیه‌سازی مونت‌کارلو
     * ═══════════════════════════════════════════════════════
     * 
     * پشتیبانی از توزیع‌ها:
     * - normal (میانگین، انحراف معیار)
     * - uniform (کمینه، بیشینه)
     * - triangular (کمینه، محتمل‌ترین، بیشینه)
     * - exponential (نرخ lambda)
     * - lognormal (میانگین، انحراف معیار)
     * 
     * @param string $text متن ورودی
     * @return array آرایه‌ای از متغیرها با ساختار:
     *   [['name_fa'=>'مواد', 'dist'=>'normal', 'params'=>['mean'=>200, 'std'=>20]], ...]
     */
    public function extractMonteCarloVariables(string $text): array
    {
        $text = $this->normalize($text);
        $variables = [];
        
        // ─────────────────────────────────────────────────────────
        // ۱. شناسایی توزیع نرمال (Normal Distribution)
        // الگو: "هزینه مواد با توزیع نرمال با میانگین ۲۰۰ و انحراف معیار ۲۰"
        // ─────────────────────────────────────────────────────────
        $normalRegex = '/([\p{Arabic}\p{Latin}\s]{2,30}?)(?:\s+با)?\s*(?:توزیع\s+)?نرمال[^\d]{0,30}?میانگین\s*(\d+(?:\.\d+)?)[^\d]{0,20}?(?:و\s+)?(?:انحراف\s+)?(?:معیار|استاندارد|انحراف)\s*(\d+(?:\.\d+)?)/ui';
        
        if (preg_match_all($normalRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $this->cleanVariableName($m[1]);
                if (empty($name) || mb_strlen($name) < 2) continue;
                
                // بررسی تکراری نبودن
                if ($this->variableExists($variables, $name)) continue;
                
                $variables[] = [
                    'name_fa' => $name,
                    'dist'    => 'normal',
                    'params'  => [
                        'mean' => (float)$m[2],
                        'std'  => (float)$m[3]
                    ]
                ];
            }
        }
        
        // ─────────────────────────────────────────────────────────
        // ۲. شناسایی توزیع یکنواخت (Uniform Distribution)
        // الگو: "هزینه نیروی کار با توزیع یکنواخت بین ۱۰۰ تا ۱۵۰"
        // ─────────────────────────────────────────────────────────
        $uniformRegex = '/([\p{Arabic}\p{Latin}\s]{2,30}?)(?:\s+با)?\s*(?:توزیع\s+)?یکنواخت[^\d]{0,30}?(?:بین|از)\s*(\d+(?:\.\d+)?)[^\d]{0,10}?(?:تا|الی|لغایت)\s*(\d+(?:\.\d+)?)/ui';
        
        if (preg_match_all($uniformRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $this->cleanVariableName($m[1]);
                if (empty($name) || mb_strlen($name) < 2) continue;
                if ($this->variableExists($variables, $name)) continue;
                
                $variables[] = [
                    'name_fa' => $name,
                    'dist'    => 'uniform',
                    'params'  => [
                        'min' => (float)$m[2],
                        'max' => (float)$m[3]
                    ]
                ];
            }
        }
        
        // ─────────────────────────────────────────────────────────
        // ۳. شناسایی توزیع مثلثی (Triangular Distribution)
        // الگو: "توزیع مثلثی با کمینه 10، محتمل‌ترین 20 و بیشینه 30"
        // ─────────────────────────────────────────────────────────
        $triangularRegex = '/([\p{Arabic}\p{Latin}\s]{2,30}?)(?:\s+با)?\s*(?:توزیع\s+)?مثلثی[^\d]{0,30}?(?:کمینه|حداقل)\s*(\d+(?:\.\d+)?)[^\d]{0,20}?(?:محتمل|محتمل‌ترین|محتملترین|بیشترین\s+احتمال)\s*(\d+(?:\.\d+)?)[^\d]{0,20}?(?:بیشینه|حداکثر)\s*(\d+(?:\.\d+)?)/ui';
        
        if (preg_match_all($triangularRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $this->cleanVariableName($m[1]);
                if (empty($name) || mb_strlen($name) < 2) continue;
                if ($this->variableExists($variables, $name)) continue;
                
                $variables[] = [
                    'name_fa' => $name,
                    'dist'    => 'triangular',
                    'params'  => [
                        'min'  => (float)$m[2],
                        'mode' => (float)$m[3],
                        'max'  => (float)$m[4]
                    ]
                ];
            }
        }
        
        // ─────────────────────────────────────────────────────────
        // ۴. شناسایی توزیع نمایی (Exponential Distribution)
        // الگو: "توزیع نمایی با میانگین 5" یا "با نرخ 0.2"
        // ─────────────────────────────────────────────────────────
        $exponentialRegex = '/([\p{Arabic}\p{Latin}\s]{2,30}?)(?:\s+با)?\s*(?:توزیع\s+)?نمایی[^\d]{0,30}?(?:میانگین|نرخ|lambda|λ)\s*(\d+(?:\.\d+)?)/ui';
        
        if (preg_match_all($exponentialRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $this->cleanVariableName($m[1]);
                if (empty($name) || mb_strlen($name) < 2) continue;
                if ($this->variableExists($variables, $name)) continue;
                
                $lambda = (float)$m[2];
                // اگر میانگین داده شده، به نرخ تبدیل می‌کنیم
                $actualLambda = $lambda > 1 ? 1 / $lambda : $lambda;
                
                $variables[] = [
                    'name_fa' => $name,
                    'dist'    => 'exponential',
                    'params'  => ['lambda' => $actualLambda]
                ];
            }
        }
        
        // ─────────────────────────────────────────────────────────
        // ۵. شناسایی توزیع لگاریتمی نرمال (Lognormal Distribution)
        // ─────────────────────────────────────────────────────────
        $lognormalRegex = '/([\p{Arabic}\p{Latin}\s]{2,30}?)(?:\s+با)?\s*(?:توزیع\s+)?(?:لگاریتمی\s+)?نرمال\s+لگاریتمی|لگاریتمی[^\d]{0,30}?میانگین\s*(\d+(?:\.\d+)?)[^\d]{0,20}?(?:و\s+)?(?:انحراف\s+)?(?:معیار|استاندارد)\s*(\d+(?:\.\d+)?)/ui';
        
        if (preg_match_all($lognormalRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $this->cleanVariableName($m[1]);
                if (empty($name) || mb_strlen($name) < 2) continue;
                if ($this->variableExists($variables, $name)) continue;
                
                $variables[] = [
                    'name_fa' => $name,
                    'dist'    => 'lognormal',
                    'params'  => [
                        'mean' => (float)$m[2],
                        'std'  => (float)$m[3]
                    ]
                ];
            }
        }
        
        return $variables;
    }

    /**
     * پاکسازی نام متغیر تصادفی
     */
    private function cleanVariableName(string $name): string
    {
        $name = trim($name);
        // حذف پیشوندهای رایج
        $name = preg_replace('/^(?:هزینه|مقدار|متغیر|میزان|قیمت|تعداد|درآمد|سود|زمان|مدت|طول)\s+/u', '', $name);
        // حذف پسوندهای رایج
        $name = preg_replace('/\s+(?:تومان|ریال|دلار|یورو|میلیون|میلیارد|هزار|درصد|واحد|نفر|ساعت|روز|سال|کیلوگرم|متر|کیلومتر)$/u', '', $name);
        // حذف علائم نگارشی انتهایی
        $name = preg_replace('/[،,.؛;:!؟?\(\)\[\]{}"]+$/u', '', $name);
        // حذف کلمات اضافی
        $name = preg_replace('/\s+(?:به\s+دست\s+می‌?آید|است|می‌?باشد|هست|دارد|دارای)\s*$/u', '', $name);
        return trim($name);
    }

    /**
     * بررسی تکراری نبودن متغیر
     */
    private function variableExists(array $variables, string $name): bool
    {
        foreach ($variables as $v) {
            if (isset($v['name_fa']) && $v['name_fa'] === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * ساخت نام انگلیسی برای متغیرها (سازگار با MonteCarloEngine)
     * 
     * @param array $variables آرایه متغیرها با فیلد name_fa
     * @return array آرایه متغیرها با name انگلیسی و label فارسی
     */
    public function assignEnglishNames(array $variables): array
    {
        $result = [];
        $nameMap = [];
        
        // نگاشت نام‌های فارسی رایج به انگلیسی
        $translations = [
            'مواد' => 'material',
            'هزینه مواد' => 'material_cost',
            'نیروی کار' => 'labor',
            'هزینه نیروی کار' => 'labor_cost',
            'سربار' => 'overhead',
            'هزینه سربار' => 'overhead_cost',
            'درآمد' => 'revenue',
            'سود' => 'profit',
            'هزینه کل' => 'total_cost',
            'زمان' => 'time',
            'تقاضا' => 'demand',
            'عرضه' => 'supply',
            'قیمت' => 'price',
            'فروش' => 'sales',
        ];
        
        foreach ($variables as $i => $v) {
            $nameFa = $v['name_fa'];
            
            // ابتدا بررسی نگاشت
            if (isset($translations[$nameFa])) {
                $englishName = $translations[$nameFa];
            } else {
                // در غیر این صورت از x1, x2, x3 استفاده کن
                $englishName = 'x' . ($i + 1);
            }
            
            // بررسی منحصر به فرد بودن
            $originalName = $englishName;
            $counter = 1;
            while (in_array($englishName, $nameMap)) {
                $englishName = $originalName . '_' . $counter;
                $counter++;
            }
            
            $nameMap[] = $englishName;
            
            $v['name'] = $englishName;
            $v['label'] = $nameFa; // حفظ نام فارسی برای نمایش
            $result[] = $v;
        }
        
        return $result;
    }
}