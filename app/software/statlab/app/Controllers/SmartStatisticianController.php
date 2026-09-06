<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Helpers\SmartStatistician;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;

class SmartStatisticianController extends Controller
{
    private $smart;

    public function __construct()
    {
        parent::__construct();
        $this->smart = new SmartStatistician($this->db ?? null);
    }

    public function index(): void
    {
        $this->requireAuth();

        $codes = ['descriptive','distribution','one_sample_t','two_sample_t','paired_t',
                'one_prop_z','chi2_gof','chi2_indep','f_var','mann_whitney',
                'correlation','simple','multiple'];
        $testNames = [];
        foreach ($codes as $c) $testNames[$c] = SmartStatistician::testNameFa($c);

        $this->view('smart_statistician/index', [
            'pageTitle'   => 'دستیار هوشمند آماری',
            'currentPage' => 'smart_statistician',
            'samples'     => SmartStatistician::samples(),
            'testNames'   => $testNames,
        ]);
    }

    /** تحلیل متن (AJAX) */
    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }
        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $result = $this->smart->analyze(trim($payload['text'] ?? ''));
            $this->json($result);
        } catch (\Throwable $e) {
            error_log('StatLab Smart Analyze Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** ایجاد پروژه از تحلیل هوشمند + هدایت به ماژول مربوطه */
    public function createProject(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }
        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $test   = $payload['test'] ?? 'descriptive';
            $text   = trim($payload['text'] ?? '');
            $params = $payload['params'] ?? [];
            $numbers = $payload['numbers'] ?? [];

            // ✅ فیلدهای تعریف دقیق پروژه
            $name = trim($payload['name'] ?? '');
            $description = trim($payload['description'] ?? '');
            $alpha = (float)($params['alpha'] ?? 0.05);
            $alternative = $params['alternative'] ?? 'two';

            if ($name === '') {
                $this->json(['success' => false, 'error' => 'نام پروژه الزامی است.']);
                return;
            }

            $pm = new Project();
            $dm = new Dataset();

            $projectId = $pm->create([
                'user_id'            => $this->currentUserId,
                'name'               => $name,
                'description'        => $description !== '' ? $description : mb_substr($text, 0, 1500),
                'category_code'      => $payload['category'] ?? 'descriptive',
                'analysis_type_code' => $test,
                'objective'          => ($payload['category'] ?? '') === 'regression' ? 'predict' : 'test',
                'significance_level' => $alpha,
                'status'             => 'draft',
                'input_params'       => json_encode(['alternative' => $alternative, 'alpha' => $alpha], JSON_UNESCAPED_UNICODE),
            ]);

            if (is_array($numbers) && count($numbers) >= 2) {
                $dm->create([
                    'project_id'      => $projectId,
                    'user_id'         => $this->currentUserId,
                    'name'            => 'داده استخراج‌شده از متن',
                    'source'          => 'smart_extraction',
                    'data_json'       => json_encode(array_slice($numbers, 0, 500)),
                    'sample_size'     => min(count($numbers), 500),
                    'variables_count' => 1,
                ]);
            }

            $this->logActivity('create_smart', $test, $projectId);

            $route = SmartStatistician::routeFor($test);
            $url = stat_url('controller=' . $route['controller']
                . ($route['param'] !== '' ? '&' . $route['param'] : '')
                . '&project_id=' . $projectId);

            $this->json([
                'success'    => true,
                'project_id' => $projectId,
                'redirect'   => $url,
                'message'    => 'پروژه «' . $name . '» ایجاد شد؛ در حال انتقال...',
            ]);
        } catch (\Throwable $e) {
            error_log('StatLab Smart Create Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}