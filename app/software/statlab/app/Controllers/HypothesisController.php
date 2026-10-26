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
            $test = $payload['test'] ?? '';
            $alpha = \App\Software\Statlab\Helpers\InputValidator::alpha($payload['alpha'] ?? 0.05);
            $tests   = HT::list();

            if (!is_string($test) || !isset($tests[$test])) {
                $this->json(['success' => false, 'error' => 'آزمون نامعتبر است.']);
                return;
            }

            // ─── ۱) اعتبارسنجی ورودی‌ها ───
            $in    = ['alternative' => $payload['alternative'] ?? 'two'];
            $parse = fn($txt) => $this->parseNumbers((string)$txt);

            switch ($test) {
                case 'one_sample_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['mu0']   = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['mu0'] ?? 0, 'میانگین فرضی');
                    if (count($in['data1']) < 3) throw new \InvalidArgumentException('حداقل ۳ داده لازم است.');
                    break;

                case 'two_sample_t':
                case 'f_var':
                case 'mann_whitney':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) < 3 || count($in['data2']) < 3)
                        throw new \InvalidArgumentException('هر گروه حداقل ۳ داده لازم دارد.');
                    break;

                case 'paired_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) !== count($in['data2']))
                        throw new \InvalidArgumentException('در آزمون جفتی، تعداد داده‌های دو گروه باید برابر باشد.');
                    if (count($in['data1']) < 3) throw new \InvalidArgumentException('حداقل ۳ جفت داده لازم است.');
                    break;

                case 'one_prop_z':
                    $x = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['x'] ?? 0, 'تعداد موفقیت');
                    $n = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['n'] ?? 0, 'حجم نمونه');
                    if (floor($x) !== $x || floor($n) !== $n) {
                        throw new \InvalidArgumentException('تعداد موفقیت و حجم نمونه باید عدد صحیح باشند.');
                    }
                    $in['x']  = (int) $x;
                    $in['n']  = (int) $n;
                    $in['p0'] = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['p0'] ?? 0.5, 'نسبت فرضی');
                    if ($in['n'] < 5 || $in['x'] < 0 || $in['x'] > $in['n'])
                        throw new \InvalidArgumentException('مقادیر x و n معتبر نیستند.');
                    break;

                case 'chi2_gof':
                    $in['observed'] = $parse($payload['observed'] ?? '');
                    $in['expected'] = $parse($payload['expected'] ?? '');
                    if (count($in['observed']) < 2) throw new \InvalidArgumentException('حداقل ۲ دسته لازم است.');
                    break;

                case 'chi2_indep':
                    $in['matrix'] = $this->parseMatrix((string)($payload['matrix'] ?? ''));
                    if (count($in['matrix']) < 2 || count($in['matrix'][0]) < 2)
                        throw new \InvalidArgumentException('جدول توافقی حداقل ۲×۲ لازم است.');
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
            $this->json(['success' => false, 'error' => $e->getMessage()], $e instanceof \InvalidArgumentException ? 422 : 500);
        }
    }

    private function parseNumbers(string $text): array
    {
        return \App\Software\Statlab\Helpers\InputValidator::parseNumbers($text);
    }

    private function parseMatrix(string $text): array
    {
        return \App\Software\Statlab\Helpers\InputValidator::parseMatrix($text);
    }
}
