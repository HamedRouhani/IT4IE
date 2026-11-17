<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Setting;
use App\Models\SoftwareActivityLog;
use App\Models\WorkflowTemplate;
use PDO;
use Throwable;

class ResearchController extends Controller
{
    private const ALLOWED_TOOLS = [
        'statlab-analyzer', 'babok-analyzer', 'pmbok-analyzer', 'mcdm-analyzer', 'or-analyzer',
        'pdm-analyzer', 'inventory-analyzer', 'time-study-analyzer', 'hr-analyzer',
        'quality-analyzer', 'engineering-economy',
    ];

    private const RESEARCH_CATEGORIES = [
        'analytics' => ['title' => 'تحلیل داده و کیفیت', 'tools' => ['statlab-analyzer', 'quality-analyzer']],
        'decision' => ['title' => 'تصمیم‌گیری و بهینه‌سازی', 'tools' => ['mcdm-analyzer', 'or-analyzer', 'engineering-economy']],
        'process' => ['title' => 'فرایند و مدیریت پروژه', 'tools' => ['babok-analyzer', 'pmbok-analyzer']],
        'operations' => ['title' => 'عملیات و بهره‌وری', 'tools' => ['inventory-analyzer', 'pdm-analyzer', 'time-study-analyzer', 'hr-analyzer']],
    ];

    private const SOFTWARE_CATALOG = [
        ['slug'=>'statlab-analyzer', 'name'=>'StatLab Analyzer', 'title'=>'آمار و تحلیل داده', 'description'=>'آمار توصیفی و استنباطی، بررسی رابطهٔ متغیرها و تحلیل داده‌های پژوهشی.', 'research'=>'آماده‌سازی داده، آزمون فرض، همبستگی و رگرسیون', 'icon'=>'fa-chart-column'],
        ['slug'=>'babok-analyzer', 'name'=>'BABOK Analyzer', 'title'=>'تحلیل کسب‌وکار', 'description'=>'شناخت مسئله، ذی‌نفعان، نیازمندی‌ها و وضعیت فرایند پیش از طراحی راهکار.', 'research'=>'تحلیل نیازمندی، ذی‌نفعان و بهبود فرایند', 'icon'=>'fa-magnifying-glass-chart'],
        ['slug'=>'pmbok-analyzer', 'name'=>'PMBOK Analyzer', 'title'=>'مدیریت پروژه', 'description'=>'تعریف و برنامه‌ریزی پروژه، تحویل‌دادنی‌ها، ریسک‌ها و پیگیری اجرا.', 'research'=>'برنامه‌ریزی، زمان‌بندی، ریسک و کنترل پروژه', 'icon'=>'fa-diagram-project'],
        ['slug'=>'mcdm-analyzer', 'name'=>'MCDM Analyzer', 'title'=>'تصمیم‌گیری چندمعیاره', 'description'=>'وزن‌دهی معیارها و مقایسهٔ گزینه‌ها با روش‌های تصمیم‌گیری چندمعیاره.', 'research'=>'انتخاب تأمین‌کننده، مکان، فناوری یا راهکار', 'icon'=>'fa-scale-balanced'],
        ['slug'=>'or-analyzer', 'name'=>'OR Analyzer', 'title'=>'تحقیق در عملیات', 'description'=>'مدل‌سازی و حل مسائل تخصیص منابع، بهینه‌سازی، صف و شبیه‌سازی.', 'research'=>'برنامه‌ریزی خطی، حمل‌ونقل، صف و شبیه‌سازی', 'icon'=>'fa-diagram-project'],
        ['slug'=>'pdm-analyzer', 'name'=>'PdM Analyzer', 'title'=>'نگهداری و تعمیرات', 'description'=>'مدیریت دارایی، خرابی و دستورکار و تحلیل شاخص‌های قابلیت اطمینان.', 'research'=>'MTBF، MTTR، FMEA و راهبرد نگهداری', 'icon'=>'fa-screwdriver-wrench'],
        ['slug'=>'inventory-analyzer', 'name'=>'Inventory Analyzer', 'title'=>'کنترل موجودی', 'description'=>'ثبت گردش اقلام و تحلیل طبقه‌بندی، نقطهٔ سفارش و سیاست تأمین موجودی.', 'research'=>'ABC، EOQ، موجودی اطمینان و سفارش خرید', 'icon'=>'fa-boxes-stacked'],
        ['slug'=>'time-study-analyzer', 'name'=>'Time Study Analyzer', 'title'=>'مطالعهٔ کار و زمان‌سنجی', 'description'=>'ثبت چرخه‌ها، بررسی کفایت نمونه و محاسبهٔ زمان نرمال و استاندارد کار.', 'research'=>'زمان‌سنجی، allowance و ظرفیت نیروی انسانی', 'icon'=>'fa-stopwatch'],
        ['slug'=>'hr-analyzer', 'name'=>'HR Analyzer', 'title'=>'مدیریت منابع انسانی', 'description'=>'پشتیبانی از فرایندهای جذب، آموزش، ارزیابی و توسعهٔ منابع انسانی.', 'research'=>'نیاز آموزشی، جذب و ارزیابی عملکرد', 'icon'=>'fa-users'],
        ['slug'=>'quality-analyzer', 'name'=>'Quality Analyzer', 'title'=>'تحلیل و کنترل کیفیت', 'description'=>'پایش پایداری فرایند، قابلیت فرایند، سیستم اندازه‌گیری و اقدام اصلاحی.', 'research'=>'SPC، قابلیت، MSA، پارتو و CAPA', 'icon'=>'fa-circle-check'],
        ['slug'=>'engineering-economy', 'name'=>'Engineering Economy Analyzer', 'title'=>'اقتصاد مهندسی', 'description'=>'مقایسهٔ اقتصادی گزینه‌ها بر پایهٔ جریان نقدی و ارزش زمانی پول.', 'research'=>'NPV، IRR، هزینهٔ چرخهٔ عمر و جایگزینی دارایی', 'icon'=>'fa-coins'],
    ];

    /** Executable, self-contained examples used when the research library has no published DB template for a module. */
    private function builtInTemplates(): array
    {
        $examples = [
            ['statlab-analyzer','statlab-diameter-summary','تحلیل پراکندگی قطر قطعه','آیا قطر خروجی فرایند در محدوده هدف قرار دارد؟','StatLab','summary_stats',['preview'=>['columns'=>['نمونه','قطر (mm)'],'rows'=>[['۱',49.8],['۲',50.1],['۳',50.0],['۴',49.9],['۵',50.2],['۶',50.1],['۷',49.7],['۸',50.3],['۹',50.0],['۱۰',49.9]]],'problem_statement'=>'خلاصه آماری اندازه‌گیری‌های قطر قطعه را تهیه و پراکندگی را بررسی کنید.','steps'=>['داده‌ها را بازبینی کنید','میانگین و انحراف معیار را بررسی کنید','نتیجه را با حدود فنی مقایسه کنید']]],
            ['babok-analyzer','babok-order-delay','کاهش تأخیر در پردازش سفارش','زمان پردازش سفارش‌ها افزایش یافته است؛ ذی‌نفعان و نیازمندی‌های بهبود را مشخص کنید.','تحلیل نیازمندی',['problem_statement'=>'زمان پردازش سفارش‌ها افزایش یافته است. مسئله، ذی‌نفعان، نیازمندی‌ها و معیارهای موفقیت بهبود را مشخص کنید.','steps'=>['مسئله و دامنه را تعریف کنید','ذی‌نفعان را شناسایی کنید','نیازمندی‌ها را ثبت و اولویت‌بندی کنید']]],
            ['pmbok-analyzer','pmbok-line-upgrade','برنامه‌ریزی ارتقای خط تولید','پروژهٔ ارتقا با محدودیت زمان، بودجه و ریسک تأمین‌کننده روبه‌رو است.','برنامه‌ریزی پروژه',['problem_statement'=>'برای ارتقای یک خط تولید، دامنه، ذی‌نفعان، برنامهٔ اجرایی و ریسک‌های اصلی را تعریف کنید.','steps'=>['دامنه و تحویل‌دادنی‌ها را تعریف کنید','فعالیت‌ها و ریسک‌ها را ثبت کنید','پیشرفت را در برابر برنامه پایش کنید']]],
            ['mcdm-analyzer','mcdm-supplier-selection','انتخاب تأمین‌کننده','سه تأمین‌کننده را بر اساس هزینه، کیفیت و زمان تحویل مقایسه کنید.','TOPSIS',['preview'=>['columns'=>['گزینه','هزینه','کیفیت','زمان تحویل'],'rows'=>[['تأمین‌کننده الف',120,85,8],['تأمین‌کننده ب',100,78,12],['تأمین‌کننده ج',135,92,6]]],'problem_statement'=>'تأمین‌کننده را بر اساس هزینه (کمتر بهتر)، کیفیت و زمان تحویل انتخاب کنید.','steps'=>['جهت معیارها را بررسی کنید','اهمیت معیارها را تعیین کنید','رتبه‌بندی را با نظر خبرگان بازبینی کنید']]],
            ['or-analyzer','or-service-queue','تحلیل صف خدمت‌رسانی','ورود متوسط ۸ مشتری در ساعت و خدمت‌دهی ۱۰ مشتری در ساعت است.','MM1',['parameters'=>['model_code'=>'MM1','lambda'=>8,'mu'=>10,'servers'=>1],'problem_statement'=>'عملکرد صف M/M/1 را با نرخ ورود ۸ و نرخ خدمت ۱۰ مشتری در ساعت تحلیل کنید.','steps'=>['پایداری سیستم را بررسی کنید','بهره‌برداری و زمان انتظار را بسنجید','فرض‌های نرخ‌های ثابت را بازبینی کنید']]],
            ['pdm-analyzer','pdm-pump-maintenance','بازرسی پمپ فرایندی','برای پمپ حیاتی یک دستورکار بازرسی پیشگیرانه ایجاد کنید.','نگهداری پیشگیرانه',['asset_name'=>'پمپ فرایندی نمونه','work_order_title'=>'بازرسی پیشگیرانه پمپ','problem_statement'=>'پمپ فرایندی حیاتی نیازمند بازرسی ارتعاش، نشتی و وضعیت یاتاقان است.'] ],
            ['inventory-analyzer','inventory-reorder-point','نقطه سفارش قلم پرمصرف','سیاست سفارش را با تقاضا، زمان تأمین و هزینه‌ها تنظیم کنید.','EOQ و نقطه سفارش',['problem_statement'=>'قلم پرمصرف را با تقاضای سالانه، زمان تأمین و هزینه سفارش/نگهداری پایش کنید.','item'=>['sku'=>'SAMPLE-INV-001','name'=>'بلبرینگ نمونه','category'=>'قطعات یدکی','unit'=>'عدد','unit_cost'=>500000,'on_hand'=>24,'annual_demand'=>240,'daily_demand_std'=>2,'lead_time_days'=>5,'order_cost'=>150000,'holding_rate'=>0.25,'service_level'=>'95']]],
            ['time-study-analyzer','time-study-assembly-cycle','زمان‌سنجی چرخه مونتاژ','زمان عنصر دستی و ماشین را مشاهده و زمان استاندارد آموزشی را محاسبه کنید.','زمان‌سنجی مستقیم',['problem_statement'=>'چرخهٔ مونتاژ شامل جاگذاری دستی و کار خودکار ماشین است. زمان‌ها را ثبت و زمان استاندارد را با allowance مستند برآورد کنید.','time_study'=>true]],
            ['hr-analyzer','hr-training-needs','آموزش کاهش خطای اپراتور','یک دورهٔ آموزشی برای کاهش خطاهای ثبت‌شده در عملیات تعریف کنید.','دوره آموزشی',['problem_statement'=>'برای کاهش خطاهای ثبت‌شده، هدف، مخاطب و محتوای یک دورهٔ آموزشی عملیاتی را تعریف کنید.'] ],
            ['quality-analyzer','quality-diameter-spc','پایش قطر قطعه','اندازه‌گیری‌های فرایند را با نمودار کنترل بررسی کنید.','I-MR',['problem_statement'=>'پایداری قطر قطعه را با دادهٔ آموزشی بررسی کنید.','product_name'=>'قطعهٔ ماشین‌کاری‌شده','process_name'=>'تراشکاری','ctq'=>'قطر خارجی','unit'=>'mm','values'=>[49.8,50.1,50.0,49.9,50.2,50.1,49.7,50.3,50.0,49.9],'chart_type'=>'i_mr','spec_lsl'=>49,'spec_usl'=>51,'spec_target'=>50]],
            ['engineering-economy','ee-machine-replacement','ارزیابی جایگزینی ماشین','هزینهٔ اولیهٔ ماشین جدید را با کاهش هزینهٔ بهره‌برداری در پنج سال مقایسه کنید.','NPV و تحلیل هزینه',['problem_statement'=>'جایگزینی ماشین را با نرخ حداقل بازده ۱۲٪ و افق پنج‌ساله ارزیابی کنید.','economy'=>true]],
        ];
        $result = [];
        foreach ($examples as [$tool,$slug,$title,$summary,$method,$payload]) {
            $result[] = ['id'=>null,'slug'=>$slug,'title'=>$title,'summary'=>$summary,'tool_slug'=>$tool,'method_code'=>$method,'difficulty'=>'beginner','estimated_minutes'=>15,'input_payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),'is_builtin'=>true];
        }
        return $result;
    }

    private function findResearchTemplate(string $slug): ?array
    {
        $template = (new WorkflowTemplate())->findPublishedBySlug($slug);
        if ($template) return $template;
        foreach ($this->builtInTemplates() as $example) if ($example['slug'] === $slug) return $example;
        return null;
    }

    public function index()
    {
        $toolSlug = $_GET['tool'] ?? null;
        if (!in_array($toolSlug, self::ALLOWED_TOOLS, true)) $toolSlug = null;
        $category = $_GET['category'] ?? null;
        if (!is_string($category) || !array_key_exists($category, self::RESEARCH_CATEGORIES)) $category = null;

        $this->logResearchActivity('research_list_view', null, [
            'tool_slug' => $toolSlug,
        ]);

        $templates = (new WorkflowTemplate())->getPublished($category ? null : $toolSlug);
        if ($category) {
            $categoryTools = self::RESEARCH_CATEGORIES[$category]['tools'];
            $templates = array_values(array_filter($templates, static fn($template) => in_array($template['tool_slug'], $categoryTools, true)));
        }
        foreach ($this->builtInTemplates() as $example) {
            $matchesCategory = !$category || in_array($example['tool_slug'], self::RESEARCH_CATEGORIES[$category]['tools'], true);
            if ($matchesCategory && ($toolSlug === null || $toolSlug === $example['tool_slug'])) $templates[] = $example;
        }

        $this->render('research/index', [
            'title' => 'نمونه‌های پژوهشی مهندسی صنایع | IT4IE',
            'settings' => (new Setting())->getAll(),
            'templates' => $templates,
            'activeTool' => $toolSlug,
            'tools' => self::SOFTWARE_CATALOG,
            'toolLabels' => array_column(self::SOFTWARE_CATALOG, 'title', 'slug'),
            'activeToolInfo' => $toolSlug ? $this->toolInfo($toolSlug) : null,
            'researchCategories' => self::RESEARCH_CATEGORIES,
            'activeCategory' => $category,
            'hideSidebar' => true,
            'hideFooter' => true,
        ]);
    }

    public function show($slug)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->start($slug);
            return;
        }

        $template = $this->findResearchTemplate((string) $slug);
        if (!$template) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'نمونه پژوهشی پیدا نشد | IT4IE',
                'settings' => (new Setting())->getAll(),
                'hideSidebar' => true,
                'hideFooter' => true,
            ]);
            return;
        }

        $payload = json_decode($template['input_payload'] ?? '', true);
        $template['payload'] = is_array($payload) ? $payload : [];
        $this->logResearchActivity('research_template_view', isset($template['id']) ? (int) $template['id'] : null, [
            'template_slug' => $template['slug'],
            'tool_slug' => $template['tool_slug'],
        ]);
        $toolNames = [
            'statlab-analyzer' => 'StatLab · تحلیل آماری',
            'mcdm-analyzer' => 'MCDM · تصمیم‌گیری چندمعیاره',
            'babok-analyzer' => 'BABOK · تحلیل کسب‌وکار',
            'pmbok-analyzer' => 'PMBOK · مدیریت پروژه',
            'or-analyzer' => 'تحقیق در عملیات',
            'hr-analyzer' => 'مدیریت منابع انسانی',
            'pdm-analyzer' => 'نگهداری و تعمیرات',
            'quality-analyzer' => 'مدیریت کیفیت',
            'inventory-analyzer' => 'Inventory · کنترل موجودی',
            'time-study-analyzer' => 'Time Study · زمان‌سنجی',
            'engineering-economy' => 'اقتصاد مهندسی',
        ];
        $toolInfo = $this->toolInfo($template['tool_slug']);

        $this->render('research/show', [
            'title' => htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') . ' | نمونه پژوهشی IT4IE',
            'settings' => (new Setting())->getAll(),
            'template' => $template,
            'toolName' => $toolNames[$template['tool_slug']] ?? 'ابزار پژوهشی',
            'moduleUrl' => $toolInfo['url'],
            'isDirectOpen' => false,
            'isLoggedIn' => isset($_SESSION['user_id']),
            'csrfField' => $this->csrfField(),
            'hideSidebar' => true,
            'hideFooter' => true,
        ]);
    }

    public function start($slug)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            return;
        }
        $this->verifyCsrf('درخواست معتبر نیست. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.');

        $template = $this->findResearchTemplate((string) $slug);
        if (!$template || !in_array($template['tool_slug'], self::ALLOWED_TOOLS, true)) {
            http_response_code(404);
            $this->redirect('/research');
        }

        $templateId = isset($template['id']) ? (int) $template['id'] : null;
        $this->logResearchActivity('research_project_requested', $templateId, [
            'template_slug' => $template['slug'],
            'tool_slug' => $template['tool_slug'],
        ]);
        if (!isset($_SESSION['user_id'])) {
            $this->logResearchActivity('research_login_required', $templateId, [
                'template_slug' => $template['slug'],
                'tool_slug' => $template['tool_slug'],
            ]);
            $_SESSION['redirect_after_login'] = '/research/' . rawurlencode($slug);
            $_SESSION['auth_message'] = 'برای ساخت پروژه از نمونه، ابتدا وارد حساب خود شوید.';
            $this->redirect('/login');
        }

        $previousHrSystem = $_SESSION['hr_active_system'] ?? null;
        $previousPdmSystem = $_SESSION['pdm_active_system'] ?? null;
        try {
            $payload = json_decode($template['input_payload'] ?? '', true, 512, JSON_THROW_ON_ERROR);
            $db = Database::getInstance();
            $db->beginTransaction();
            $userId = (int) $_SESSION['user_id'];
            $projectName = mb_substr($template['title'] . ' · ' . date('Y-m-d H:i') . ' · ' . strtoupper(bin2hex(random_bytes(2))), 0, 95, 'UTF-8');
            switch ($template['tool_slug']) {
                case 'statlab-analyzer':
                    $projectId = $this->createStatlabProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/statlab-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'mcdm-analyzer':
                    $projectId = $this->createMcdmProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/mcdm-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'babok-analyzer':
                    $projectId = $this->createBabokProject($db, $userId, $projectName, $payload);
                    $target = '/software/babok-analyzer/?route=projects_view&id=' . $projectId;
                    break;
                case 'pmbok-analyzer':
                    $projectId = $this->createPmbokProject($db, $userId, $projectName, $payload);
                    $target = '/software/pmbok-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'or-analyzer':
                    $projectId = $this->createQueueingProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/or-analyzer/?controller=queueing&action=show&id=' . $projectId;
                    break;
                case 'hr-analyzer':
                    $projectId = $this->createHrTraining($db, $userId, $projectName, $payload);
                    $target = '/software/hr-analyzer/?controller=training&action=show&id=' . $projectId;
                    break;
                case 'pdm-analyzer':
                    $projectId = $this->createPdmWorkOrder($db, $userId, $projectName, $payload);
                    $target = '/software/pdm-analyzer/?controller=workorder&action=show&id=' . $projectId;
                    break;
                case 'quality-analyzer':
                    $projectId = $this->createQualityProject($db, $userId, $projectName, $payload);
                    $target = '/software/quality-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'inventory-analyzer':
                    $projectId = $this->createInventorySample($db, $userId, $payload);
                    $target = '/software/inventory-analyzer/?controller=item';
                    break;
                case 'time-study-analyzer':
                    $projectId = $this->createTimeStudySample($db, $userId, $projectName, $payload);
                    $target = '/software/time-study-analyzer/?controller=study&action=show&id=' . $projectId;
                    break;
                case 'engineering-economy':
                    $projectId = $this->createEconomySample($db, $userId, $projectName, $payload);
                    $target = '/software/engineering-economy/?controller=project&action=show&id=' . $projectId;
                    break;
                default:
                    throw new \RuntimeException('Unsupported research module.');
            }

            $db->commit();
            $this->logResearchActivity('research_project_created', $templateId, [
                'template_slug' => $template['slug'],
                'tool_slug' => $template['tool_slug'],
                'project_id' => (int) $projectId,
            ]);
            $this->redirect($target);
        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
            if ($previousHrSystem === null) unset($_SESSION['hr_active_system']); else $_SESSION['hr_active_system'] = $previousHrSystem;
            if ($previousPdmSystem === null) unset($_SESSION['pdm_active_system']); else $_SESSION['pdm_active_system'] = $previousPdmSystem;
            error_log('ResearchController::start: ' . $e->getMessage());
            $this->logResearchActivity('research_project_failed', $templateId, [
                'template_slug' => $template['slug'],
                'tool_slug' => $template['tool_slug'],
            ]);
            $_SESSION['error'] = 'ساخت پروژه از نمونه انجام نشد. لطفاً دوباره تلاش کنید.';
            $this->redirect('/research/' . rawurlencode($slug));
        }
    }

    private function toolInfo(string $slug): array
    {
        foreach (self::SOFTWARE_CATALOG as $tool) {
            if ($tool['slug'] === $slug) {
                $tool['url'] = '/software/' . $slug . '/';
                return $tool;
            }
        }
        return ['url' => '/software'];
    }

    private function logResearchActivity(string $action, ?int $templateId = null, array $details = []): void
    {
        try {
            (new SoftwareActivityLog())->log(
                'research',
                $action,
                $templateId === null ? 'research' : 'research_template',
                $templateId,
                null,
                $details
            );
        } catch (Throwable $e) {
            // Analytics must never interrupt browsing or project creation.
            error_log('ResearchController activity log: ' . $e->getMessage());
        }
    }

    private function createStatlabProject(PDO $db, int $userId, string $name, array $template, array $payload): int
    {
        $preview = $payload['preview'] ?? [];
        $columns = $preview['columns'] ?? [];
        $rows = $preview['rows'] ?? [];
        if (!is_array($columns) || count($columns) < 2 || !is_array($rows) || !$rows) {
            throw new \RuntimeException('StatLab sample has no usable observations.');
        }
        $values = [];
        foreach ($rows as $row) {
            if (isset($row[1]) && is_numeric($row[1])) $values[] = (float) $row[1];
        }
        if (!$values) throw new \RuntimeException('StatLab sample has no numeric values.');

        $stmt = $db->prepare("INSERT INTO stat_projects (user_id, name, description, category_code, analysis_type_code, objective, significance_level, status) VALUES (?, ?, ?, 'descriptive', ?, 'descriptive', 0.05, 'draft')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? '', $template['method_code'] ?? 'summary_stats']);
        $projectId = (int) $db->lastInsertId();
        foreach (array_slice($columns, 1, null, true) as $columnIndex => $columnName) {
            $series = [];
            foreach ($rows as $row) {
                if (isset($row[$columnIndex]) && is_numeric($row[$columnIndex])) $series[] = (float) $row[$columnIndex];
            }
            if (!$series) continue;
            $dataset = $db->prepare("INSERT INTO stat_datasets (project_id, user_id, name, source, data_json, variables, sample_size, variables_count, has_missing, missing_count) VALUES (?, ?, ?, 'sample', ?, ?, ?, 1, 0, 0)");
            $dataset->execute([$projectId, $userId, 'داده نمونه · ' . (string) $columnName, json_encode($series, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode([(string) $columnName], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), count($series)]);
        }
        return $projectId;
    }

    private function createMcdmProject(PDO $db, int $userId, string $name, array $template, array $payload): int
    {
        $preview = $payload['preview'] ?? [];
        $columns = $preview['columns'] ?? [];
        $rows = $preview['rows'] ?? [];
        if (!is_array($columns) || count($columns) < 2 || !is_array($rows) || !$rows) {
            throw new \RuntimeException('MCDM sample has no usable decision matrix.');
        }
        $method = $db->prepare('SELECT id FROM mcdm_methods WHERE code = ? LIMIT 1');
        $method->execute([$template['method_code'] ?? 'TOPSIS']);
        $methodId = $method->fetchColumn();
        $stmt = $db->prepare("INSERT INTO mcdm_projects (user_id, name, method_id, phase, industry, alternatives_count, criteria_count) VALUES (?, ?, ?, 'evaluation', 'education', ?, ?)");
        $stmt->execute([$userId, $name, $methodId ?: null, count($rows), count($columns) - 1]);
        $projectId = (int) $db->lastInsertId();

        $criteriaIds = [];
        $criterionStmt = $db->prepare('INSERT INTO mcdm_project_criteria (project_id, name, type, weight, is_active, sort_order) VALUES (?, ?, ?, ?, 1, ?)');
        foreach (array_slice($columns, 1) as $index => $label) {
            $isCost = preg_match('/هزینه|زمان|ریسک|cost|time/i', (string) $label) === 1;
            $criterionStmt->execute([$projectId, (string) $label, $isCost ? 'cost' : 'benefit', 1 / (count($columns) - 1), $index + 1]);
            $criteriaIds[] = (int) $db->lastInsertId();
        }
        $alternativeIds = [];
        $alternativeStmt = $db->prepare('INSERT INTO mcdm_project_alternatives (project_id, name, description, is_active, sort_order) VALUES (?, ?, ?, 1, ?)');
        foreach ($rows as $index => $row) {
            if (!is_array($row) || count($row) < count($columns)) continue;
            $alternativeStmt->execute([$projectId, (string) $row[0], 'گزینه نمونه از کتابخانه پژوهشی IT4IE', $index + 1]);
            $alternativeIds[] = (int) $db->lastInsertId();
            $currentAltId = end($alternativeIds);
            foreach ($criteriaIds as $criterionIndex => $criterionId) {
                if (!is_numeric($row[$criterionIndex + 1] ?? null)) continue;
                $db->prepare('INSERT INTO mcdm_project_evaluations (project_id, criterion_id, alternative_id, value) VALUES (?, ?, ?, ?)')->execute([$projectId, $criterionId, $currentAltId, (float) $row[$criterionIndex + 1]]);
            }
        }
        if (!$alternativeIds) throw new \RuntimeException('MCDM sample has no alternatives.');
        return $projectId;
    }
    private function createBabokProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $stmt = $db->prepare("INSERT INTO babok_projects (user_id, name, description, phase, stakeholder_count, methodology, industry) VALUES (?, ?, ?, 'analysis', 3, 'hybrid', 'education')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? 'پروژهٔ تمرینی BABOK؛ داده‌ها آموزشی هستند.']);
        $projectId = (int) $db->lastInsertId();
        $tasks = $db->query('SELECT id FROM babok_tasks ORDER BY id ASC LIMIT 5')->fetchAll(PDO::FETCH_COLUMN);
        if ($tasks) {
            $link = $db->prepare("INSERT INTO babok_project_tasks (user_id, project_id, task_id, status, notes) VALUES (?, ?, ?, 'not_started', ?)");
            foreach ($tasks as $taskId) $link->execute([$userId, $projectId, (int) $taskId, 'وظیفهٔ پیشنهادی از نمونهٔ آموزشی IT4IE']);
        }
        return $projectId;
    }

    private function createPmbokProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $stmt = $db->prepare("INSERT INTO pmbok_projects (user_id, name, description, phase, stakeholder_count, budget, currency, methodology, industry) VALUES (?, ?, ?, 'planning', 4, 250000000, 'IRR', 'hybrid', 'education')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? 'پروژهٔ تمرینی PMBOK؛ داده‌ها آموزشی هستند.']);
        $projectId = (int) $db->lastInsertId();
        $tasks = $db->query('SELECT id FROM pmbok_tasks ORDER BY id ASC LIMIT 5')->fetchAll(PDO::FETCH_COLUMN);
        if ($tasks) {
            $link = $db->prepare("INSERT INTO pmbok_project_tasks (user_id, project_id, task_id, status, planned_hours, percent_complete, notes) VALUES (?, ?, ?, 'not_started', 4, 0, ?)");
            foreach ($tasks as $taskId) $link->execute([$userId, $projectId, (int) $taskId, 'وظیفهٔ پیشنهادی از نمونهٔ آموزشی IT4IE']);
        }
        $risk = $db->prepare("INSERT INTO pmbok_risks (user_id, project_id, title, description, probability, impact, risk_score, response_strategy, response_plan, status) VALUES (?, ?, ?, ?, 'medium', 'high', 12, 'mitigate', ?, 'analyzed')");
        $risk->execute([$userId, $projectId, 'ریسک تأخیر تأمین', 'تأخیر در تأمین اقلام کلیدی می‌تواند برنامهٔ زمان‌بندی را جابه‌جا کند.', 'تأمین‌کنندهٔ جایگزین شناسایی و اقلام بحرانی زودتر سفارش‌گذاری شوند.']);
        return $projectId;
    }

    private function createQueueingProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $params = $payload['parameters'] ?? [];
        $modelCode = $params['model_code'] ?? 'MM1';
        $lambda = (float) ($params['lambda'] ?? 8);
        $mu = (float) ($params['mu'] ?? 10);
        $servers = max(1, (int) ($params['servers'] ?? 1));
        $capacity = isset($params['capacity']) ? (int) $params['capacity'] : null;
        $serviceStd = isset($params['service_std']) ? (float) $params['service_std'] : null;
        require_once APP_PATH . '/software/or/app/Helpers/QueueingEngine.php';
        $result = \App\Software\Or\Helpers\QueueingEngine::solve($modelCode, ['lambda' => $lambda, 'mu' => $mu, 'servers' => $servers, 'capacity' => $capacity, 'service_std' => $serviceStd]);
        if (($result['status'] ?? '') !== 'ok') throw new \RuntimeException('Queueing example parameters did not produce a stable result.');
        $stmt = $db->prepare('INSERT INTO or_queueing_projects (user_id, name, description, model_code, `lambda`, `mu`, servers, capacity, service_std, status, result_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'solved\', ?)');
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? null, $modelCode, $lambda, $mu, $servers, $capacity, $serviceStd, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        return (int) $db->lastInsertId();
    }

    private function createHrTraining(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM hr_systems WHERE owner_user_id = ? ORDER BY (status = 'active') DESC, id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO hr_systems (owner_user_id, company_name, industry, company_size, description, status) VALUES (?, 'سازمان آموزشی نمونه IT4IE', 'education', 'small', 'سیستم ساخته‌شده برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $_SESSION['hr_active_system'] = $systemId;
        $code = 'IT4IE-SAMPLE-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare("INSERT INTO hr_trainings (system_id, code, title, training_type, category, provider, description, objectives, content, target_audience, duration_hours, delivery_mode, priority, start_date, end_date, max_participants, current_participants, has_certificate, status) VALUES (?, ?, ?, 'workshop', 'بهبود فرایند', 'IT4IE · دادهٔ آموزشی', ?, ?, ?, 'دانشجویان و تحلیلگران مهندسی صنایع', 8, 'online_self_paced', 'normal', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 25, 0, 0, 'planned')");
        $problem = $payload['problem_statement'] ?? 'تمرین آموزشی برای آشنایی با تحلیل و بهبود فرایند.';
        $stmt->execute([$systemId, $code, $name, $problem, 'آشنایی با مسئله، شاخص‌گذاری و طراحی اقدام بهبود', 'تعریف مسئله؛ ترسیم فرایند؛ انتخاب شاخص؛ ارزیابی راهکار']);
        return (int) $db->lastInsertId();
    }

    private function createPdmWorkOrder(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM pm_systems WHERE owner_user_id = ? ORDER BY (status = 'active') DESC, id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO pm_systems (owner_user_id, company_name, industry, description, status) VALUES (?, 'کارخانهٔ آموزشی نمونه IT4IE', 'manufacturing', 'سیستم ساخته‌شده برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $_SESSION['pdm_active_system'] = $systemId;
        $token = strtoupper(bin2hex(random_bytes(4)));
        $assetCode = 'IT4IE-SAMPLE-' . $token;
        $assetStmt = $db->prepare("INSERT INTO pm_assets (system_id, asset_code, name, manufacturer, model, criticality, status, description) VALUES (?, ?, ?, 'سازندهٔ فرضی', 'مدل آموزشی', 'high', 'active', ?)");
        $assetStmt->execute([$systemId, $assetCode, $payload['asset_name'] ?? 'پمپ فرایندی آموزشی', $payload['problem_statement'] ?? 'دارایی ساختگی برای تمرین نگهداری و تعمیرات.']);
        $assetId = (int) $db->lastInsertId();
        $orderStmt = $db->prepare("INSERT INTO pm_work_orders (system_id, wo_number, asset_id, title, description, priority, status, planned_date) VALUES (?, ?, ?, ?, ?, 'high', 'open', DATE_ADD(CURDATE(), INTERVAL 1 DAY))");
        $orderStmt->execute([$systemId, 'IT4IE-WO-' . $token, $assetId, $payload['work_order_title'] ?? $name, $payload['problem_statement'] ?? 'بازرسی پیشگیرانهٔ تجهیز نمونه.']);
        return (int) $db->lastInsertId();
    }

    private function createQualityProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM qc_systems WHERE user_id = ? AND status = 'active' ORDER BY id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO qc_systems (user_id, company_name, industry, company_size, description, status) VALUES (?, 'کارخانهٔ آموزشی نمونه IT4IE', 'manufacturing', 'small', 'سیستم ساختگی برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $stmt = $db->prepare("INSERT INTO qc_projects (system_id, user_id, name, description, product_name, process_name, ctq, unit, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$systemId, $userId, $name, $payload['problem_statement'] ?? null, $payload['product_name'] ?? 'محصول نمونهٔ آموزشی', $payload['process_name'] ?? 'فرایند نمونه', $payload['ctq'] ?? 'ویژگی کیفی بحرانی', $payload['unit'] ?? 'mm']);
        $projectId = (int) $db->lastInsertId();
        $values = $payload['values'] ?? [49.8, 50.1, 50.0, 49.9, 50.2, 50.1, 49.7, 50.3, 50.0, 49.9];
        $chartType = $payload['chart_type'] ?? 'i_mr';
        $subgroupSize = $chartType === 'xbar_r' ? 4 : null;
        $dataset = $db->prepare('INSERT INTO qc_datasets (system_id, project_id, name, chart_type, subgroup_size, spec_lsl, spec_usl, spec_target, data_json, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $dataset->execute([$systemId, $projectId, 'دادهٔ آموزشی · ' . strtoupper($chartType), $chartType, $subgroupSize, $payload['spec_lsl'] ?? 49, $payload['spec_usl'] ?? 51, $payload['spec_target'] ?? 50, json_encode($values, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'مقادیر ساختگی آموزشی؛ بدون اطلاعات فرایند واقعی.']);
        $datasetId = (int) $db->lastInsertId();
        $measurement = $db->prepare('INSERT INTO qc_measurements (system_id, dataset_id, subgroup_no, sample_no, value, is_defective, defect_count) VALUES (?, ?, ?, ?, ?, 0, 0)');
        if ($chartType === 'xbar_r') {
            foreach ($values as $groupIndex => $group) {
                if (!is_array($group)) throw new \RuntimeException('X-bar/R example values must be grouped by subgroup.');
                foreach ($group as $sampleIndex => $value) $measurement->execute([$systemId, $datasetId, $groupIndex + 1, $sampleIndex + 1, (float) $value]);
            }
        } else {
            foreach ($values as $index => $value) $measurement->execute([$systemId, $datasetId, $index + 1, 1, (float) $value]);
        }
        return $projectId;
    }

    private function createInventorySample(PDO $db, int $userId, array $payload): int
    {
        $item = $payload['item'] ?? [];
        $db->prepare("INSERT INTO inv_workspaces (user_id, name) VALUES (?, 'انبار نمونه پژوهشی') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)")->execute([$userId]);
        $workspaceQuery = $db->prepare('SELECT id FROM inv_workspaces WHERE user_id=? LIMIT 1');
        $workspaceQuery->execute([$userId]);
        $workspaceId = (int) $workspaceQuery->fetchColumn();
        if (!$workspaceId) throw new \RuntimeException('فضای کاری موجودی ایجاد نشد.');

        $sku = (string) ($item['sku'] ?? 'SAMPLE-INV-001');
        $sku .= '-' . strtoupper(bin2hex(random_bytes(2)));
        $opening = max(0, (float) ($item['on_hand'] ?? 24));
        $stmt = $db->prepare('INSERT INTO inv_items (workspace_id,sku,name,category,unit,unit_cost,on_hand,on_order,annual_demand,daily_demand_std,lead_time_days,order_cost,holding_rate,service_level,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$workspaceId,$sku,$item['name'] ?? 'بلبرینگ نمونه',$item['category'] ?? 'قطعات یدکی',$item['unit'] ?? 'عدد',$item['unit_cost'] ?? 500000,$opening,0,$item['annual_demand'] ?? 240,$item['daily_demand_std'] ?? 2,$item['lead_time_days'] ?? 5,$item['order_cost'] ?? 150000,$item['holding_rate'] ?? 0.25,$item['service_level'] ?? '95',$userId]);
        $itemId = (int) $db->lastInsertId();
        if ($opening > 0) {
            $db->prepare("INSERT INTO inv_movements (workspace_id,item_id,user_id,movement_type,quantity,balance_after,note) VALUES (?,?,?,'opening',?,?,?)")
                ->execute([$workspaceId,$itemId,$userId,$opening,$opening,'موجودی افتتاحیهٔ نمونهٔ آموزشی']);
        }
        return $itemId;
    }

    private function createTimeStudySample(PDO $db, int $userId, string $name, array $payload): int
    {
        $stmt = $db->prepare("INSERT INTO ts_studies (user_id,method,status,title,process_name,department,product_name,work_center,operator_ref,study_date,shift_label,unit_name,method_description,work_conditions,confidence_level,precision_pct,allowance_basis,personal_allowance_pct,fatigue_allowance_pct,delay_allowance_pct,other_allowance_pct,allowance_rationale) VALUES (?,'stopwatch','active',?,'مونتاژ قطعه','تولید','قطعهٔ نمونه','ایستگاه مونتاژ','اپراتور نمونه',CURDATE(),'شیفت روز','قطعه','مطالعهٔ آموزشی با دادهٔ ساختگی؛ پیش از استفادهٔ عملی شرایط و روش را تأیید کنید.','شرایط استاندارد آموزشی','95',10,'standard',5,4,3,0,'مقادیر صرفاً آموزشی؛ allowance باید بر پایهٔ شرایط واقعی تعیین شود.')");
        $stmt->execute([$userId,$name]);
        $studyId = (int) $db->lastInsertId();
        $db->prepare('UPDATE ts_studies SET study_no=? WHERE id=?')->execute(['TS-SAMPLE-' . $studyId,$studyId]);
        $db->prepare("INSERT INTO ts_elements (study_id,sequence_no,element_code,name,element_type,frequency_per_cycle) VALUES (?,1,'MAN-01','جاگذاری و تثبیت قطعه','manual',1),(?,2,'MAC-01','چرخهٔ خودکار دستگاه','machine',1)")->execute([$studyId,$studyId]);
        $elements = $db->prepare('SELECT id,element_type FROM ts_elements WHERE study_id=? ORDER BY sequence_no');
        $elements->execute([$studyId]);
        $elementRows = $elements->fetchAll(PDO::FETCH_ASSOC);
        $cycleInsert = $db->prepare("INSERT INTO ts_cycles (study_id,cycle_no,observed_at,cycle_status,observer_name,note) VALUES (?,?,DATE_ADD(NOW(), INTERVAL ? MINUTE),'valid','نمونهٔ آموزشی','چرخهٔ ساختگی برای نمایش محاسبه')");
        $observationInsert = $db->prepare("INSERT INTO ts_time_observations (study_id,cycle_id,element_id,observed_seconds,performance_rating_pct,is_valid) VALUES (?,?,?,?,?,1)");
        $manualTimes = [14.8,15.2,15.0,15.4,14.9,15.1,14.7,15.3];
        $machineTimes = [24.9,25.1,25.0,25.2,24.8,25.0,25.1,24.9];
        foreach ($manualTimes as $index => $manualTime) {
            $cycleInsert->execute([$studyId,$index + 1,$index]);
            $cycleId = (int) $db->lastInsertId();
            $observationInsert->execute([$studyId,$cycleId,(int)$elementRows[0]['id'],$manualTime,105]);
            $observationInsert->execute([$studyId,$cycleId,(int)$elementRows[1]['id'],$machineTimes[$index],100]);
        }
        return $studyId;
    }

    private function createEconomySample(PDO $db, int $userId, string $name, array $payload): int
    {
        $period = 5;
        $stmt = $db->prepare("INSERT INTO ee_projects (user_id,title,description,study_kind,currency,base_year,study_period,marr_pct,inflation_pct,tax_rate_pct,depreciation_method,status,notes) VALUES (?,?,?,'replacement','IRT',YEAR(CURDATE()),?,12,0,0,'none','draft',?)");
        $stmt->execute([$userId,$name,$payload['problem_statement'] ?? 'ارزیابی اقتصادی نمونهٔ آموزشی با افق پنج‌ساله و نرخ حداقل بازده ۱۲٪.',$period,'جریان‌های نقدی ساختگی؛ پیش از تصمیم، برآوردهای سازمانی و مفروضات را جایگزین کنید.']);
        $projectId = (int) $db->lastInsertId();
        $db->prepare('UPDATE ee_projects SET project_no=? WHERE id=?')->execute(['EE-SAMPLE-' . $projectId,$projectId]);
        $insertAlternative = $db->prepare('INSERT INTO ee_alternatives (project_id,name,description,life_years,is_baseline,sequence_no) VALUES (?,?,?,?,?,?)');
        $insertCashflow = $db->prepare('INSERT INTO ee_cashflows (project_id,alternative_id,year_no,inflow,outflow,label,note) VALUES (?,?,?,?,?,?,?)');
        $alternatives = [
            ['ادامهٔ کار با ماشین فعلی',1,0,0],
            ['خرید ماشین کم‌مصرف',0,1200000000,320000000],
        ];
        foreach ($alternatives as $sequence => [$altName,$baseline,$initial,$annualOutflow]) {
            $insertAlternative->execute([$projectId,$altName,'سناریوی آموزشی برای مقایسهٔ جریان نقدی.', $period,$baseline,$sequence + 1]);
            $alternativeId = (int) $db->lastInsertId();
            for ($year=0; $year <= $period; $year++) {
                $outflow = $year === 0 ? $initial : ($baseline ? 420000000 : $annualOutflow);
                $insertCashflow->execute([$projectId,$alternativeId,$year,0,$outflow,$year === 0 ? 'سرمایه‌گذاری اولیه' : 'هزینهٔ سالانه','فرض نمونه؛ نیازمند جایگزینی با دادهٔ واقعی']);
            }
        }
        return $projectId;
    }

}
