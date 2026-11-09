<?php

namespace App\Services;

class ProblemIntelligenceService
{
    public function analyze($problem)
    {
        $problem = trim((string)$problem);

        if ($problem === '') {
            return [
                'success' => false,
                'message' => 'لطفاً مسئله خود را توضیح دهید.'
            ];
        }

        $text = $this->normalizeText($problem);

        $domains = [];
        $methods = [];
        $modules = [];
        $guidanceNotes = [];

        $supplierDecisionSignal = $this->containsAny($text, [
            'تامین کننده', 'تامین‌کننده', 'supplier', 'فروشنده',
            'انتخاب بین گزینه', 'انتخاب بهترین گزینه', 'تصمیم‌گیری چندمعیاره',
            'اولویت‌بندی گزینه', 'ahp', 'topsis', 'vikor'
        ]);

        $qualityControlSignal = $this->containsAny($text, [
            'کنترل کیفیت', 'نمودار کنترل', 'حدود کنترل', 'فرایند خارج از کنترل',
            'قابلیت فرایند', 'شاخص cp', 'شاخص cpk', 'نرخ معیوب', 'نرخ عیب',
            'نرخ ضایعات', 'مردودی', 'عیب‌یابی کیفیت', 'اندازه‌گیری سیستم',
            'msa', 'gr&r', 'spc', 'pareto', 'پارتو', 'نمونه‌گیری پذیرشی',
            'aql', 'عدم انطباق', 'اقدام اصلاحی', 'کنترل فرایند',
            'control chart', 'process capability', 'quality control', 'acceptance sampling'
        ]);

        $futureMoneyQuestion = $this->containsAny($text, [
            'ارزش پول', 'ارزش آتی', 'ارزش آینده', 'مبلغ آینده', 'مبلغ در آینده',
            'ارزش زمانی پول', 'قدرت خرید', 'سود مرکب', 'رشد سرمایه', 'ارزش اسمی',
            'ارزش واقعی', 'future value', 'time value of money', 'purchasing power',
            'compound interest'
        ]);

        /*
         * موجودی، انبار و سیاست سفارش‌دهی
         */
        if ($this->containsAny($text, [
            'موجودی', 'انبار', 'نقطه سفارش', 'نقطهٔ سفارش', 'موجودی اطمینان',
            'موجودی ایمنی', 'کسری موجودی', 'کمبود موجودی', 'سفارش اقتصادی',
            'مقدار سفارش', 'طبقه بندی abc', 'طبقه‌بندی abc', 'eoq', 'inventory',
            'reorder point', 'safety stock', 'stockout'
        ])) {
            $domains[] = 'کنترل موجودی و برنامه‌ریزی سفارش';
            $inventoryAbcSignal = $this->containsAny($text, ['abc', 'طبقه‌بندی', 'طبقه بندی', 'ارزش مصرف']);
            $inventoryOrderSignal = $this->containsAny($text, ['eoq', 'مقدار سفارش', 'سفارش اقتصادی', 'هزینه سفارش', 'هزینه نگهداری']);
            $inventoryReorderSignal = $this->containsAny($text, ['نقطه سفارش', 'نقطهٔ سفارش', 'موجودی اطمینان', 'موجودی ایمنی', 'کمبود موجودی', 'کسری موجودی', 'حداقل موجودی', 'reorder point', 'safety stock', 'stockout']);

            if ($inventoryAbcSignal) {
                $methods[] = 'طبقه‌بندی ABC بر پایهٔ ارزش مصرف سالانه';
                $guidanceNotes[] = 'برای ABC آماده کنید: اقلام، مصرف سالانه و بهای واحد؛ این تحلیل اقلام را بر اساس ارزش مصرف اولویت‌بندی می‌کند و به‌تنهایی نقطهٔ سفارش تعیین نمی‌کند.';
            }
            if ($inventoryOrderSignal) {
                $methods[] = 'EOQ با تقاضای سالانه، هزینهٔ سفارش و هزینهٔ نگهداری';
                $guidanceNotes[] = 'برای EOQ، تقاضای سالانه، هزینهٔ هر سفارش و هزینهٔ نگهداری هر واحد در سال لازم است؛ زمان تأمین و موجودی اطمینان را جداگانه در سیاست سفارش لحاظ کنید.';
            }
            if ($inventoryReorderSignal) {
                $methods[] = 'نقطهٔ سفارش و موجودی اطمینان بر پایهٔ تقاضا و زمان تأمین';
                $guidanceNotes[] = 'برای نقطهٔ سفارش، تقاضا طی زمان تأمین و سطح خدمت هدف را مشخص کنید؛ سفارش‌های درراه و موجودی قابل‌استفاده را هم از موقعیت موجودی کم نکنید.';
            }
            if (!$inventoryAbcSignal && !$inventoryOrderSignal && !$inventoryReorderSignal) {
                $methods[] = 'ثبت گردش اقلام و پایش موجودی و وضعیت سفارش‌ها';
                $guidanceNotes[] = 'برای شروع، اقلام و واحد سنجش، موجودی فعلی، گردش یا تقاضا و زمان تأمین را مشخص کنید؛ سپس می‌توان تعیین کرد ABC، EOQ یا نقطهٔ سفارش مناسب است.';
            }
            $modules[] = [
                'name' => 'Inventory Analyzer',
                'title' => 'تحلیل و کنترل موجودی',
                'url' => '/software/inventory-analyzer/'
            ];
        }

        /*
         * مطالعه کار و زمان‌سنجی
         */
        if ($this->containsAny($text, [
            'زمان سنجی', 'زمان‌سنجی', 'زمان سنجی کار', 'مطالعه کار', 'مطالعهٔ کار',
            'اندازه گیری کار', 'اندازه‌گیری کار', 'زمان استاندارد', 'زمان نرمال',
            'زمان چرخه', 'زمان سیکل', 'ضریب عملکرد', 'کارسنجی', 'ارزیابی کار',
            'نمونه‌گیری کار', 'work sampling', 'تعداد چرخه', 'مشاهده زمان‌سنجی', 'مطالعهٔ زمان',
            'time study', 'work measurement', 'standard time', 'cycle time'
        ])) {
            $domains[] = 'مطالعه کار و اندازه‌گیری زمان';
            if ($this->containsAny($text, ['نمونه‌گیری کار', 'work sampling', 'درصد بیکاری', 'درصد فعالیت'])) {
                $methods[] = 'نمونه‌گیری کار و برآورد نسبت فعالیت با سطح اطمینان و دقت هدف';
                $guidanceNotes[] = 'برای نمونه‌گیری کار، فعالیت‌ها را دسته‌بندی کنید، نسبت مورد انتظار و دقت و اطمینان مطلوب را تعیین کنید و مشاهدات را در زمان‌های تصادفی انجام دهید؛ این روش زمان هر چرخه را مستقیماً اندازه نمی‌گیرد.';
            } else {
                $methods[] = 'زمان‌سنجی مستقیم به تفکیک عناصر و چرخه‌ها';
                $methods[] = 'تعدیل زمان مشاهده‌شده با ضریب عملکرد و allowance مستند';
                $methods[] = 'محاسبهٔ زمان نرمال و استاندارد با انتخاب مبنای allowance';
                $guidanceNotes[] = 'برای زمان‌سنجی، روش کار را تثبیت و به عناصر قابل‌اندازه‌گیری تقسیم کنید؛ چند چرخه ثبت کنید و ضریب عملکرد و allowance را با مبنای روشن وارد کنید. زمان ماشین را از زمان انسانی جدا نگه دارید.';
            }
            $modules[] = [
                'name' => 'Time Study Analyzer',
                'title' => 'مطالعه کار و زمان‌سنجی',
                'url' => '/software/time-study-analyzer/'
            ];
        }

        /*
         * ارزیابی اقتصادی گزینه‌ها و طرح‌ها
         */
        if ($futureMoneyQuestion || $this->containsAny($text, [
            'اقتصاد مهندسی', 'ارزش فعلی خالص', 'ارزش فعلی', 'ارزش سالانه',
            'نرخ بازگشت سرمایه', 'نرخ بازده داخلی', 'جریان نقدی', 'تحلیل اقتصادی',
            'مقایسه اقتصادی', 'دوره بازگشت', 'نقطه سربه سر', 'نقطه سر به سر',
            'نرخ تورم', 'تورم', 'نرخ سود', 'بازده سالانه', 'افزایش سرمایه',
            'ارزش پول', 'ارزش آتی', 'ارزش آینده', 'مبلغ آینده', 'future value',
            'npv', 'irr', 'engineering economy', 'cash flow', 'payback period', 'inflation'
        ])) {
            $domains[] = 'ارزیابی اقتصادی و مقایسه سرمایه‌گذاری';
            if ($futureMoneyQuestion) {
                $methods[] = 'ارزش آتی (FW) با نرخ مرکب و تعداد دوره‌ها';
                $methods[] = 'تفکیک ارزش اسمی آینده از قدرت خرید واقعی با لحاظ تورم';
                $guidanceNotes[] = 'برای برآورد مبلغ آینده، مبلغ فعلی، نرخ بازده یا سود سالانه و تعداد دوره‌ها را مشخص کنید. اگر منظورتان قدرت خرید به قیمت امروز است، نرخ تورم مورد انتظار را هم وارد کنید؛ نتیجه به این فرض‌ها وابسته است و پیش‌بینی قطعی نیست.';
            } else {
                $methods[] = 'ارزش فعلی، ارزش آتی و ارزش سالانه';
                $methods[] = 'نرخ بازده داخلی و تحلیل جریان نقدی';
                $methods[] = 'مقایسه اقتصادی گزینه‌ها';
            }
            $modules[] = [
                'name' => 'Engineering Economy Analyzer',
                'title' => 'تحلیل اقتصاد مهندسی',
                'url' => '/software/engineering-economy/'
            ];
        }

        /*
         * نگهداری و تعمیرات و قابلیت اطمینان دارایی‌ها
         */
        $pdmMethodsBefore = count($methods);
        if ($this->containsAny($text, [
            'نگهداری', 'تعمیرات', 'خرابی تجهیز', 'خرابی ماشین', 'خرابی دستگاه',
            'قابلیت اطمینان تجهیز', 'قابلیت اطمینان سامانه', 'دستورکار تعمیر',
            'دارایی فنی', 'تعمیر پیشگیرانه', 'تعمیرات پیشگیرانه', 'تعمیرات اضطراری',
            'mtbf', 'mttr', 'failure rate', 'maintenance', 'reliability', 'fmea'
        ])) {
            $domains[] = 'نگهداری، تعمیرات و قابلیت اطمینان';
            if ($this->containsAny($text, ['mtbf', 'فاصله خرابی', 'فاصلهٔ خرابی', 'تکرار خرابی', 'نرخ خرابی', 'خرابی مکرر'])) {
                $methods[] = 'تحلیل روند خرابی و MTBF با تعریف روشن رویداد و ساعت کارکرد';
                $guidanceNotes[] = 'برای تحلیل MTBF، خرابی‌های معتبر و بازهٔ زمانی یا ساعت کارکرد هر تجهیز لازم است. فاصلهٔ تقویمی بین دستورکارها را با ساعت کار واقعی تجهیز یکی نگیرید.';
            }
            if ($this->containsAny($text, ['mttr', 'زمان تعمیر', 'مدت تعمیر', 'توقف', 'downtime'])) {
                $methods[] = 'تحلیل MTTR و مدت توقف با تفکیک زمان سپری‌شده از نفرساعت';
                $guidanceNotes[] = 'در PdM، MTTR از زمان شروع تا تکمیل دستورکار محاسبه می‌شود و ممکن است زمان انتظار را هم شامل شود؛ برای نفرساعت یا توقف تجهیز باید همان دادهٔ مستقل ثبت شود.';
            }
            if ($this->containsAny($text, ['پیشگیرانه', 'سرویس دوره‌ای', 'سرویس دوره ای', 'برنامه تعمیر', 'برنامه نگهداری', 'preventive maintenance'])) {
                $methods[] = 'برنامه‌ریزی نگهداری پیشگیرانه و پیگیری دستورکار';
                $guidanceNotes[] = 'برای زمان‌بندی PM، دارایی، نوع فعالیت، تناوب و مبنای زمانی را تعیین کنید؛ برنامهٔ تقویمی را با ساعت‌کارکرد واقعی تجهیز اشتباه نگیرید.';
            }
            if ($this->containsAny($text, ['fmea', 'اولویت خرابی', 'ریسک خرابی', 'rpn', 'حالت خرابی'])) {
                $methods[] = 'اولویت‌بندی حالت‌های خرابی با FMEA و RPN';
                $guidanceNotes[] = 'برای FMEA هر حالت خرابی را جداگانه با شدت، وقوع و قابلیت کشف ارزیابی کنید؛ RPN ابزار اولویت‌بندی است و جایگزین بررسی اقدام‌های ایمنی بحرانی نیست.';
            }
            if (count($methods) === $pdmMethodsBefore) {
                $methods[] = 'ثبت دارایی، دستورکار و تاریخچهٔ خرابی برای انتخاب سیاست نگهداری';
                $guidanceNotes[] = 'برای پیشنهاد سیاست نگهداری، نوع دارایی، پیامد خرابی، سوابق خرابی و تعمیر، زمان‌های شروع و پایان و برنامهٔ PM را مشخص کنید.';
            }
            $modules[] = [
                'name' => 'PdM Analyzer',
                'title' => 'مدیریت نگهداری و تعمیرات',
                'url' => '/software/pdm-analyzer/'
            ];
        }

        /*
         * کنترل و بهبود کیفیت؛ جدا از تحلیل آماری عمومی
         */
        $qualityMethodsBefore = count($methods);
        if ($qualityControlSignal || (!$supplierDecisionSignal && $this->containsAny($text, [
            'کیفیت', 'کیفیت محصول', 'کیفیت پایین', 'کیفیت نامناسب', 'کیفیت ناپایدار',
            'بهبود کیفیت', 'افت کیفیت', 'کیفیت فرایند', 'کیفیت فرآیند',
            'quality', 'quality issue', 'quality problem'
        ]))) {
            $domains[] = 'کنترل آماری و بهبود کیفیت';
            if ($this->containsAny($text, ['نمودار کنترل', 'حدود کنترل', 'spc', 'کنترل فرایند', 'control chart'])) {
                $methods[] = 'انتخاب نمودار SPC متناسب با دادهٔ متغیر یا صفتی و زیرگروه';
                $guidanceNotes[] = 'برای SPC نوع داده، اندازه و منطق زیرگروه و ترتیب زمانی را مشخص کنید؛ حدود کنترل از دادهٔ فرایند به‌دست می‌آیند و با حدود مشخصات مشتری یکی نیستند.';
            }
            if ($this->containsAny($text, ['قابلیت فرایند', 'شاخص cp', 'شاخص cpk', 'process capability'])) {
                $methods[] = 'محاسبهٔ Cp/Cpk پس از بررسی پایداری فرایند';
                $guidanceNotes[] = 'برای Cp/Cpk، دادهٔ اندازه‌گیری معتبر و حدود مشخصات LSL/USL لازم است؛ ابتدا پایداری فرایند را بررسی کنید، چون قابلیت یک فرایند ناپایدار تفسیرپذیر نیست.';
            }
            if ($this->containsAny($text, ['msa', 'gr&r', 'اندازه‌گیری سیستم', 'اندازه گیری سیستم'])) {
                $methods[] = 'MSA و تفکیک تکرارپذیری و تجدیدپذیری';
                $guidanceNotes[] = 'برای GR&R مشخص کنید چه قطعات، ارزیاب‌ها و تکرارهایی در مطالعه‌اند؛ طراحی متوازن و دامنهٔ قطعات باید نمایندهٔ کاربرد واقعی اندازه‌گیری باشد.';
            }
            if ($this->containsAny($text, ['نمونه‌گیری پذیرشی', 'aql', 'بازرسی محموله', 'acceptance sampling'])) {
                $methods[] = 'طرح نمونه‌گیری پذیرشی و احتمال پذیرش محموله';
                $guidanceNotes[] = 'برای نمونه‌گیری پذیرشی، اندازهٔ محموله، n، عدد پذیرش c و ریسک‌های تولیدکننده و مصرف‌کننده را تعیین کنید؛ این ابزار جایگزین کنترل آماری پایدار فرایند نیست.';
            }
            if ($this->containsAny($text, ['pareto', 'پارتو', 'نرخ ضایعات', 'نرخ عیب', 'مردودی', 'عدم انطباق', 'اقدام اصلاحی'])) {
                $methods[] = 'اولویت‌بندی عیب‌ها و اقدام اصلاحی با تحلیل پارتو';
                $guidanceNotes[] = 'برای تحلیل عیب، دسته‌بندی ثابت و شمارش یا هزینهٔ عیب‌ها را در یک بازهٔ زمانی مشخص ثبت کنید؛ پارتو اولویت را نشان می‌دهد، نه علت ریشه‌ای را.';
            }
            if (count($methods) === $qualityMethodsBefore) {
                $methods[] = 'تعریف مسئلهٔ کیفیت و انتخاب تحلیل مناسب از داده‌های عیب یا اندازه‌گیری';
                $guidanceNotes[] = 'برای تعیین روش، روشن کنید مسئله دربارهٔ نوسان فرایند، انطباق با مشخصات، خطای اندازه‌گیری، عیب‌های محصول یا بازرسی محموله است.';
            }
            $modules[] = [
                'name' => 'Quality Analyzer',
                'title' => 'تحلیل کیفیت و کنترل فرایند',
                'url' => '/software/quality-analyzer/'
            ];
        }

        /*
         * انتخاب تأمین‌کننده
         */
        if (
            $this->containsAny($text, [
                'تامین کننده',
                'تأمین کننده',
                'تامین‌کننده',
                'تأمین‌کننده',
                'supplier',
                'فروشنده',
                'تصمیم گیری چندمعیاره',
                'تصمیم‌گیری چندمعیاره',
                'انتخاب بین گزینه',
                'انتخاب بهترین گزینه',
                'انتخاب گزینه',
                'اولویت بندی گزینه',
                'اولویت‌بندی گزینه',
                'ahp',
                'topsis',
                'vikor',
                'روش saw',
                'saw method'
            ])
        ) {
            $domains[] = $this->containsAny($text, [
                'تامین کننده', 'تأمین کننده', 'تامین‌کننده', 'تأمین‌کننده', 'supplier', 'فروشنده'
            ])
                ? 'انتخاب تأمین‌کننده'
                : 'تصمیم‌گیری چندمعیاره و انتخاب گزینه';

            $methods[] = 'تحلیل تصمیم‌گیری چندمعیاره (MCDM)';
            $methods[] = 'AHP / TOPSIS';
            $methods[] = 'تحلیل حساسیت';

            $modules[] = [
                'name' => 'MCDM Analyzer',
                'title' => 'تحلیل تصمیم‌گیری چندمعیاره',
                'url' => '/software/mcdm-analyzer/'
            ];
        }

        /*
         * تولید و برنامه‌ریزی
         */
        if (
            $this->containsAny($text, [
                'تولید',
                'برنامه تولید',
                'زمانبندی تولید',
                'زمان‌بندی تولید',
                'ظرفیت تولید',
                'خط تولید',
                'ماشین آلات',
                'ماشین‌آلات'
            ])
        ) {
            $domains[] = 'برنامه‌ریزی و کنترل تولید';

            $methods[] = 'تحقیق در عملیات';
            $methods[] = 'بهینه‌سازی';
            $methods[] = 'برنامه‌ریزی تولید';

            $modules[] = [
                'name' => 'OR Analyzer',
                'title' => 'تحقیق در عملیات و بهینه‌سازی',
                'url' => '/software/or-analyzer/'
            ];
        }

        /*
         * آماری / کیفیت / داده
         */
        $explicitStatisticsSignal = $this->containsAny($text, [
            'آمار', 'تحلیل آماری', 'آزمون فرض', 'توزیع احتمال', 'رگرسیون',
            'میانگین', 'واریانس', 'انحراف معیار', 'پیش‌بینی', 'پیش بینی',
            'statistical analysis', 'regression', 'hypothesis test', 'forecast'
        ]);
        $genericDataSignal = $this->containsAny($text, ['داده', 'dataset', 'data analysis']);

        if ($explicitStatisticsSignal || ($genericDataSignal && empty($modules))) {
            $domains[] = 'تحلیل داده و آمار';
            $methods[] = 'تحلیل آماری';
            $methods[] = 'تحلیل داده';
            $methods[] = 'مدل‌سازی آماری';
            $modules[] = [
                'name' => 'StatLab',
                'title' => 'تحلیل آماری و داده',
                'url' => '/software/statlab-analyzer/'
            ];
        }

        /*
         * مدیریت پروژه
         */
        if (
            $this->containsAny($text, [
                'پروژه',
                'تاخیر پروژه',
                'تأخیر پروژه',
                'زمان پروژه',
                'ریسک پروژه',
                'برنامه پروژه'
            ])
        ) {
            $domains[] = 'مدیریت پروژه';

            $methods[] = 'تحلیل پروژه';
            $methods[] = 'مدیریت زمان';
            $methods[] = 'مدیریت ریسک';

            $modules[] = [
                'name' => 'PMBOK Analyzer',
                'title' => 'تحلیل و مدیریت پروژه',
                'url' => '/software/pmbok-analyzer/'
            ];
        }

        /*
         * تحلیل نیازمندی / فرآیند کسب‌وکار
         */
        if (
            $this->containsAny($text, [
                'نیازمندی',
                'نیازمندی ها',
                'نیازمندی‌ها',
                'فرآیند',
                'فرایند',
                'کسب و کار',
                'کسب‌وکار',
                'سیستم'
            ])
        ) {
            $domains[] = 'تحلیل کسب‌وکار و فرآیند';

            $methods[] = 'تحلیل کسب‌وکار';
            $methods[] = 'تحلیل نیازمندی';
            $methods[] = 'مدل‌سازی فرآیند';

            $modules[] = [
                'name' => 'BABOK Analyzer',
                'title' => 'تحلیل کسب‌وکار و نیازمندی',
                'url' => '/software/babok-analyzer/'
            ];
        }

        $domains = array_values(array_unique($domains));
        $methods = array_values(array_unique($methods));
        $uniqueModules = [];
        foreach ($modules as $module) {
            if (!isset($uniqueModules[$module['url']])) {
                $uniqueModules[$module['url']] = $module;
            }
        }
        $modules = array_values($uniqueModules);

        if (empty($domains)) {
            return [
                'success' => true,
                'confidence' => 'low',
                'domains' => [],
                'methods' => [],
                'modules' => [],
                'message' =>
                    'برای تشخیص دقیق‌تر، مسئله شما نیاز به اطلاعات بیشتری دارد.'
            ];
        }

        return [
            'success' => true,
            'confidence' => count($domains) >= 2 ? 'medium' : 'initial',
            'domains' => $domains,
            'methods' => $methods,
            'modules' => $modules,
            'message' => $guidanceNotes !== []
                ? implode(' ', array_values(array_unique($guidanceNotes)))
                : 'بر اساس توضیح اولیه شما، مسیرهای زیر برای بررسی مناسب به نظر می‌رسند.'
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = strtr($text, [
            'ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ۀ' => 'ه', 'ة' => 'ه',
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ـ' => '', '‌' => '',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
        ]);
        $text = mb_strtolower($text, 'UTF-8');
        return preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    }

    private function containsAny($text, array $keywords)
    {
        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeText((string) $keyword);
            if ($normalizedKeyword !== '' && mb_strpos($text, $normalizedKeyword, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }
}
