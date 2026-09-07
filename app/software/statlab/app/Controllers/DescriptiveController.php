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

    public function index(): void
    {
        $this->requireAuth();

        $attachProjectId = (int)($_GET['project_id'] ?? 0);
        $attachProject = null;
        if ($attachProjectId > 0) {
            $attachProject = $this->projectModel->find($attachProjectId);
            if (!$attachProject || (int)$attachProject['user_id'] !== (int)$this->currentUserId) {
                $attachProject = null;
                $attachProjectId = 0;
            }
        }

        $this->view('descriptive/index', [
            'pageTitle'       => 'آمار توصیفی',
            'currentPage'     => 'descriptive',
            'projects'        => $this->projectModel->getListForUser($this->currentUserId),
            'attachProject'   => $attachProject,
            'attachProjectId' => $attachProjectId,
        ]);
    }

    /** تحلیل آماری توصیفی (AJAX) */
    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload      = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $dataText     = trim($payload['data'] ?? '');
            $variableName = trim($payload['variable_name'] ?? 'متغیر اصلی') ?: 'متغیر اصلی';
            $projectId    = (int)($payload['project_id'] ?? 0);
            $projectName  = trim($payload['project_name'] ?? '');

            $data = $this->parseNumbers($dataText);
            if (count($data) < 2) {
                $this->json(['success' => false, 'error' => 'حداقل ۲ داده معتبر نیاز است.']);
                return;
            }

            // محاسبات
            $stats = StatEngine::describe($data);
            $interpretation = $this->generateInterpretation($stats);

            // پروژه: بازیابی یا ایجاد
            if ($projectId > 0) {
                $proj = $this->projectModel->find($projectId);
                if (!$proj || (int)$proj['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                $projectId = (int)$this->projectModel->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => $projectName ?: ('تحلیل توصیفی - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل خودکار داده‌های ورودی',
                    'category_code'      => 'descriptive',
                    'analysis_type_code' => 'summary_stats',
                    'objective'          => 'descriptive',
                    'significance_level' => 0.05,
                    'status'             => 'completed',
                ]);
                $this->logActivity('create_project', 'project', $projectId);
                $proj = $this->projectModel->find($projectId);
            }

            // ✅ dataset بدون افزونگی
            $datasetId = $this->resolveDataset($this->datasetModel, $projectId, $data, $variableName, [], 'data1');

            // نتیجه
            $this->resultModel->create([
                'project_id'         => $projectId,
                'dataset_id'         => $datasetId,
                'analysis_type_code' => 'descriptive',
                'test_code'          => 'summary_stats',
                'method_name'        => 'Summary Statistics (آمار توصیفی)',
                'input_params'       => json_encode(['variable' => $variableName], JSON_UNESCAPED_UNICODE),
                'output_data'        => json_encode($stats, JSON_UNESCAPED_UNICODE),
                'interpretation_fa'  => $interpretation,
            ]);

            // result_data تجمیعی پروژه
            $accumulated = json_decode($proj['result_data'] ?? '{}', true) ?: [];
            $accumulated[$variableName] = $stats;
            $this->projectModel->update($projectId, [
                'status'      => 'completed',
                'result_data' => json_encode($accumulated, JSON_UNESCAPED_UNICODE),
                'description' => 'تحلیل آماری ' . count($accumulated) . ' متغیر شامل: ' . implode('، ', array_keys($accumulated)),
            ]);

            $this->logActivity('analyze_descriptive', 'dataset', $datasetId);

            $this->json([
                'success'    => true,
                'project_id' => $projectId,
                'dataset_id' => $datasetId,
                'stats'      => $stats,
                'data_count' => count($data),
            ]);

        } catch (\Throwable $e) {
            error_log('StatLab Descriptive Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function parseNumbers(string $text): array
    {
        $out = [];
        $text = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $text
        );
        foreach (preg_split('/[\s,;\n\r\t|]+/', $text) as $part) {
            $part = trim($part);
            if ($part !== '' && is_numeric($part)) $out[] = (float)$part;
        }
        return $out;
    }

    private function generateInterpretation(array $s): string
    {
        $out = [];
        $n = $s['count'];
        $out[] = $n < 30 ? "⚠️ نمونه کوچک است ({$n} مشاهده)؛ در تعمیم احتیاط کنید." : "✅ حجم نمونه مناسب است ({$n} مشاهده).";
        $sk = $s['skewness'];
        $out[] = abs($sk) < 0.5 ? "📊 توزیع تقریباً متقارن است (چولگی: " . round($sk, 3) . ")."
               : ($sk > 0 ? "📈 چولگی مثبت (دنباله راست): " . round($sk, 3) : "📉 چولگی منفی (دنباله چپ): " . round($sk, 3));
        $ku = $s['kurtosis'];
        $out[] = abs($ku) < 0.5 ? "✓ کشیدگی نزدیک به نرمال." : ($ku > 0 ? "🔺 لپتوکورتیک (دم سنگین)." : "🔻 پلاتیکورتیک (دم سبک).");
        $oc = count($s['outliers']);
        $out[] = $oc > 0 ? "⚠️ {$oc} داده پرت با روش IQR شناسایی شد." : "✓ داده پرت شناسایی نشد.";
        if (abs($s['mean']) > 0.001) {
            $cv = ($s['std'] / abs($s['mean'])) * 100;
            $out[] = $cv > 30 ? "⚠️ ضریب تغییرات بالا (" . round($cv, 2) . "%)." : "✅ ضریب تغییرات قابل قبول (" . round($cv, 2) . "%).";
        }
        return implode("\n", $out);
    }
}