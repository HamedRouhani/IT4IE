<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;
use App\Software\Statlab\Models\Result;
use App\Software\Statlab\Helpers\StatEngine;

class DescriptiveController extends Controller
{
    private $projectModel;
    private $datasetModel;
    private $resultModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
        $this->datasetModel = new Dataset();
        $this->resultModel  = new Result();
    }

    /**
     * صفحه اصلی آمار توصیفی
     */
    public function index(): void
    {
        $this->requireAuth();

        // ✅ پروژه مقصد برای اتصال داده (اگر از صفحه پروژه آمده باشد)
        $attachProjectId = (int)($_GET['project_id'] ?? 0);
        $attachProject = null;
        if ($attachProjectId > 0) {
            $attachProject = $this->projectModel->find($attachProjectId);
            if (!$attachProject || (int)$attachProject['user_id'] !== (int)$this->currentUserId) {
                $attachProject = null;
                $attachProjectId = 0;
            }
        }

        // لیست پروژه‌های کاربر برای select
        $projects = $this->projectModel->query(
            "SELECT id, name, status FROM `{$this->projectModel->getTableName()}` 
            WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 50",
            ['uid' => $this->currentUserId]
        );

        $this->view('descriptive/index', [
            'pageTitle'       => 'آمار توصیفی',
            'currentPage'     => 'descriptive',
            'projects'        => $projects,
            'attachProject'   => $attachProject,
            'attachProjectId' => $attachProjectId,
        ]);
    }

    /**
     * تحلیل داده‌های ورودی (AJAX)
     * - اگر project_id=0: پروژه جدید ایجاد می‌کند
     * - اگر project_id>0: dataset و result را به پروژه موجود اضافه می‌کند
     * - result_data به صورت تجمیعی ذخیره می‌شود (همه متغیرها در یک JSON)
     */
    public function analyze(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            // ─────────────────────────────────────────────
            // ۱) دریافت و اعتبارسنجی payload
            // ─────────────────────────────────────────────
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $dataText    = trim($payload['data'] ?? '');
            $projectId   = (int)($payload['project_id'] ?? 0);
            $variableName = trim($payload['variable_name'] ?? 'متغیر اصلی');
            $projectName  = trim($payload['project_name'] ?? '');

            if (empty($dataText)) {
                $this->json(['success' => false, 'error' => 'داده‌ای وارد نشده است']);
                return;
            }

            if (mb_strlen($variableName) > 100) {
                $variableName = mb_substr($variableName, 0, 100);
            }

            // ─────────────────────────────────────────────
            // ۲) Parse داده‌ها
            // ─────────────────────────────────────────────
            $data = $this->parseData($dataText);

            if (count($data) < 2) {
                $this->json([
                    'success' => false,
                    'error'   => 'حداقل ۲ داده معتبر نیاز است. تعداد داده‌های معتبر یافت‌شده: ' . count($data)
                ]);
                return;
            }

            // ─────────────────────────────────────────────
            // ۳) محاسبات آماری
            // ─────────────────────────────────────────────
            $stats = StatEngine::describe($data);
            $interpretation = $this->generateInterpretation($stats);

            // ─────────────────────────────────────────────
            // ۴) مدیریت پروژه (ایجاد یا استفاده از موجود)
            // ─────────────────────────────────────────────
            $isNewProject = false;
            $project = null;

            if ($projectId > 0) {
                // ─── استفاده از پروژه موجود ───
                $project = $this->projectModel->find($projectId);
                
                if (!$project) {
                    $this->json(['success' => false, 'error' => 'پروژه یافت نشد.'], 404);
                    return;
                }
                
                // بررسی مالکیت
                if ((int)$project['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به این پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                // ─── ایجاد پروژه جدید (فقط برای اولین متغیر) ───
                $projectId = $this->projectModel->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => $projectName ?: ('تحلیل توصیفی - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل خودکار داده‌های ورودی',
                    'category_code'      => 'descriptive',
                    'analysis_type_code' => 'summary_stats',
                    'objective'          => 'descriptive',
                    'significance_level' => 0.05,
                    'status'             => 'completed',
                ]);
                
                $isNewProject = true;
                $project = $this->projectModel->find($projectId);
                
                $this->logActivity('create_project', 'project', $projectId);
            }

            // ─────────────────────────────────────────────
            // ۵) ذخیره Dataset
            // ─────────────────────────────────────────────
            $datasetId = $this->datasetModel->create([
                'project_id'      => $projectId,
                'user_id'         => $this->currentUserId,
                'name'            => $variableName,
                'source'          => 'manual',
                'data_json'       => json_encode($data, JSON_UNESCAPED_UNICODE),
                'variables'       => json_encode([
                    ['name' => $variableName, 'type' => 'numeric', 'count' => count($data)]
                ], JSON_UNESCAPED_UNICODE),
                'sample_size'     => count($data),
                'variables_count' => 1,
                'has_missing'     => 0,
                'missing_count'   => 0,
            ]);

            // ─────────────────────────────────────────────
            // ۶) ذخیره Result
            // ─────────────────────────────────────────────
            $resultId = $this->resultModel->create([
                'project_id'         => $projectId,
                'dataset_id'         => $datasetId,
                'analysis_type_code' => 'descriptive',
                'test_code'          => 'summary_stats',
                'method_name'        => 'Summary Statistics (آمار توصیفی)',
                'input_params'       => json_encode([
                    'variable' => $variableName,
                    'sample_size' => count($data),
                ], JSON_UNESCAPED_UNICODE),
                'output_data'        => json_encode($stats, JSON_UNESCAPED_UNICODE),
                'interpretation_fa'  => $interpretation,
            ]);

            // ─────────────────────────────────────────────
            // ۷) به‌روزرسانی تجمیعی result_data پروژه
            // ─────────────────────────────────────────────
            $accumulated = [];
            if (!empty($project['result_data'])) {
                $accumulated = json_decode($project['result_data'], true) ?: [];
            }
            
            // اضافه کردن نتیجه فعلی با کلید = نام متغیر
            $accumulated[$variableName] = [
                'dataset_id'      => $datasetId,
                'result_id'       => $resultId,
                'sample_size'     => count($data),
                'stats'           => $stats,
                'interpretation'  => $interpretation,
                'analyzed_at'     => date('Y-m-d H:i:s'),
            ];

            // شمارش کل datasets این پروژه
            $totalDatasets = $this->datasetModel->count(['project_id' => $projectId]);

            $this->projectModel->update($projectId, [
                'status'      => 'completed',
                'result_data' => json_encode($accumulated, JSON_UNESCAPED_UNICODE),
                'description' => sprintf(
                    'تحلیل آماری %d متغیر شامل: %s',
                    $totalDatasets,
                    implode('، ', array_keys($accumulated))
                ),
            ]);

            $this->logActivity('analyze_descriptive', 'dataset', $datasetId);

            // ─────────────────────────────────────────────
            // ۸) ارسال پاسخ موفق
            // ─────────────────────────────────────────────
            $this->json([
                'success'       => true,
                'project_id'    => $projectId,
                'dataset_id'    => $datasetId,
                'result_id'     => $resultId,
                'stats'         => $stats,
                'interpretation'=> $interpretation,
                'data_count'    => count($data),
                'is_new_project'=> $isNewProject,
                'total_variables' => count($accumulated),
                'message'       => $isNewProject 
                    ? 'پروژه جدید ایجاد شد و تحلیل با موفقیت انجام شد.' 
                    : 'متغیر جدید به پروژه موجود اضافه شد.',
                'redirect'      => stat_url('controller=project&action=show&id=' . $projectId),
            ]);

        } catch (\Throwable $e) {
            error_log("StatLab Analyze Error: " . $e->getMessage() . 
                    " in " . $e->getFile() . ":" . $e->getLine());
            
            $this->json([
                'success' => false,
                'error'   => 'خطا در تحلیل: ' . $e->getMessage(),
                'file'    => basename($e->getFile()),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Parse داده‌ها از فرمت‌های مختلف
     * پشتیبانی از: اعداد فارسی/عربی، جداکننده‌های متعدد (newline, comma, semicolon, space, tab)
     */
    private function parseData(string $text): array
    {
        $data = [];
        
        // جداکننده‌های رایج: newline، کاما، semicolon، tab، فاصله
        $parts = preg_split('/[\n\r,;|\t]+/', $text);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            
            // تبدیل اعداد فارسی (۰-۹) به انگلیسی
            $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $english = ['0','1','2','3','4','5','6','7','8','9'];
            
            $part = str_replace($persian, $english, $part);
            $part = str_replace($arabic, $english, $part);
            
            // حذف علائم اضافی (مثل % یا واحد)
            $part = preg_replace('/[^0-9.\-+eE]/', '', $part);
            
            if ($part !== '' && is_numeric($part)) {
                $data[] = (float)$part;
            }
        }
        
        return $data;
    }

    /**
     * تولید تفسیر فارسی هوشمند برای نتایج آماری
     */
    private function generateInterpretation(array $stats): string
    {
        $interp = [];
        $n = $stats['count'];

        // ۱) بررسی حجم نمونه
        if ($n < 5) {
            $interp[] = "⚠️ حجم نمونه بسیار کوچک است ({$n} مشاهده). نتایج قابل اعتماد نیستند.";
        } elseif ($n < 30) {
            $interp[] = "⚠️ نمونه کوچک است ({$n} مشاهده). در استنتاج آماری احتیاط کنید.";
        } else {
            $interp[] = "✅ حجم نمونه مناسب است ({$n} مشاهده).";
        }

        // ۲) مقایسه میانگین و میانه (تشخیص چولگی)
        $mean   = $stats['mean'];
        $median = $stats['median'];
        if (abs($mean - $median) < 0.01 * abs($mean + 0.001)) {
            $interp[] = "📊 میانگین و میانه تقریباً برابرند که نشان‌دهنده تقارن داده‌هاست.";
        } elseif ($mean > $median) {
            $interp[] = "📈 میانگین بزرگ‌تر از میانه است (چولگی مثبت - دنباله بلند در سمت راست).";
        } else {
            $interp[] = "📉 میانگین کوچک‌تر از میانه است (چولگی منفی - دنباله بلند در سمت چپ).";
        }

        // ۳) تحلیل چولگی
        $skew = $stats['skewness'];
        if (abs($skew) < 0.5) {
            $interp[] = "✓ توزیع تقریباً متقارن است (چولگی: " . round($skew, 3) . ").";
        } elseif ($skew > 0.5 && $skew < 1) {
            $interp[] = "↗️ توزیع به سمت راست کمی چوله است (چولگی: " . round($skew, 3) . ").";
        } elseif ($skew >= 1) {
            $interp[] = "⚠️ توزیع به شدت به سمت راست چوله است (چولگی: " . round($skew, 3) . ").";
        } elseif ($skew < -0.5 && $skew > -1) {
            $interp[] = "↙️ توزیع به سمت چپ کمی چوله است (چولگی: " . round($skew, 3) . ").";
        } else {
            $interp[] = "⚠️ توزیع به شدت به سمت چپ چوله است (چولگی: " . round($skew, 3) . ").";
        }

        // ۴) تحلیل کشیدگی (Kurtosis)
        $kurt = $stats['kurtosis'];
        if (abs($kurt) < 0.5) {
            $interp[] = "✓ کشیدگی نزدیک به توزیع نرمال است (کشیدگی: " . round($kurt, 3) . ").";
        } elseif ($kurt > 0) {
            $interp[] = "🔺 توزیع لپتوکورتیک (کشیده‌تر از نرمال) با دم‌های سنگین است.";
        } else {
            $interp[] = "🔻 توزیع پلاتیکورتیک (پهن‌تر از نرمال) با دم‌های سبک است.";
        }

        // ۵) ضریب تغییرات (CV)
        if (abs($mean) > 0.001) {
            $cv = ($stats['std'] / abs($mean)) * 100;
            if ($cv < 15) {
                $interp[] = "✅ ضریب تغییرات پایین است (CV = " . round($cv, 2) . "%) → داده‌ها یکنواخت‌اند.";
            } elseif ($cv < 30) {
                $interp[] = "✓ ضریب تغییرات متوسط است (CV = " . round($cv, 2) . "%) → پراکندگی قابل قبول.";
            } else {
                $interp[] = "⚠️ ضریب تغییرات بالا است (CV = " . round($cv, 2) . "%) → پراکندگی زیاد.";
            }
        }

        // ۶) داده‌های پرت
        $outCount = count($stats['outliers']);
        if ($outCount > 0) {
            $interp[] = "⚠️ {$outCount} داده پرت با روش IQR شناسایی شد. بازبینی آن‌ها توصیه می‌شود.";
        } else {
            $interp[] = "✓ داده پرت شناسایی نشد.";
        }

        return implode("\n", $interp);
    }
}