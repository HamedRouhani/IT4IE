<?php

namespace App\Services;

use App\Models\Technique;

class RequirementService
{
    private Technique $techniqueModel;
    private array $allTechniques = [];
    private float $maxPossibleScore = 0;
    
    // ============================================================
    // حوزه‌های دانشی BABOK v3
    // ============================================================
    private array $knowledgeAreas = [
        'KA1' => [
            'name' => 'Business Analysis Planning and Monitoring',
            'persian' => 'برنامه‌ریزی و نظارت بر تحلیل کسب‌وکار',
            'keywords' => ['برنامه‌ریزی', 'نظارت', 'رویکرد', 'ذی‌نفع', 'حاکمیت', 'بهبود', 'عملکرد', 'برآورد', 'ریسک']
        ],
        'KA2' => [
            'name' => 'Elicitation and Collaboration',
            'persian' => 'الهام‌گیری و همکاری',
            'keywords' => ['الهام‌گیری', 'مصاحبه', 'کارگاه', 'همکاری', 'ذی‌نفع', 'ارتباط', 'جمع‌آوری', 'نیازمندی', 'گروه']
        ],
        'KA3' => [
            'name' => 'Requirements Life Cycle Management',
            'persian' => 'مدیریت چرخه حیات نیازمندی‌ها',
            'keywords' => ['نیازمندی', 'چرخه حیات', 'ردیابی', 'اولویت‌بندی', 'تغییرات', 'تأیید', 'نگهداری', 'نسخه‌بندی']
        ],
        'KA4' => [
            'name' => 'Strategy Analysis',
            'persian' => 'تحلیل استراتژیک',
            'keywords' => ['استراتژی', 'وضعیت فعلی', 'وضعیت مطلوب', 'تحلیل شکاف', 'راه‌حل', 'چشم‌انداز', 'اهداف', 'SWOT']
        ],
        'KA5' => [
            'name' => 'Requirements Analysis and Design Definition',
            'persian' => 'تحلیل و تعریف طراحی نیازمندی‌ها',
            'keywords' => ['تحلیل', 'طراحی', 'مدل‌سازی', 'اعتبارسنجی', 'راستی‌آزمایی', 'معماری', 'گزینه‌ها', 'راه‌حل', 'ارزش']
        ],
        'KA6' => [
            'name' => 'Solution Evaluation',
            'persian' => 'ارزیابی راه‌حل',
            'keywords' => ['ارزیابی', 'عملکرد', 'اندازه‌گیری', 'محدودیت', 'بهبود', 'ارزش', 'KPI', 'بازخورد', 'تحلیل']
        ]
    ];

    // ============================================================
    // کلمات کلیدی اختصاصی هر تکنیک
    // ============================================================
    private array $techniqueKeywords = [
        'Acceptance and Evaluation Criteria' => ['پذیرش', 'معیار', 'ارزیابی', 'تأیید', 'کیفیت', 'آزمون'],
        'Backlog Management' => ['بک‌لاگ', 'اولویت‌بندی', 'مدیریت', 'چابک', 'اسپرینت', 'محصول'],
        'Balanced Scorecard' => ['کارت امتیازی', 'عملکرد', 'استراتژی', 'هدف', 'سنجش', 'مالی'],
        'Benchmarking and Market Analysis' => ['معیارسنجی', 'بازار', 'رقبا', 'تحلیل', 'مقایسه', 'صنعت'],
        'Brainstorming' => ['طوفان فکری', 'ایده', 'خلاقیت', 'گروهی', 'نوآوری', 'مشارکت'],
        'Business Capability Analysis' => ['قابلیت', 'کسب‌وکار', 'تحلیل', 'استراتژی', 'هم‌راستایی', 'سازمان'],
        'Business Cases' => ['توجیه اقتصادی', 'کسب‌وکار', 'هزینه', 'سود', 'ROI', 'سرمایه‌گذاری'],
        'Business Model Canvas' => ['بیزینس مدل', 'کانوا', 'ارزش', 'مشتری', 'درآمد', 'هزینه'],
        'Business Rules Analysis' => ['قوانین', 'کسب‌وکار', 'محدودیت', 'سیاست', 'مقررات', 'شرایط'],
        'Collaborative Games' => ['بازی', 'گروهی', 'همکاری', 'الهام‌گیری', 'تعامل', 'خلاقیت'],
        'Concept Modeling' => ['مدل مفهومی', 'مفهوم', 'روابط', 'موجودیت', 'داده', 'ساختار'],
        'Data Dictionary' => ['دیکشنری داده', 'داده', 'تعریف', 'ساختار', 'عناصر', 'متاداده'],
        'Data Flow Diagrams' => ['نمودار جریان داده', 'جریان', 'داده', 'فرآیند', 'ورودی', 'خروجی'],
        'Data Mining' => ['داده‌کاوی', 'الگو', 'تحلیل', 'داده', 'کشف', 'دانش'],
        'Data Modeling' => ['مدل‌سازی داده', 'داده', 'ساختار', 'موجودیت', 'رابطه', 'ERD'],
        'Decision Analysis' => ['تحلیل تصمیم', 'تصمیم‌گیری', 'گزینه‌ها', 'ارزیابی', 'معیار', 'ریسک'],
        'Decision Modeling' => ['مدل تصمیم', 'تصمیم‌گیری', 'درخت', 'جدول', 'منطق', 'شرایط'],
        'Document Analysis' => ['تحلیل مستندات', 'مستندات', 'اسناد', 'بررسی', 'استخراج', 'نیازمندی'],
        'Estimation' => ['تخمین', 'زمان', 'هزینه', 'منابع', 'پیچیدگی', 'برنامه‌ریزی'],
        'Financial Analysis' => ['تحلیل مالی', 'مالی', 'ROI', 'سرمایه', 'هزینه', 'سودآوری'],
        'Focus Groups' => ['گروه متمرکز', 'گروه', 'بازخورد', 'کاربر', 'نظر', 'کیفی'],
        'Functional Decomposition' => ['تجزیه کارکردی', 'کارکرد', 'جزء', 'سیستم', 'ساختار', 'ماژول'],
        'Glossary' => ['واژه‌نامه', 'اصطلاحات', 'تعریف', 'زبان', 'درک مشترک', 'دانش'],
        'Interface Analysis' => ['تحلیل رابط', 'رابط', 'سیستم', 'اتصال', 'یکپارچه‌سازی', 'نقاط تماس'],
        'Interviews' => ['مصاحبه', 'ذی‌نفع', 'سوال', 'پاسخ', 'جمع‌آوری', 'نیازمندی'],
        'Item Tracking' => ['رهگیری', 'آیتم', 'پیگیری', 'مدیریت', 'وظیفه', 'پیشرفت'],
        'Lessons Learned' => ['درس آموخته', 'تجربه', 'بهبود', 'پروژه', 'بازخورد', 'دانش'],
        'Metrics and Key Performance Indicators (KPIs)' => ['KPI', 'معیار', 'عملکرد', 'سنجش', 'هدف', 'اندازه‌گیری'],
        'Mind Mapping' => ['نقشه ذهنی', 'ایده', 'سازماندهی', 'تفکر', 'خلاقیت', 'رابطه'],
        'Non-Functional Requirements Analysis' => ['نیازمندی غیرعملیاتی', 'عملکرد', 'امنیت', 'کیفیت', 'مقیاس‌پذیری', 'قابلیت اعتماد'],
        'Observation' => ['مشاهده', 'کاربر', 'رفتار', 'فرآیند', 'محیط', 'واقعی'],
        'Organizational Modeling' => ['مدل سازمانی', 'سازمان', 'نقش', 'ساختار', 'مسئولیت', 'روابط'],
        'Prioritization' => ['اولویت‌بندی', 'نیازمندی', 'ارزش', 'فوریت', 'MoSCoW', 'تصمیم‌گیری'],
        'Process Analysis' => ['تحلیل فرآیند', 'فرآیند', 'بهبود', 'جریان', 'کارایی', 'بازنگری'],
        'Process Modeling' => ['مدل‌سازی فرآیند', 'BPMN', 'جریان', 'مراحل', 'تصمیم', 'فعالیت'],
        'Prototyping' => ['نمونه‌سازی', 'نمونه اولیه', 'اعتبارسنجی', 'طراحی', 'تعامل', 'بازخورد'],
        'Reviews' => ['بازبینی', 'بررسی', 'نیازمندی', 'کیفیت', 'تأیید', 'مستندات'],
        'Risk Analysis and Management' => ['ریسک', 'تحلیل ریسک', 'مدیریت ریسک', 'شناسایی', 'ارزیابی', 'کنترل'],
        'Roles and Permissions Matrix' => ['نقش', 'دسترسی', 'مجوز', 'امنیت', 'کاربر', 'سطوح دسترسی'],
        'Root Cause Analysis' => ['تحلیل ریشه‌ای', 'علت', 'مشکل', 'خطا', 'شناسایی', 'حل'],
        'Scope Modeling' => ['مدل‌سازی محدوده', 'محدوده', 'مرز', 'سیستم', 'ورودی', 'خروجی'],
        'Sequence Diagrams' => ['نمودار توالی', 'تعامل', 'زمان', 'شیء', 'پیام', 'ترتیب'],
        'Stakeholder List, Map, or Personas' => ['ذی‌نفع', 'شخصیت', 'نقش', 'تحلیل', 'نقشه', 'لیست'],
        'State Modeling' => ['مدل حالت', 'حالت', 'تغییر', 'موجودیت', 'چرخه حیات', 'رویداد'],
        'Survey or Questionnaire' => ['نظرسنجی', 'پرسشنامه', 'داده', 'کمی', 'کیفی', 'بازخورد'],
        'SWOT Analysis' => ['SWOT', 'نقاط قوت', 'ضعف', 'فرصت', 'تهدید', 'استراتژی'],
        'Use Cases and Scenarios' => ['سناریو', 'کیس استفاده', 'تعامل', 'کاربر', 'جریان', 'شرایط'],
        'User Stories' => ['داستان کاربری', 'کاربر', 'نیازمندی', 'چابک', 'Agile', 'محصول'],
        'Vendor Assessment' => ['ارزیابی فروشنده', 'تامین‌کننده', 'برون‌سپاری', 'معیار', 'انتخاب', 'مقایسه'],
        'Workshops' => ['کارگاه', 'گروهی', 'همکاری', 'الهام‌گیری', 'مشارکت', 'تصمیم‌گیری']
    ];

    // ============================================================
    // وزن‌دهی به کلمات کلیدی (گسترده برای همه صنایع)
    // ============================================================
    private array $keywordWeights = [
        // کلمات کلیدی عمومی BABOK
        'نیازمندی' => 5, 'تحلیل' => 4, 'طراحی' => 4, 'ارزیابی' => 4, 'مدیریت' => 3,
        'برنامه‌ریزی' => 3, 'الهام‌گیری' => 3, 'مصاحبه' => 3, 'کارگاه' => 3,
        'استراتژی' => 3, 'فرآیند' => 3, 'مدل' => 3, 'داده' => 3,
        'عملکرد' => 3, 'کیفیت' => 3, 'ریسک' => 3, 'ارزش' => 3,
        'سیستم' => 2, 'کاربر' => 2, 'مشتری' => 2, 'ذی‌نفع' => 2,
        'پیاده‌سازی' => 2, 'آزمون' => 2, 'تأیید' => 2, 'اعتبارسنجی' => 2,
        'جریان' => 2, 'ساختار' => 2, 'گروه' => 2, 'همکاری' => 2,
        'چابک' => 2, 'Agile' => 2, 'هزینه' => 2, 'زمان' => 2,
        'مستندات' => 1, 'گزارش' => 1, 'اطلاعات' => 1, 'ارتباط' => 1,
        'بازخورد' => 1, 'تغییر' => 1, 'بهبود' => 1, 'هدف' => 1,
        'منابع' => 1, 'امنیت' => 1, 'مقیاس‌پذیری' => 1, 'قابلیت اعتماد' => 1,
        
        // کلمات کلیدی حوزه مالی و حسابداری
        'مالی' => 3, 'حسابداری' => 3, 'فاکتور' => 3, 'حسابرسی' => 3,
        'بودجه' => 3, 'درآمد' => 2, 'هزینه' => 2, 'سود' => 2,
        'تراکنش' => 2, 'پرداخت' => 2, 'صورت مالی' => 3,
        
        // کلمات کلیدی حوزه تولید و صنعت
        'تولید' => 3, 'انبار' => 3, 'موجودی' => 3, 'سفارش' => 3,
        'تامین' => 2, 'مواد اولیه' => 3, 'خط تولید' => 3, 'کیفیت' => 3,
        'کنترل کیفیت' => 3, 'بسته‌بندی' => 2, 'توزیع' => 2,
        
        // کلمات کلیدی حوزه خدمات و پشتیبانی
        'خدمات' => 3, 'پشتیبانی' => 3, 'مشتری' => 3, 'رضایت' => 2,
        'درخواست' => 2, 'تیکت' => 2, 'پیگیری' => 2, 'ارائه خدمات' => 3,
        
        // کلمات کلیدی حوزه منابع انسانی
        'کارمند' => 2, 'پرسنل' => 2, 'حقوق' => 3, 'دستمزد' => 3,
        'ارزیابی عملکرد' => 3, 'آموزش' => 2, 'استخدام' => 2,
        
        // کلمات کلیدی حوزه فروش و بازاریابی
        'فروش' => 3, 'بازاریابی' => 3, 'مشتری' => 3, 'تبلیغات' => 2,
        'قیمت' => 2, 'تخفیف' => 2, 'پروموشن' => 2,
        
        // کلمات کلیدی حوزه فناوری اطلاعات
        'نرم‌افزار' => 3, 'سخت‌افزار' => 2, 'زیرساخت' => 3, 'شبکه' => 2,
        'امنیت سایبری' => 3, 'داده‌ها' => 3, 'سیستم' => 3,
        
        // کلمات کلیدی حوزه سلامت و پزشکی
        'بیمار' => 3, 'درمان' => 3, 'پزشکی' => 3, 'بیمه' => 3,
        'سوابق پزشکی' => 3, 'نسخه' => 2, 'دارو' => 2,
        
        // کلمات کلیدی حوزه آموزش
        'آموزش' => 3, 'دانش‌آموز' => 2, 'دانشجو' => 2, 'کلاس' => 2,
        'درس' => 2, 'آزمون' => 2, 'ارزیابی' => 2,
        
        // کلمات کلیدی حوزه حقوقی و قراردادها
        'قرارداد' => 3, 'حقوقی' => 3, 'قانون' => 3, 'مقررات' => 3,
        'انطباق' => 3, 'مجوز' => 2, 'ضمانت' => 2,
        
        // کلمات کلیدی حوزه تحقیق و توسعه
        'تحقیق' => 3, 'توسعه' => 3, 'نوآوری' => 3, 'آزمایشگاه' => 2,
        'تحقیقات' => 3, 'R&D' => 3,
        
        // کلمات کلیدی عمومی برای هر صنعت
        'مشکل' => 2, 'چالش' => 2, 'نیاز' => 3, 'هدف' => 2,
        'فرصت' => 2, 'تهدید' => 2, 'نقاط قوت' => 2, 'نقاط ضعف' => 2
    ];

    // ============================================================
    // الگوهای تشخیص جملات نیازمندی (گسترده)
    // ============================================================
    private array $sentencePatterns = [
        'system_requirement' => '/سیستم باید (?:قابلیت|امکان|بتواند) (?P<action>.+?) (?:را داشته باشد|فراهم کند|دهد|انجام دهد|مدیریت کند|پشتیبانی کند)/u',
        'user_requirement' => '/کاربران باید بتوانند (?P<action>.+?) (?:انجام دهند|استفاده کنند|داشته باشند|مدیریت کنند|مشاهده کنند)/u',
        'manager_requirement' => '/مدیران باید بتوانند (?P<action>.+?) (?:مدیریت کنند|کنترل کنند|نظارت کنند|رسیدگی کنند|گزارش دهند)/u',
        'process_requirement' => '/فرآیند (?:باید|می‌بایست) (?P<action>.+?) (?:شود|انجام شود|طراحی شود|بهبود یابد|بهینه شود)/u',
        'relationship_requirement' => '/(?:نیاز به|باید) (?P<action>.+?) (?:روابط|ارتباطات|تعاملات|همکاری) (?:بین افراد|بین تیم‌ها|سازمانی|با مشتریان)/u',
        'financial_requirement' => '/(?:مسائل|مشکلات|نیازهای) (?:مالی|حسابداری|فاکتور|حسابرسی|بودجه) (?P<action>.+?) (?:داریم|است|وجود دارد|مطرح است)/u',
        'production_requirement' => '/(?:تولید|انبار|موجودی|سفارش|مواد اولیه) (?P<action>.+?) (?:داریم|نیاز داریم|مشکل دارد)/u',
        'service_requirement' => '/(?:خدمات|پشتیبانی|مشتری) (?P<action>.+?) (?:ارائه می‌شود|نیاز است|باید انجام شود)/u',
        'general_requirement' => '/نیاز به (?P<action>.+?) (?:داریم|است|می‌باشد|وجود دارد)/u',
        'problem_requirement' => '/(?:مشکل|چالش|مسئله) (?P<action>.+?) (?:داریم|وجود دارد|مطرح است|پیش آمده)/u',
        'security_requirement' => '/امنیت (?:داده‌ها|اطلاعات|سیستم|شبکه) باید (?P<action>.+?) (?:شود|رعایت شود|تأمین شود|برقرار شود)/u',
        'performance_requirement' => '/عملکرد سیستم باید (?P<action>.+?) (?:باشد|ارائه شود|بهبود یابد|بهینه شود)/u'
    ];

    // ============================================================
    // نگاشت عبارات به نیازمندی‌های خاص (برای موارد خاص)
    // ============================================================
    private array $phraseMapping = [
        // حوزه مالی و حسابداری
        'مسائل مالی' => [
            'title' => 'مدیریت جامع مالی و کنترل هزینه‌ها',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت جامع مالی شامل بودجه‌بندی، کنترل هزینه‌ها، پیش‌بینی درآمد، گزارش‌دهی مالی و تحلیل شاخص‌های کلیدی مالی را فراهم کند.',
            'babok_reference' => 'مدیریت مالی و کنترل هزینه‌ها'
        ],
        'فاکتور' => [
            'title' => 'سیستم مدیریت فاکتورها و صورتحساب',
            'type' => 'functional',
            'description' => 'سیستم باید امکان ثبت، صدور، پیگیری و مدیریت کامل فاکتورها را با قابلیت تولید خودکار، اتصال به سیستم حسابداری و گزارش‌دهی دقیق فراهم کند.',
            'babok_reference' => 'مدیریت صورتحساب و فاکتور'
        ],
        'حسابرسی' => [
            'title' => 'سیستم حسابرسی و انطباق',
            'type' => 'functional',
            'description' => 'سیستم باید امکان انجام حسابرسی داخلی و خارجی را با قابلیت ردیابی کامل تراکنش‌ها، مستندسازی، گزارش‌دهی و پوشش الزامات استانداردهای حسابرسی فراهم کند.',
            'babok_reference' => 'مدیریت حسابرسی و انطباق'
        ],
        'حسابداری' => [
            'title' => 'سیستم حسابداری یکپارچه',
            'type' => 'functional',
            'description' => 'سیستم باید امکان ثبت و مدیریت کامل عملیات حسابداری شامل ثبت اسناد، دفتر کل، ترازنامه، صورت سود و زیان و صورت جریان وجوه نقد را فراهم کند.',
            'babok_reference' => 'مدیریت حسابداری و ثبت‌های مالی'
        ],
        
        // حوزه تولید و صنعت
        'تولید' => [
            'title' => 'سیستم مدیریت تولید و عملیات',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت کامل فرآیندهای تولید شامل برنامه‌ریزی تولید، کنترل کیفیت، مدیریت ماشین‌آلات، برنامه‌ریزی مواد اولیه و ردیابی تولید را فراهم کند.',
            'babok_reference' => 'مدیریت تولید و عملیات'
        ],
        'انبار' => [
            'title' => 'سیستم مدیریت انبار و موجودی',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت کامل انبار شامل دریافت، نگهداری، توزیع، کنترل موجودی، برنامه‌ریزی سفارش‌ها و گزارش‌دهی دقیق موجودی را فراهم کند.',
            'babok_reference' => 'مدیریت انبار و موجودی'
        ],
        'موجودی' => [
            'title' => 'سیستم کنترل موجودی و تامین',
            'type' => 'functional',
            'description' => 'سیستم باید امکان کنترل دقیق موجودی، مدیریت تامین‌کنندگان، پیش‌بینی نیازها، مدیریت سفارش‌ها و بهینه‌سازی سطح موجودی را فراهم کند.',
            'babok_reference' => 'مدیریت موجودی و تامین'
        ],
        'سفارش' => [
            'title' => 'سیستم مدیریت سفارشات',
            'type' => 'functional',
            'description' => 'سیستم باید امکان ثبت، پیگیری، تایید و مدیریت کامل سفارشات مشتریان را با قابلیت برنامه‌ریزی تامین و توزیع فراهم کند.',
            'babok_reference' => 'مدیریت سفارشات'
        ],
        
        // حوزه خدمات و پشتیبانی
        'خدمات' => [
            'title' => 'سیستم مدیریت خدمات و پشتیبانی',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت کامل خدمات شامل ثبت درخواست‌ها، برنامه‌ریزی خدمات، مدیریت قراردادهای خدماتی، پیگیری و ارزیابی کیفیت خدمات را فراهم کند.',
            'babok_reference' => 'مدیریت خدمات و پشتیبانی'
        ],
        'پشتیبانی' => [
            'title' => 'سیستم پشتیبانی مشتریان',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت درخواست‌های پشتیبانی، تیکت‌ها، پیگیری مشکلات، ارتباط با مشتریان و ارزیابی رضایت مشتریان را فراهم کند.',
            'babok_reference' => 'مدیریت پشتیبانی مشتریان'
        ],
        
        // حوزه منابع انسانی
        'کارمند' => [
            'title' => 'سیستم مدیریت منابع انسانی',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت کامل منابع انسانی شامل اطلاعات کارکنان، حقوق و دستمزد، ارزیابی عملکرد، آموزش، استخدام و برنامه‌ریزی نیروی انسانی را فراهم کند.',
            'babok_reference' => 'مدیریت منابع انسانی'
        ],
        'حقوق' => [
            'title' => 'سیستم مدیریت حقوق و دستمزد',
            'type' => 'functional',
            'description' => 'سیستم باید امکان محاسبه، ثبت و مدیریت حقوق و دستمزد کارکنان را با قابلیت محاسبه مالیات، بیمه و گزارش‌دهی دقیق فراهم کند.',
            'babok_reference' => 'مدیریت حقوق و دستمزد'
        ],
        
        // حوزه فروش و بازاریابی
        'فروش' => [
            'title' => 'سیستم مدیریت فروش و مشتریان',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت کامل فروش شامل ثبت سفارشات، مدیریت مشتریان، پیگیری فروش، تحلیل بازار و پیش‌بینی فروش را فراهم کند.',
            'babok_reference' => 'مدیریت فروش و مشتریان'
        ],
        'بازاریابی' => [
            'title' => 'سیستم مدیریت بازاریابی',
            'type' => 'functional',
            'description' => 'سیستم باید امکان برنامه‌ریزی، اجرا و ارزیابی کمپین‌های بازاریابی، مدیریت محتوا، تحلیل بازار و مدیریت ارتباط با مشتریان را فراهم کند.',
            'babok_reference' => 'مدیریت بازاریابی'
        ],
        
        // حوزه فناوری اطلاعات
        'نرم‌افزار' => [
            'title' => 'سیستم مدیریت نرم‌افزار و فناوری اطلاعات',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت چرخه حیات نرم‌افزار، توسعه، پیاده‌سازی، پشتیبانی و ارتقاء سیستم‌های نرم‌افزاری را فراهم کند.',
            'babok_reference' => 'مدیریت فناوری اطلاعات'
        ],
        'زیرساخت' => [
            'title' => 'سیستم مدیریت زیرساخت فناوری اطلاعات',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت زیرساخت‌های فناوری اطلاعات شامل شبکه، سرورها، ذخیره‌سازی، امنیت و پشتیبانی را فراهم کند.',
            'babok_reference' => 'مدیریت زیرساخت فناوری اطلاعات'
        ],
        
        // حوزه سلامت و پزشکی
        'بیمار' => [
            'title' => 'سیستم مدیریت اطلاعات بیماران',
            'type' => 'functional',
            'description' => 'سیستم باید امکان ثبت، ذخیره، بازیابی و مدیریت اطلاعات بیماران، سوابق پزشکی، نسخه‌ها و برنامه‌های درمانی را فراهم کند.',
            'babok_reference' => 'مدیریت اطلاعات سلامت'
        ],
        'درمان' => [
            'title' => 'سیستم مدیریت درمان و مراقبت',
            'type' => 'functional',
            'description' => 'سیستم باید امکان برنامه‌ریزی درمان، مدیریت نوبت‌دهی، پیگیری فرآیند درمان و ارتباط با بیماران را فراهم کند.',
            'babok_reference' => 'مدیریت درمان و مراقبت'
        ],
        
        // حوزه آموزش
        'آموزش' => [
            'title' => 'سیستم مدیریت آموزشی',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت دوره‌های آموزشی، ثبت‌نام دانشجویان، برنامه‌ریزی کلاس‌ها، ارزیابی و گزارش‌دهی آموزشی را فراهم کند.',
            'babok_reference' => 'مدیریت آموزشی'
        ],
        
        // حوزه حقوقی
        'قرارداد' => [
            'title' => 'سیستم مدیریت قراردادها',
            'type' => 'functional',
            'description' => 'سیستم باید امکان ثبت، پیگیری، مدیریت و نظارت بر قراردادها را با قابلیت هشدار انقضا و گزارش‌دهی فراهم کند.',
            'babok_reference' => 'مدیریت قراردادها'
        ],
        
        // حوزه تحقیق و توسعه
        'تحقیق' => [
            'title' => 'سیستم مدیریت تحقیق و توسعه',
            'type' => 'functional',
            'description' => 'سیستم باید امکان مدیریت پروژه‌های تحقیقاتی، ثبت نتایج، همکاری تیمی و انتشار یافته‌ها را فراهم کند.',
            'babok_reference' => 'مدیریت تحقیق و توسعه'
        ],
        
        // عمومی
        'مشکل' => [
            'title' => 'سیستم مدیریت مسائل و بهبود مستمر',
            'type' => 'functional',
            'description' => 'سیستم باید امکان شناسایی، ثبت، پیگیری و حل مسائل را با ابزارهای تحلیل علت ریشه‌ای و بهبود مستمر فراهم کند.',
            'babok_reference' => 'مدیریت مسائل و بهبود'
        ]
    ];

    // ============================================================
    // سرویس‌های پیشنهادی برای هر حوزه
    // ============================================================
    private array $domainSuggestions = [
        'مالی' => [
            'title' => 'سیستم جامع مدیریت مالی',
            'description' => 'سیستمی یکپارچه برای مدیریت مالی شامل بودجه‌بندی، حسابداری، گزارش‌دهی مالی، مدیریت فاکتورها و حسابرسی'
        ],
        'حسابداری' => [
            'title' => 'سیستم حسابداری یکپارچه',
            'description' => 'سیستمی کامل برای ثبت و مدیریت عملیات حسابداری شامل دفتر کل، ترازنامه و صورت‌های مالی'
        ],
        'تولید' => [
            'title' => 'سیستم برنامه‌ریزی و کنترل تولید (MRP/ERP)',
            'description' => 'سیستمی برای مدیریت کامل فرآیندهای تولید شامل برنامه‌ریزی، کنترل کیفیت و مدیریت مواد'
        ],
        'انبار' => [
            'title' => 'سیستم مدیریت انبار و موجودی (WMS)',
            'description' => 'سیستمی برای مدیریت کامل انبار شامل دریافت، نگهداری، توزیع و کنترل موجودی'
        ],
        'خدمات' => [
            'title' => 'سیستم مدیریت خدمات (CRM/Service Management)',
            'description' => 'سیستمی برای مدیریت خدمات، پشتیبانی مشتریان و بهبود کیفیت خدمات'
        ],
        'کارمند' => [
            'title' => 'سیستم مدیریت منابع انسانی (HRM)',
            'description' => 'سیستمی جامع برای مدیریت منابع انسانی شامل استخدام، آموزش، حقوق و ارزیابی عملکرد'
        ],
        'فروش' => [
            'title' => 'سیستم مدیریت فروش و مشتریان (CRM)',
            'description' => 'سیستمی برای مدیریت فروش، مشتریان، پیگیری فرصت‌ها و تحلیل بازار'
        ],
        'آموزش' => [
            'title' => 'سیستم مدیریت آموزش (LMS)',
            'description' => 'سیستمی برای مدیریت دوره‌های آموزشی، ثبت‌نام، ارزیابی و گزارش‌دهی'
        ],
        'بیمار' => [
            'title' => 'سیستم مدیریت اطلاعات سلامت (HIS)',
            'description' => 'سیستمی برای مدیریت اطلاعات بیماران، سوابق پزشکی و فرآیندهای درمانی'
        ],
        'قرارداد' => [
            'title' => 'سیستم مدیریت قراردادها (CLM)',
            'description' => 'سیستمی برای مدیریت کامل چرخه حیات قراردادها از ایجاد تا انقضا'
        ],
        'تحقیق' => [
            'title' => 'سیستم مدیریت تحقیق و توسعه (R&D)',
            'description' => 'سیستمی برای مدیریت پروژه‌های تحقیقاتی و نوآوری'
        ]
    ];

    public function __construct()
    {
        $this->techniqueModel = new Technique();
        $this->allTechniques = $this->techniqueModel->getAll();
    }

    /**
     * پردازش یکپارچه: استخراج نیازمندی + تحلیل تکنیک
     */
    public function process(string $text): array
    {
        // مرحله ۱: پردازش پیشرفته متن
        $processed = $this->advancedTextProcessing($text);
        
        // مرحله ۲: استخراج کلمات کلیدی با وزن
        $keywords = $this->extractWeightedKeywords($processed['cleaned']);
        
        // مرحله ۳: شناسایی حوزه دانشی مرتبط
        $knowledgeArea = $this->detectKnowledgeArea($processed['cleaned']);
        
        // مرحله ۴: شناسایی حوزه کاری (صنعت)
        $domain = $this->detectDomain($text);
        
        // مرحله ۵: استخراج هوشمند نیازمندی‌ها
        $requirements = $this->extractRequirementsIntelligent($text, $domain);
        
        // مرحله ۶: تحلیل و امتیازدهی به تکنیک‌ها
        $techniques = $this->analyzeTechniques($text, $keywords, $knowledgeArea);
        
        return [
            'requirements' => $requirements,
            'techniques' => $techniques,
            'domain' => $domain,
            'suggestion' => $this->getDomainSuggestion($domain, $text),
            'stats' => [
                'total' => count($requirements['functional']) + count($requirements['non_functional']),
                'functional' => count($requirements['functional']),
                'non_functional' => count($requirements['non_functional']),
                'techniques' => count($techniques)
            ]
        ];
    }

    /**
     * شناسایی حوزه کاری (صنعت) از متن
     */
    private function detectDomain(string $text): string
    {
        $domains = [
            'مالی' => ['مالی', 'حسابداری', 'فاکتور', 'حسابرسی', 'بودجه', 'درآمد', 'هزینه'],
            'تولید' => ['تولید', 'انبار', 'موجودی', 'سفارش', 'خط تولید', 'مواد اولیه'],
            'خدمات' => ['خدمات', 'پشتیبانی', 'مشتری', 'درخواست', 'تیکت'],
            'منابع انسانی' => ['کارمند', 'پرسنل', 'حقوق', 'دستمزد', 'استخدام', 'آموزش'],
            'فروش' => ['فروش', 'بازاریابی', 'مشتری', 'تبلیغات', 'قیمت'],
            'فناوری اطلاعات' => ['نرم‌افزار', 'سخت‌افزار', 'زیرساخت', 'شبکه', 'سیستم'],
            'سلامت' => ['بیمار', 'درمان', 'پزشکی', 'بیمه', 'نسخه'],
            'آموزش' => ['آموزش', 'دانش‌آموز', 'دانشجو', 'کلاس', 'درس'],
            'حقوقی' => ['قرارداد', 'حقوقی', 'قانون', 'مقررات', 'انطباق'],
            'تحقیق و توسعه' => ['تحقیق', 'توسعه', 'نوآوری', 'آزمایشگاه', 'R&D']
        ];
        
        $scores = [];
        foreach ($domains as $domain => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (mb_stripos($text, $keyword) !== false) {
                    $score += 2;
                }
            }
            $scores[$domain] = $score;
        }
        
        arsort($scores);
        $topDomain = key($scores);
        
        return ($scores[$topDomain] > 0) ? $topDomain : 'عمومی';
    }

    /**
     * دریافت پیشنهاد سیستم برای حوزه شناسایی شده
     */
    private function getDomainSuggestion(string $domain, string $text): ?array
    {
        // پیدا کردن بهترین تطابق با کلمات کلیدی
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($this->domainSuggestions as $key => $suggestion) {
            $score = 0;
            foreach (explode(' ', $key) as $word) {
                if (mb_stripos($text, $word) !== false) {
                    $score += 3;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $suggestion;
            }
        }
        
        // اگر تطابق خوبی پیدا نشد، از حوزه استفاده کن
        if ($bestScore < 3 && isset($this->domainSuggestions[$domain])) {
            return $this->domainSuggestions[$domain];
        }
        
        return $bestMatch;
    }

    /**
     * استخراج هوشمند نیازمندی‌ها با تشخیص جملات و کلمات کلیدی
     */
    private function extractRequirementsIntelligent(string $text, string $domain): array
    {
        $result = ['functional' => [], 'non_functional' => []];
        $detectedTitles = [];
        $sentences = $this->splitIntoSentences($text);
        
        // ۱. بررسی عبارات خاص با نگاشت مستقیم
        foreach ($this->phraseMapping as $phrase => $mapped) {
            if (mb_stripos($text, $phrase) !== false) {
                $title = $mapped['title'];
                if (!in_array($title, $detectedTitles)) {
                    $result[$mapped['type']][] = [
                        'title' => $title,
                        'description' => $mapped['description'],
                        'babok_reference' => $mapped['babok_reference'] ?? 'استاندارد BABOK v3',
                        'source_phrase' => $phrase,
                        'domain' => $domain
                    ];
                    $detectedTitles[] = $title;
                }
            }
        }
        
        // ۲. بررسی الگوهای جملات
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            $matched = false;
            
            foreach ($this->sentencePatterns as $pattern) {
                if (preg_match($pattern, $sentence, $matches)) {
                    $action = trim($matches['action'] ?? '');
                    if (!empty($action)) {
                        $type = $this->detectRequirementType($sentence);
                        $title = $this->extractTitleFromSentence($sentence, $action);
                        $description = $this->enhanceDescription($sentence, $action, $domain);
                        
                        if (!in_array($title, $detectedTitles)) {
                            $result[$type][] = [
                                'title' => $title,
                                'description' => $description,
                                'babok_reference' => $this->detectBabokReference($sentence),
                                'source_sentence' => $sentence,
                                'domain' => $domain
                            ];
                            $detectedTitles[] = $title;
                            $matched = true;
                        }
                    }
                }
                if ($matched) break;
            }
            
            // ۳. اگر الگوی خاصی تشخیص داده نشد، با کلمات کلیدی عمومی بررسی کن
            if (!$matched) {
                $req = $this->extractFromKeywords($sentence, $domain);
                if ($req && !in_array($req['title'], $detectedTitles)) {
                    $result[$req['type']][] = $req;
                    $detectedTitles[] = $req['title'];
                }
            }
        }
        
        // ۴. اگر هیچ موردی پیدا نشد، از روش کلاسیک استفاده کن
        if (empty($result['functional']) && empty($result['non_functional'])) {
            $result = $this->extractRequirementsClassic($text, $domain);
        }
        
        // ۵. اگر باز هم چیزی پیدا نشد، نیازمندی پیش‌فرض اضافه کن
        if (empty($result['functional']) && empty($result['non_functional'])) {
            $result['functional'][] = [
                'title' => 'نیازمندی پایه پروژه',
                'description' => 'سیستم باید نیازمندی‌های پایه پروژه را بر اساس اهداف تعیین‌شده و استانداردهای BABOK v3 پوشش دهد.',
                'domain' => $domain
            ];
        }
        
        return $result;
    }

    /**
     * استخراج نیازمندی از کلمات کلیدی عمومی
     */
    private function extractFromKeywords(string $sentence, string $domain): ?array
    {
        // کلمات کلیدی عمومی برای تشخیص نیازمندی
        $generalKeywords = [
            'نیاز' => ['title' => 'نیازمندی استخراج شده', 'type' => 'functional'],
            'باید' => ['title' => 'الزام سیستم', 'type' => 'functional'],
            'می‌خواهیم' => ['title' => 'درخواست کاربران', 'type' => 'functional'],
            'مشکل' => ['title' => 'حل مسئله', 'type' => 'functional'],
            'چالش' => ['title' => 'مدیریت چالش', 'type' => 'functional']
        ];
        
        foreach ($generalKeywords as $keyword => $info) {
            if (mb_stripos($sentence, $keyword) !== false) {
                // استخراج عنوان از جمله
                $title = mb_substr($sentence, 0, 50);
                if (mb_strlen($title) > 50) $title = mb_substr($title, 0, 47) . '...';
                
                return [
                    'title' => $title,
                    'type' => $info['type'],
                    'description' => $sentence . ' (استخراج شده از متن)',
                    'babok_reference' => 'استاندارد BABOK v3',
                    'source_sentence' => $sentence,
                    'domain' => $domain
                ];
            }
        }
        
        return null;
    }

    /**
     * تشخیص نوع نیازمندی از جمله
     */
    private function detectRequirementType(string $sentence): string
    {
        $nonFunctionalKeywords = ['امنیت', 'سرعت', 'عملکرد', 'مقیاس', 'پایداری', 'کیفیت', 'دسترس‌پذیری', 'رمزنگاری', 'قابلیت اعتماد'];
        $functionalKeywords = ['کاربر', 'پرداخت', 'گزارش', 'مدیریت', 'جستجو', 'ذخیره', 'ویرایش', 'حذف', 'ورود', 'ثبت', 'روابط', 'ارتباط', 'همکاری', 'تیم', 'مالی', 'حسابداری', 'فاکتور', 'حسابرسی', 'تولید', 'انبار', 'خدمات'];
        
        foreach ($nonFunctionalKeywords as $keyword) {
            if (mb_stripos($sentence, $keyword) !== false) {
                return 'non_functional';
            }
        }
        
        foreach ($functionalKeywords as $keyword) {
            if (mb_stripos($sentence, $keyword) !== false) {
                return 'functional';
            }
        }
        
        if (preg_match('/امنیت|سرعت|عملکرد|مقیاس|پایداری|کیفیت|دسترس‌پذیری|رمزنگاری/u', $sentence)) {
            return 'non_functional';
        }
        
        return 'functional';
    }

    /**
     * استخراج عنوان از جمله
     */
    private function extractTitleFromSentence(string $sentence, string $action): string
    {
        $title = preg_replace('/^سیستم باید|^کاربران باید بتوانند|^مدیران باید بتوانند|^فرآیند باید|^امنیت|^نیاز به|^مشکل|^چالش/', '', $sentence);
        $title = preg_replace('/ (?:را داشته باشد|فراهم کند|دهد|انجام دهد|انجام شوند|استفاده کنند|داشته باشند|شود|رعایت شود|تأمین شود|مدیریت کنند|کنترل کنند|نظارت کنند|رسیدگی کنند|پشتیبانی کند|بهبود یابد|بهینه شود)$/', '', $title);
        $title = trim($title);
        
        if (mb_strlen($title) > 50) {
            $title = mb_substr($title, 0, 47) . '...';
        }
        
        return $title ?: 'نیازمندی استخراج شده';
    }

    /**
     * بهبود توضیحات نیازمندی
     */
    private function enhanceDescription(string $sentence, string $action, string $domain): string
    {
        if (preg_match('/^نیاز به (.+)/u', $sentence, $matches)) {
            $action = trim($matches[1]);
            return "سیستم باید قابلیت {$action} را با رعایت استانداردهای BABOK v3 و متناسب با نیازهای حوزه {$domain} فراهم کند.";
        }
        
        if (preg_match('/^مشکل (.+)/u', $sentence, $matches)) {
            $action = trim($matches[1]);
            return "سیستم باید راه‌حلی برای حل مشکل {$action} ارائه دهد و فرآیندهای مرتبط را بهبود بخشد.";
        }
        
        $description = trim($sentence);
        if (!preg_match('/[.!?]$/u', $description)) {
            $description .= '.';
        }
        
        return $description;
    }

    /**
     * تشخیص مرجع BABOK از جمله
     */
    private function detectBabokReference(string $sentence): string
    {
        $references = [
            'مالی' => 'مدیریت مالی و کنترل هزینه‌ها',
            'حسابداری' => 'مدیریت حسابداری و ثبت‌های مالی',
            'فاکتور' => 'مدیریت صورتحساب و فاکتور',
            'حسابرسی' => 'مدیریت حسابرسی و انطباق',
            'تولید' => 'مدیریت تولید و عملیات',
            'انبار' => 'مدیریت انبار و موجودی',
            'خدمات' => 'مدیریت خدمات و پشتیبانی',
            'کارمند' => 'مدیریت منابع انسانی',
            'فروش' => 'مدیریت فروش و مشتریان',
            'آموزش' => 'مدیریت آموزشی',
            'بیمار' => 'مدیریت اطلاعات سلامت',
            'قرارداد' => 'مدیریت قراردادها',
            'تحقیق' => 'مدیریت تحقیق و توسعه',
            'روابط' => 'مدیریت ذی‌نفعان و ارتباطات',
            'ارتباط' => 'مدیریت ارتباطات و همکاری',
            'همکاری' => 'همکاری و تعامل گروهی',
            'تیم' => 'مدیریت منابع انسانی',
            'ذی‌نفع' => 'شناسایی و مدیریت ذی‌نفعان',
            'پرداخت' => 'مدیریت تراکنش‌های مالی',
            'کاربر' => 'مدیریت موجودیت‌ها و نقش‌ها',
            'گزارش' => 'تحلیل داده و گزارش‌سازی',
            'امنیت' => 'امنیت اطلاعات',
            'سرعت' => 'عملکرد سیستم'
        ];
        
        foreach ($references as $keyword => $reference) {
            if (mb_stripos($sentence, $keyword) !== false) {
                return $reference;
            }
        }
        
        return 'استاندارد BABOK v3';
    }

    /**
     * تقسیم متن به جملات
     */
    private function splitIntoSentences(string $text): array
    {
        $sentences = preg_split('/[.!?]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $sentences);
    }

    /**
     * استخراج نیازمندی‌ها به روش کلاسیک
     */
    private function extractRequirementsClassic(string $text, string $domain): array
    {
        $result = ['functional' => [], 'non_functional' => []];
        $detected = [];
        
        foreach ($this->phraseMapping as $phrase => $mapped) {
            if (mb_stripos($text, $phrase) !== false) {
                $title = $mapped['title'];
                if (!in_array($title, $detected)) {
                    $result[$mapped['type']][] = [
                        'title' => $title,
                        'description' => $mapped['description'],
                        'babok_reference' => $mapped['babok_reference'] ?? 'استاندارد BABOK v3',
                        'domain' => $domain
                    ];
                    $detected[] = $title;
                }
            }
        }
        
        return $result;
    }

    /**
     * پردازش پیشرفته متن
     */
    private function advancedTextProcessing(string $text): array
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        
        return [
            'cleaned' => $cleaned,
            'word_count' => str_word_count($cleaned),
            'char_count' => mb_strlen($cleaned)
        ];
    }

    /**
     * استخراج کلمات کلیدی با وزن
     */
    private function extractWeightedKeywords(string $text): array
    {
        $words = array_unique(explode(' ', mb_strtolower($text)));
        $keywords = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) < 2) continue;
            
            $weight = $this->keywordWeights[$word] ?? 1;
            $keywords[$word] = $weight;
        }
        
        // کلمات کلیدی BABOK
        $babokKeywords = [
            'نیازمندی' => 5, 'تحلیل' => 4, 'طراحی' => 4, 'ارزیابی' => 4,
            'مدیریت' => 3, 'روابط' => 3, 'ارتباط' => 3, 'همکاری' => 3,
            'استراتژی' => 3, 'فرآیند' => 3, 'مدل' => 3, 'داده' => 3,
            'عملکرد' => 3, 'کیفیت' => 3, 'ریسک' => 3, 'ارزش' => 3,
            'مالی' => 3, 'حسابداری' => 3, 'تولید' => 3, 'خدمات' => 3
        ];
        
        foreach ($babokKeywords as $word => $weight) {
            if (mb_strpos($text, $word) !== false) {
                $keywords[$word] = max($keywords[$word] ?? 0, $weight);
            }
        }
        
        arsort($keywords);
        return $keywords;
    }

    /**
     * تشخیص حوزه دانشی مرتبط با متن
     */
    private function detectKnowledgeArea(string $text): ?array
    {
        $scores = [];
        foreach ($this->knowledgeAreas as $code => $area) {
            $score = 0;
            foreach ($area['keywords'] as $keyword) {
                if (mb_strpos($text, $keyword) !== false) {
                    $score += 2;
                }
            }
            $scores[$code] = $score;
        }
        
        arsort($scores);
        $topArea = key($scores);
        
        return $topArea ? $this->knowledgeAreas[$topArea] : null;
    }

    /**
     * تحلیل و پیشنهاد تکنیک‌ها با امتیازدهی پیشرفته
     */
    private function analyzeTechniques(string $text, array $keywords, ?array $knowledgeArea): array
    {
        $this->maxPossibleScore = $this->calculateMaxPossibleScore($keywords);
        
        $ranked = [];
        foreach ($this->allTechniques as $technique) {
            $score = $this->calculateAdvancedScore($technique, $text, $keywords, $knowledgeArea);
            if ($score > 0) {
                $ranked[] = [
                    'technique' => $technique,
                    'score' => round($score, 1),
                    'score_percent' => $this->maxPossibleScore > 0 ? round(($score / $this->maxPossibleScore) * 100, 1) : 0,
                    'reason' => $this->getDetailedReason($technique, $keywords, $knowledgeArea)
                ];
            }
        }
        
        usort($ranked, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($ranked, 0, 5);
    }

    /**
     * محاسبه حداکثر امتیاز ممکن
     */
    private function calculateMaxPossibleScore(array $keywords): float
    {
        $maxScore = 0;
        foreach ($keywords as $weight) {
            $maxScore += $weight * 1.5;
        }
        $maxScore += 30 + 15 + 5 + 20;
        return $maxScore;
    }

    /**
     * محاسبه امتیاز پیشرفته برای هر تکنیک
     */
    private function calculateAdvancedScore(array $technique, string $text, array $keywords, ?array $knowledgeArea): float
    {
        $score = 0;
        $name = mb_strtolower($technique['name'] ?? '');
        $description = mb_strtolower($technique['description'] ?? '');
        $purpose = mb_strtolower($technique['purpose'] ?? '');
        $advantages = mb_strtolower($technique['advantages'] ?? '');
        $category = $technique['category'] ?? '';
        
        $techText = $name . ' ' . $description . ' ' . $purpose . ' ' . $advantages;
        
        // 1. امتیاز بر اساس تطابق کلمات کلیدی
        foreach ($keywords as $keyword => $weight) {
            if (mb_strpos($techText, $keyword) !== false) {
                $score += $weight * 1.5;
            }
        }
        
        // 2. امتیاز بر اساس تطابق در نام تکنیک
        foreach ($keywords as $keyword => $weight) {
            if (mb_strpos($name, $keyword) !== false) {
                $score += $weight * 2;
            }
        }
        
        // 3. امتیاز بر اساس دسته‌بندی تکنیک
        $categoryScores = [
            'collaborative' => ['الهام‌گیری', 'همکاری', 'کارگاه', 'مصاحبه', 'گروه', 'روابط', 'ارتباط'],
            'research' => ['تحلیل', 'داده', 'مستندات', 'بررسی', 'پژوهش', 'ارزیابی'],
            'experimental' => ['نمونه', 'آزمایش', 'مدل', 'تعامل', 'اعتبارسنجی', 'پروتوتایپ'],
            'management' => ['مدیریت', 'برنامه‌ریزی', 'نظارت', 'ارزیابی', 'کنترل', 'بهبود'],
            'strategic' => ['استراتژی', 'تحلیل', 'چشم‌انداز', 'اهداف', 'SWOT', 'کسب‌وکار'],
            'modeling' => ['مدل', 'داده', 'فرآیند', 'ساختار', 'نمودار', 'طراحی']
        ];
        
        foreach ($categoryScores as $cat => $keywords_list) {
            if ($cat === $category) {
                foreach ($keywords_list as $kw) {
                    if (isset($keywords[$kw])) {
                        $score += $keywords[$kw] * 0.5;
                    }
                }
            }
        }
        
        // 4. امتیاز بر اساس حوزه دانشی
        if ($knowledgeArea) {
            $areaKeywords = $knowledgeArea['keywords'] ?? [];
            foreach ($areaKeywords as $kw) {
                if (isset($keywords[$kw])) {
                    $score += $keywords[$kw] * 0.3;
                }
            }
        }
        
        // 5. امتیاز برای کلمات کلیدی مرتبط با حوزه‌های مختلف
        $domainKeywords = ['مالی', 'حسابداری', 'تولید', 'خدمات', 'انبار', 'فروش', 'کارمند', 'آموزش'];
        foreach ($domainKeywords as $kw) {
            if (isset($keywords[$kw])) {
                $score += $keywords[$kw] * 1.5;
            }
        }
        
        // 6. امتیاز برای تطابق با کلمات کلیدی اختصاصی تکنیک
        $techKeywords = $this->techniqueKeywords[$technique['name']] ?? [];
        foreach ($techKeywords as $kw) {
            if (isset($keywords[$kw])) {
                $score += $keywords[$kw] * 1.2;
            }
        }
        
        return $score;
    }

    /**
     * تولید دلیل دقیق برای پیشنهاد تکنیک
     */
    private function getDetailedReason(array $technique, array $keywords, ?array $knowledgeArea): string
    {
        $reasons = [];
        $name = $technique['name'] ?? '';
        $category = $technique['category'] ?? '';
        
        $categoryNames = [
            'collaborative' => 'مناسب برای کارهای گروهی و همکاری',
            'research' => 'مناسب برای تحقیقات و تحلیل داده',
            'experimental' => 'مناسب برای آزمایش و نمونه‌سازی',
            'management' => 'مناسب برای مدیریت و برنامه‌ریزی',
            'strategic' => 'مناسب برای تحلیل استراتژیک',
            'modeling' => 'مناسب برای مدل‌سازی و طراحی'
        ];
        if (isset($categoryNames[$category])) {
            $reasons[] = $categoryNames[$category];
        }
        
        if ($knowledgeArea) {
            $reasons[] = 'مطابق با حوزه دانشی ' . ($knowledgeArea['persian'] ?? '');
        }
        
        // دلایل خاص برای تکنیک‌های مختلف
        $specificReasons = [
            'Financial Analysis' => 'برای تحلیل و بهبود وضعیت مالی و حسابرسی',
            'Root Cause Analysis' => 'برای شناسایی علل ریشه‌ای مشکلات مالی و فاکتورها',
            'Balanced Scorecard' => 'برای ارزیابی جامع عملکرد سازمان در ابعاد مختلف',
            'Lessons Learned' => 'برای مستندسازی تجربیات و بهبود فرآیندها',
            'Process Analysis' => 'برای تحلیل و بهبود فرآیندهای مالی و حسابداری',
            'Business Rules Analysis' => 'برای تحلیل قوانین و مقررات مالی و حسابرسی',
            'Risk Analysis and Management' => 'برای مدیریت ریسک‌های مالی و حسابرسی',
            'Interviews' => 'برای جمع‌آوری دقیق نیازمندی‌های مالی از ذی‌نفعان',
            'Workshops' => 'برای همکاری گروهی در حل مسائل مالی',
            'Document Analysis' => 'برای تحلیل مستندات مالی و حسابرسی',
            'Data Mining' => 'برای کشف الگوهای پنهان در داده‌های مالی',
            'Benchmarking' => 'برای مقایسه با بهترین شیوه‌های مالی در صنعت'
        ];
        if (isset($specificReasons[$name])) {
            $reasons[] = $specificReasons[$name];
        }
        
        if (empty($reasons)) {
            $reasons[] = 'تکنیک استاندارد با کاربرد عمومی در تحلیل کسب‌وکار';
        }
        
        return implode(' | ', array_slice($reasons, 0, 3));
    }
}