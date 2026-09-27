-- IT4IE research examples: curated, public starter workflows.
-- Apply once to the application database (MySQL 8+).

CREATE TABLE IF NOT EXISTS `workflow_templates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(150) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `summary` VARCHAR(500) NOT NULL,
    `tool_slug` VARCHAR(80) NOT NULL,
    `method_code` VARCHAR(80) DEFAULT NULL,
    `problem_type` VARCHAR(80) NOT NULL,
    `difficulty` ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    `estimated_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    `template_version` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `input_payload` JSON NOT NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `created_by` INT(11) DEFAULT NULL,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_workflow_templates_slug` (`slug`),
    KEY `idx_workflow_templates_listing` (`status`, `sort_order`, `published_at`),
    KEY `idx_workflow_templates_tool` (`tool_slug`, `status`),
    CONSTRAINT `fk_workflow_templates_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `workflow_templates`
    (`slug`, `title`, `summary`, `tool_slug`, `method_code`, `problem_type`, `difficulty`, `estimated_minutes`, `input_payload`, `status`, `sort_order`, `published_at`)
VALUES
(
    'descriptive-process-time',
    'تحلیل زمان انجام یک فرایند',
    'با داده‌های زمان انجام کار، شاخص‌های توصیفی و پراکندگی را بررسی کنید.',
    'statlab-analyzer', 'summary_stats', 'descriptive_statistics', 'beginner', 10,
    '{"problem_statement":"زمان انجام یک فرایند در چند مشاهده ثبت شده است. هدف، خلاصه‌کردن داده‌ها و دیدن میزان پراکندگی آن‌هاست.","preview":{"columns":["مشاهده","زمان (دقیقه)"],"rows":[[1,24],[2,26],[3,23],[4,25],[5,27],[6,24],[7,28],[8,22]]},"steps":["واحد اندازه‌گیری و متغیر را مشخص کنید.","میانگین، میانه و انحراف معیار را بررسی کنید.","پراکندگی و داده‌های دورافتاده را در کنار زمینهٔ فرایند تفسیر کنید."],"interpretation_note":"این دادهٔ کوچک آموزشی است؛ به‌تنهایی برای نتیجه‌گیری دربارهٔ کل فرایند کافی نیست."}',
    'published', 10, CURRENT_TIMESTAMP
),
(
    'one-sample-t-production-target',
    'آیا میانگین وزن محصول با مقدار هدف تفاوت دارد؟',
    'یک مثال آموزشی برای صورت‌بندی آزمون t تک‌نمونه‌ای در کنترل فرایند.',
    'statlab-analyzer', 'one_sample_t', 'one_sample_mean_test', 'beginner', 15,
    '{"problem_statement":"وزن هدف محصول 500 گرم است. می‌خواهیم بررسی کنیم آیا میانگین نمونهٔ فرایند با این مقدار تفاوت دارد یا نه.","preview":{"columns":["شماره نمونه","وزن (گرم)"],"rows":[[1,498],[2,503],[3,501],[4,497],[5,505],[6,499],[7,502],[8,496],[9,504],[10,500]]},"steps":["فرض صفر و فرض مقابل را پیش از دیدن نتیجه بنویسید.","سطح معناداری را تعیین کنید.","شرایط آزمون و کیفیت نمونه‌گیری را بررسی کنید.","p-value را با سطح معناداری و فاصلهٔ اطمینان تفسیر کنید."],"interpretation_note":"ده مشاهدهٔ نمایش‌داده‌شده صرفاً برای تمرین هستند و مبنای نتیجه‌گیری صنعتی نیستند."}',
    'published', 20, CURRENT_TIMESTAMP
),
(
    'supplier-selection-topsis',
    'انتخاب تأمین‌کننده با TOPSIS',
    'سه تأمین‌کننده را بر اساس هزینه، کیفیت و زمان تحویل مقایسه کنید.',
    'mcdm-analyzer', 'TOPSIS', 'supplier_selection', 'beginner', 20,
    '{"problem_statement":"یک تیم خرید می‌خواهد سه تأمین‌کننده را بر اساس هزینه، کیفیت و زمان تحویل رتبه‌بندی کند.","preview":{"columns":["گزینه","هزینه (هزار تومان)","کیفیت (از 10)","تحویل (روز)"],"rows":[["تأمین‌کننده الف",120,8,5],["تأمین‌کننده ب",100,7,8],["تأمین‌کننده پ",135,9,4]]},"steps":["جهت هر معیار را مشخص کنید؛ هزینه و زمان کمتر بهتر است.","اهمیت معیارها را تعیین و مجموع وزن‌ها را کنترل کنید.","ماتریس تصمیم را نرمال‌سازی و روش TOPSIS را اجرا کنید.","رتبه‌بندی را همراه با فرض‌های وزن‌دهی تفسیر کنید."],"interpretation_note":"وزن‌دهی بر رتبه‌بندی اثر می‌گذارد؛ برای تصمیم واقعی وزن‌ها را با ذی‌نفعان بررسی کنید."}',
    'published', 30, CURRENT_TIMESTAMP
),
(
    'warehouse-location-ahp',
    'اولویت‌بندی گزینه‌های مکان‌یابی با AHP',
    'تمرین ساخت سلسله‌مراتب تصمیم و مقایسهٔ زوجی معیارهای مکان‌یابی.',
    'mcdm-analyzer', 'AHP', 'location_selection', 'intermediate', 25,
    '{"problem_statement":"برای انتخاب محل انبار، سه گزینه را با معیارهای هزینه، دسترسی حمل‌ونقل و نزدیکی به بازار مقایسه می‌کنیم.","preview":{"columns":["گزینه","هزینه (از 10)","دسترسی حمل‌ونقل (از 10)","نزدیکی به بازار (از 10)"],"rows":[["مکان الف",7,9,8],["مکان ب",9,6,7],["مکان پ",6,8,9]]},"steps":["هدف، معیارها و گزینه‌ها را در سلسله‌مراتب تصمیم ثبت کنید.","اهمیت معیارها را به‌صورت زوجی مقایسه کنید.","سازگاری قضاوت‌ها را بررسی کنید.","گزینه‌ها را مقایسه و حساسیت رتبه‌بندی به وزن‌ها را ارزیابی کنید."],"interpretation_note":"امتیازهای گزینه‌ها دادهٔ تمرینی‌اند؛ در کاربرد واقعی مقایسهٔ زوجی را از داده و نظر خبرگان بسازید."}',
    'published', 40, CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);
