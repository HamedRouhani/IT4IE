<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Helpers\HypothesisTester as HT;

class HypothesisController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $projects = $this->queryProjects();

        $this->view('hypothesis/index', [
            'pageTitle'   => 'آزمون فرض',
            'currentPage' => 'hypothesis',
            'tests'       => HT::list(),
            'projects'    => $projects,
        ]);
    }

    private function queryProjects(): array
    {
        $pm = new \App\Software\Statlab\Models\Project();
        return $pm->query(
            "SELECT id, name FROM `{$pm->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 50",
            ['uid' => $this->currentUserId]
        );
    }

    /**
     * اجرای آزمون (AJAX) + ذخیره در پروژه
     */
    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $test  = $payload['test'] ?? '';
            $alpha = (float)($payload['alpha'] ?? 0.05);
            $tests = HT::list();

            if (!isset($tests[$test])) {
                $this->json(['success' => false, 'error' => 'آزمون نامعتبر است.']);
                return;
            }

            // اعتبارسنجی ورودی‌ها
            $in = ['alternative' => $payload['alternative'] ?? 'two'];
            $parse = fn($txt) => $this->parseNumbers((string)$txt);

            switch ($test) {
                case 'one_sample_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['mu0'] = (float)($payload['mu0'] ?? 0);
                    if (count($in['data1']) < 3) throw new \Exception('حداقل ۳ داده لازم است.');
                    break;
                case 'two_sample_t':
                case 'f_var':
                case 'mann_whitney':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) < 3 || count($in['data2']) < 3) throw new \Exception('هر گروه حداقل ۳ داده لازم دارد.');
                    break;
                case 'paired_t':
                    $in['data1'] = $parse($payload['data1'] ?? '');
                    $in['data2'] = $parse($payload['data2'] ?? '');
                    if (count($in['data1']) !== count($in['data2'])) throw new \Exception('در آزمون جفتی، تعداد داده‌های دو گروه باید برابر باشد.');
                    if (count($in['data1']) < 3) throw new \Exception('حداقل ۳ جفت داده لازم است.');
                    break;
                case 'one_prop_z':
                    $in['x'] = (int)($payload['x'] ?? 0);
                    $in['n'] = (int)($payload['n'] ?? 0);
                    $in['p0'] = (float)($payload['p0'] ?? 0.5);
                    if ($in['n'] < 5 || $in['x'] > $in['n']) throw new \Exception('مقادیر x و n معتبر نیستند.');
                    break;
                case 'chi2_gof':
                    $in['observed'] = $parse($payload['observed'] ?? '');
                    $in['expected'] = $parse($payload['expected'] ?? '');
                    if (count($in['observed']) < 2) throw new \Exception('حداقل ۲ دسته لازم است.');
                    break;
                case 'chi2_indep':
                    $in['matrix'] = $this->parseMatrix((string)($payload['matrix'] ?? ''));
                    if (count($in['matrix']) < 2 || count($in['matrix'][0]) < 2) throw new \Exception('جدول توافقی حداقل ۲×۲ لازم است.');
                    break;
            }

            // اجرای آزمون
            $result = HT::run($test, $in, $alpha);

            // ─── ذخیره در پروژه ───
            $projectId = (int)($payload['project_id'] ?? 0);
            $projectName = trim($payload['project_name'] ?? '');
            $pm = new \App\Software\Statlab\Models\Project();
            $dm = new \App\Software\Statlab\Models\Dataset();
            $rm = new \App\Software\Statlab\Models\Result();

            if ($projectId > 0) {
                $proj = $pm->find($projectId);
                if (!$proj || (int)$proj['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                $projectId = $pm->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => $projectName ?: ($tests[$test]['name_fa'] . ' - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل آزمون فرض',
                    'category_code'      => 'hypothesis',
                    'analysis_type_code' => $test,
                    'objective'          => 'test',
                    'significance_level' => $alpha,
                    'status'             => 'completed',
                ]);
                $this->logActivity('create_project', 'project', $projectId);
            }

            // ذخیره datasetها
            if (!empty($in['data1'])) {
                $dm->create([
                    'project_id' => $projectId, 'user_id' => $this->currentUserId,
                    'name' => 'نمونه ۱', 'source' => 'manual',
                    'data_json' => json_encode($in['data1']), 'sample_size' => count($in['data1']), 'variables_count' => 1,
                ]);
            }
            if (!empty($in['data2'])) {
                $dm->create([
                    'project_id' => $projectId, 'user_id' => $this->currentUserId,
                    'name' => 'نمونه ۲', 'source' => 'manual',
                    'data_json' => json_encode($in['data2']), 'sample_size' => count($in['data2']), 'variables_count' => 1,
                ]);
            }

            // ذخیره result
            $rm->create([
                'project_id'         => $projectId,
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

            $pm->update($projectId, ['status' => 'completed']);
            $this->logActivity('analyze_hypothesis', 'project', $projectId);

            $this->json([
                'success'    => true,
                'test_name'  => $tests[$test]['name_fa'],
                'result'     => $result,
                'project_id' => $projectId,
            ]);

        } catch (\Throwable $e) {
            error_log("StatLab Hypothesis Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function parseNumbers(string $text): array
    {
        $out = [];
        $text = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
                            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'], $text);
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
        // همسان‌سازی طول سطرها
        $max = max(array_map('count', $rows));
        foreach ($rows as &$r) $r = array_pad($r, $max, 0);
        return $rows;
    }
}