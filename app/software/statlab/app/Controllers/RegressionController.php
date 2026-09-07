<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;
use App\Software\Statlab\Models\Result;
use App\Software\Statlab\Helpers\RegressionEngine as RE;

class RegressionController extends Controller
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
        $this->view('regression/index', [
            'pageTitle'       => 'رگرسیون و همبستگی',
            'currentPage'     => 'regression',
            'projects'        => $this->projectModel->getListForUser($this->currentUserId),
            'presetMode'      => trim($_GET['mode'] ?? ''),
            'presetProjectId' => (int)($_GET['project_id'] ?? 0),
        ]);
    }

    /** اجرای رگرسیون/همبستگی (AJAX) */
    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $mode    = $payload['mode'] ?? 'simple';
            $alpha   = (float)($payload['alpha'] ?? 0.05);

            $Y = $this->parseNumbers((string)($payload['y'] ?? ''));
            $n = count($Y);
            if ($n < 3) throw new \Exception('حداقل ۳ مشاهده برای Y لازم است.');

            $k  = ($mode === 'multiple') ? max(1, min(5, (int)($payload['k'] ?? 1))) : 1;
            $Xs = [];
            for ($j = 1; $j <= $k; $j++) {
                $xj = $this->parseNumbers((string)($payload['x' . $j] ?? ''));
                if (count($xj) !== $n) throw new \Exception("تعداد داده‌های X$j باید برابر Y باشد ($n).");
                $Xs[] = $xj;
            }
            if ($mode === 'multiple' && $n <= $k + 1) {
                throw new \Exception('تعداد مشاهدات باید بیشتر از (تعداد پیشبین‌ها + ۱) باشد.');
            }

            // ─── اجرای محاسبات ───
            if ($mode === 'correlation')     $result = RE::correlation($Xs[0], $Y, $alpha);
            elseif ($mode === 'simple')      $result = RE::simple($Xs[0], $Y, $alpha);
            else                             $result = RE::multiple($Y, $Xs, $alpha);

            // ─── پروژه: بازیابی یا ایجاد ───
            $projectId = (int)($payload['project_id'] ?? 0);
            if ($projectId > 0) {
                $proj = $this->projectModel->find($projectId);
                if (!$proj || (int)$proj['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                $titles = ['correlation' => 'تحلیل همبستگی', 'simple' => 'رگرسیون خطی ساده', 'multiple' => 'رگرسیون چندگانه'];
                $projectId = (int)$this->projectModel->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => trim($payload['project_name'] ?? '') ?: (($titles[$mode] ?? 'رگرسیون') . ' - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل رگرسیون/همبستگی',
                    'category_code'      => 'regression',
                    'analysis_type_code' => $mode,
                    'objective'          => 'predict',
                    'significance_level' => $alpha,
                    'status'             => 'completed',
                ]);
                $this->logActivity('create_project', 'project', $projectId);
            }

            // ─── ✅ datasetها بدون افزونگی ───
            $sourceIds = $payload['source_ids'] ?? [];
            $dsY = $this->resolveDataset($this->datasetModel, $projectId, $Y, 'Y (وابسته)', $sourceIds, 'y');
            foreach ($Xs as $j => $xj) {
                $this->resolveDataset($this->datasetModel, $projectId, $xj, 'X' . $j, $sourceIds, 'x' . $j);
            }

            // ─── ذخیره نتیجه ───
            $pval = $result['p_slope'] ?? $result['p_F'] ?? $result['pearson_p'] ?? null;
            $this->resultModel->create([
                'project_id'         => $projectId,
                'dataset_id'         => $dsY,
                'analysis_type_code' => 'regression',
                'test_code'          => $mode,
                'method_name'        => ['correlation' => 'همبستگی پیرسون/اسپیرمن', 'simple' => 'رگرسیون خطی ساده', 'multiple' => 'رگرسیون چندگانه (OLS)'][$mode],
                'input_params'       => json_encode(['alpha' => $alpha, 'k' => $k], JSON_UNESCAPED_UNICODE),
                'output_data'        => json_encode($result, JSON_UNESCAPED_UNICODE),
                'p_value'            => $pval !== null ? min(1, max(0, $pval)) : null,
                'conclusion'         => ($pval !== null && $pval < $alpha) ? 'significant' : 'not_significant',
                'interpretation_fa'  => $result['interpretation_fa'],
            ]);

            $this->projectModel->update($projectId, ['status' => 'completed']);
            $this->logActivity('analyze_regression', $mode, $projectId);

            $this->json([
                'success'    => true,
                'mode'       => $mode,
                'result'     => $result,
                'project_id' => $projectId,
            ]);

        } catch (\Throwable $e) {
            error_log('StatLab Regression Error: ' . $e->getMessage());
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
}