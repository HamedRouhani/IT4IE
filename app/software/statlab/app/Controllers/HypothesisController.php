<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;
use App\Software\Statlab\Models\Result;
use App\Software\Statlab\Helpers\HypothesisTester as HT;

class HypothesisController extends Controller
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
        $this->view('hypothesis/index', [
            'pageTitle'       => 'آزمون فرض',
            'currentPage'     => 'hypothesis',
            'tests'           => HT::list(),
            'projects'        => $this->projectModel->getListForUser($this->currentUserId),
            'presetTest'      => trim($_GET['test'] ?? ''),
            'presetProjectId' => (int)($_GET['project_id'] ?? 0),
        ]);
    }

    /** اجرای آزمون فرض (AJAX) */
    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $test    = $payload['test'] ?? '';
            $alpha   = (float)($payload['alpha'] ?? 0.05);
            $tests   = HT::list();

            if (!isset($tests[$test])) {
                $this->json(['success' => false, 'error' => 'آزمون نامعتبر است.']);
                return;
            }

            // ─── ۱) اعتبارسنجی ورودی‌ها ───
            $in    = ['alternative' => $payload['alternative'] ?? 'two'];
            $parse = fn($txt) => $this->parseNumbers((string)$txt);

            switch ($test) {
                case 'one_sample_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['mu0']   = (float)($payload['mu0'] ?? 0);
                    if (count($in['data1']) < 3) throw new \Exception('حداقل ۳ داده لازم است.');
                    break;

                case 'two_sample_t':
                case 'f_var':
                case 'mann_whitney':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) < 3 || count($in['data2']) < 3)
                        throw new \Exception('هر گروه حداقل ۳ داده لازم دارد.');
                    break;

                case 'paired_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) !== count($in['data2']))
                        throw new \Exception('در آزمون جفتی، تعداد داده‌های دو گروه باید برابر باشد.');
                    if (count($in['data1']) < 3) throw new \Exception('حداقل ۳ جفت داده لازم است.');
                    break;

                case 'one_prop_z':
                    $in['x']  = (int)($payload['x'] ?? 0);
                    $in['n']  = (int)($payload['n'] ?? 0);
                    $in['p0'] = (float)($payload['p0'] ?? 0.5);
                    if ($in['n'] < 5 || $in['x'] < 0 || $in['x'] > $in['n'])
                        throw new \Exception('مقادیر x و n معتبر نیستند.');
                    break;

                case 'chi2_gof':
                    $in['observed'] = $parse($payload['observed'] ?? '');
                    $in['expected'] = $parse($payload['expected'] ?? '');
                    if (count($in['observed']) < 2) throw new \Exception('حداقل ۲ دسته لازم است.');
                    break;

                case 'chi2_indep':
                    $in['matrix'] = $this->parseMatrix((string)($payload['matrix'] ?? ''));
                    if (count($in['matrix']) < 2 || count($in['matrix'][0]) < 2)
                        throw new \Exception('جدول توافقی حداقل ۲×۲ لازم است.');
                    break;
            }

            // ─── ۲) اجرای آزمون ───
            $result = HT::run($test, $in, $alpha);

            // ─── ) پروژه: بازیابی یا ایجاد ───
            $projectId = (int)($payload['project_id'] ?? 0);
            if ($projectId > 0) {
                $proj = $this->projectModel->find($projectId);
                if (!$proj || (int)$proj['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                $projectId = (int)$this->projectModel->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => trim($payload['project_name'] ?? '') ?: ($tests[$test]['name_fa'] . ' - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل آزمون فرض',
                    'category_code'      => 'hypothesis',
                    'analysis_type_code' => $test,
                    'objective'          => 'test',
                    'significance_level' => $alpha,
                    'status'             => 'completed',
                ]);
                $this->logActivity('create_project', 'project', $projectId);
            }

            // ─── ۴) ✅ datasetها بدون افزونگی ───
            $sourceIds = $payload['source_ids'] ?? [];
            $ds1 = !empty($in['data1'])
                ? $this->resolveDataset($this->datasetModel, $projectId, $in['data1'], 'نمونه ۱', $sourceIds, 'data1')
                : null;
            $ds2 = !empty($in['data2'])
                ? $this->resolveDataset($this->datasetModel, $projectId, $in['data2'], 'نمونه ۲', $sourceIds, 'data2')
                : null;

            // ─── ) ذخیره نتیجه ───
            $this->resultModel->create([
                'project_id'         => $projectId,
                'dataset_id'         => $ds1,
                'analysis_type_code' => 'hypothesis',
                'test_code'          => $test,
                'method_name'        => $tests[$test]['name_fa'],
                'input_params'       => json_encode(['alpha' => $alpha, 'alternative' => $in['alternative']], JSON_UNESCAPED_UNICODE),
                'output_data'        => json_encode($result, JSON_UNESCAPED_UNICODE),
                'p_value'            => min(1, max(0, $result['p_value'])),
                'test_statistic'     => $result['statistic'],
                'degrees_of_freedom' => is_int($result['df']) ? $result['df'] : null,
                'conclusion'         => $result['conclusion'] === 'reject' ? 'reject_h0' : 'fail_to_reject_h0',
                'effect_size'        => $result['extra']['effect_size'] ?? null,
                'interpretation_fa'  => $result['interpretation_fa'],
            ]);

            $this->projectModel->update($projectId, ['status' => 'completed']);
            $this->logActivity('analyze_hypothesis', $test, $projectId);

            $this->json([
                'success'     => true,
                'test_name'   => $tests[$test]['name_fa'],
                'result'      => $result,
                'project_id'  => $projectId,
                'dataset_ids' => ['data1' => $ds1, 'data2' => $ds2],
            ]);

        } catch (\Throwable $e) {
            error_log('StatLab Hypothesis Error: ' . $e->getMessage());
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

    private function parseMatrix(string $text): array
    {
        $rows = [];
        foreach (preg_split('/\n+/', trim($text)) as $line) {
            $vals = $this->parseNumbers($line);
            if (!empty($vals)) $rows[] = $vals;
        }
        if (empty($rows)) return [];
        $max = max(array_map('count', $rows));
        foreach ($rows as &$r) $r = array_pad($r, $max, 0);
        return $rows;
    }
}