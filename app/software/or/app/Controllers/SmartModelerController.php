<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Helpers\SmartModeler;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\QueueingProject;
use App\Software\Or\Models\MarkovProject;
use App\Software\Or\Models\GameTheoryProject;
use App\Software\Or\Models\MonteCarloProject;
use App\Software\Or\Models\ILPProject;
use App\Software\Or\Models\DualProject;
use App\Software\Or\Helpers\QueueingEngine;
use App\Software\Or\Helpers\MarkovEngine;
use App\Software\Or\Helpers\GameTheoryEngine;
use App\Software\Or\Helpers\MonteCarloEngine;
use App\Software\Or\Helpers\ILPEngine;
use App\Software\Or\Helpers\DualSimplexEngine;

class SmartModelerController extends Controller
{
    private $modeler;
    private $model;

    /**
     * نگاشت کامل انواع مسئله به کنترلرها و متاداده
     * group: network | lp | advanced
     */
    private const TYPE_MAP = [
        'TRANS'       => ['controller' => 'transport',    'code' => 'TRANS',       'group' => 'network'],
        'ASSIGN'      => ['controller' => 'assignment',   'code' => 'ASSIGN',      'group' => 'network'],
        'SHORTEST'    => ['controller' => 'shortest',     'code' => 'SHORTEST',    'group' => 'network'],
        'TRANSSHIP'   => ['controller' => 'transship',    'code' => 'TRANSSHIP',   'group' => 'network'],
        'LP'          => ['controller' => 'simplex',      'code' => 'LP',          'group' => 'lp'],
        'QUEUEING'    => ['controller' => 'queueing',     'code' => 'QUEUEING',    'group' => 'advanced'],
        'MARKOV'      => ['controller' => 'markov',       'code' => 'MARKOV',      'group' => 'advanced'],
        'GAME_THEORY' => ['controller' => 'game_theory',  'code' => 'GAME_THEORY', 'group' => 'advanced'],
        'MONTE_CARLO' => ['controller' => 'monte_carlo',  'code' => 'MONTE_CARLO', 'group' => 'advanced'],
        'ILP'         => ['controller' => 'ilp',          'code' => 'ILP',         'group' => 'advanced'],
        'DUAL'        => ['controller' => 'dual',         'code' => 'DUAL',        'group' => 'advanced'],
    ];

    public function __construct()
    {
        parent::__construct();
        $dbConnection = property_exists($this, 'db') ? $this->db : null;
        $this->modeler = new SmartModeler($dbConnection);
        $this->model = new Project();
    }

    /**
     * صفحه اصلی مدلسازی هوشمند
     */
    public function index()
    {
        $this->requireAuth();
        $samples = $this->modeler->getSamples();
        $this->view('smart_modeler/index', [
            'pageTitle'   => 'مدلسازی هوشمند OR',
            'currentPage' => 'smart_modeler',
            'samples'     => $samples,
        ]);
    }

    /**
     * تحلیل متن کاربر (AJAX)
     */
    public function analyze()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }
        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $text = trim($payload['text'] ?? '');
            if (empty($text)) {
                $this->json(['success' => false, 'error' => 'متن خالی است']);
                return;
            }
            $result = $this->modeler->analyze($text);
            $this->json($result);
        } catch (\Exception $e) {
            error_log("SmartModeler Analyze Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    /**
     * دریافت نمونه واقعی
     */
    public function sample($id)
    {
        $this->requireAuth();
        $samples = $this->modeler->getSamples();
        $sample = null;
        foreach ($samples as $s) {
            if ($s['id'] == $id) {
                $sample = $s;
                break;
            }
        }
        if (!$sample) {
            $this->json(['success' => false, 'error' => 'نمونه یافت نشد']);
            return;
        }
        $this->json(['success' => true, 'sample' => $sample]);
    }

    /**
     * ═══════════════════════════════════════════════════════
     * ایجاد پروژه خودکار از تحلیل هوشمند
     * پشتیبانی از هر ۱۱ نوع مسئله
     * ═══════════════════════════════════════════════════════
     */
    public function createProject()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $type      = $payload['type'] ?? '';
            $modelData = $payload['model_data'] ?? [];

            if (!isset(self::TYPE_MAP[$type])) {
                $this->json(['success' => false, 'error' => "نوع مسئله '{$type}' نامعتبر است"]);
                return;
            }

            $typeInfo = self::TYPE_MAP[$type];
            $group    = $typeInfo['group'];

            // مسیریابی بر اساس گروه
            if ($group === 'network' || $group === 'lp') {
                $result = $this->createNetworkOrLPProject($type, $modelData, $typeInfo);
            } else {
                $result = $this->createAdvancedProject($type, $modelData, $typeInfo);
            }

            if (!$result['success']) {
                $this->json($result);
                return;
            }

            $this->logActivity('create_smart', $typeInfo['controller'], $result['project_id']);

            $this->json([
                'success'    => true,
                'project_id' => $result['project_id'],
                'redirect'   => "?controller={$typeInfo['controller']}&action=show&id={$result['project_id']}",
                'message'    => '✅ پروژه با موفقیت و با داده‌های استخراج‌شده ایجاد شد!'
            ]);

        } catch (\Exception $e) {
            error_log("SmartModeler CreateProject Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════
    // ایجاد پروژه‌های شبکه‌ای و LP (جدول or_projects)
    // ═══════════════════════════════════════════════════════
    private function createNetworkOrLPProject(string $type, array $modelData, array $typeInfo): array
    {
        $pt = $this->getProblemTypeSafe($typeInfo['code']);
        $projectObjective = $modelData['objective'] ?? 'minimize';

        // تبدیل مدل LP به فرمت استاندارد سیمپلکس
        if ($type === 'LP' && !empty($modelData['variables']) && !empty($modelData['constraints'])) {
            $c = [];
            foreach ($modelData['variables'] as $var) {
                $c[] = (float)($var['coeff'] ?? 0);
            }
            $A = []; $b = []; $types = [];
            foreach ($modelData['constraints'] as $const) {
                $A[] = array_map('floatval', $const['coeffs'] ?? []);
                $b[] = (float)($const['capacity'] ?? 0);
                $types[] = $const['type'] ?? '<=';
            }
            $modelData = [
                'c' => $c, 'A' => $A, 'b' => $b, 'types' => $types,
                'name' => $modelData['name'] ?? '',
                'description' => $modelData['description'] ?? '',
                'objective' => $modelData['objective'] ?? 'maximize',
            ];
            $projectObjective = $modelData['objective'];
        }

        // ۱. ایجاد پروژه اصلی
        $projectId = $this->model->create([
            'user_id'         => $this->currentUserId,
            'name'            => $modelData['name'] ?? "پروژه {$typeInfo['code']} هوشمند",
            'description'     => $modelData['description'] ?? '',
            'problem_type_id' => $pt['id'] ?? null,
            'objective'       => $projectObjective,
            'status'          => 'draft',
            'model_data'      => json_encode($modelData, JSON_UNESCAPED_UNICODE),
        ]);

        // ۲. پیاده‌سازی گره‌ها و یال‌ها برای مدل‌های شبکه‌ای
        $this->buildNetworkStructure($type, $projectId, $modelData);

        return ['success' => true, 'project_id' => $projectId];
    }

    /**
     * ساخت گره‌ها و یال‌ها برای مسائل شبکه‌ای
     */
    private function buildNetworkStructure(string $type, int $projectId, array $modelData): void
    {
        if ($type === 'SHORTEST' && !empty($modelData['nodes'])) {
            $nodeIds = [];
            foreach ($modelData['nodes'] as $i => $node) {
                $nodeIds[$i] = $this->model->addNode($projectId, 'source', $node['name'], 0, $i);
            }
            if (!empty($modelData['edges'])) {
                foreach ($modelData['edges'] as $edge) {
                    $fromIdx = $edge['from']; $toIdx = $edge['to'];
                    $weight = (float)($edge['weight'] ?? 0);
                    if (isset($nodeIds[$fromIdx]) && isset($nodeIds[$toIdx])) {
                        $this->model->setEdge($projectId, $nodeIds[$fromIdx], $nodeIds[$toIdx], $weight, 0);
                    }
                }
            }
        } 
        elseif ($type === 'TRANS' && !empty($modelData['sources'])) {
            $sourceIds = [];
            foreach ($modelData['sources'] as $i => $src) {
                $sourceIds[$i] = $this->model->addNode($projectId, 'source', $src['name'], (int)($src['capacity'] ?? 0), $i);
            }
            if (!empty($modelData['destinations'])) {
                $destIds = [];
                foreach ($modelData['destinations'] as $j => $dst) {
                    $destIds[$j] = $this->model->addNode($projectId, 'destination', $dst['name'], (int)($dst['demand'] ?? $dst['capacity'] ?? 0), $j);
                }
                if (!empty($modelData['cost_matrix'])) {
                    foreach ($modelData['cost_matrix'] as $i => $row) {
                        foreach ($row as $j => $cost) {
                            $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                            $costVal = $isProhib ? null : (float)$cost;
                            $this->model->setEdge($projectId, $sourceIds[$i], $destIds[$j], $costVal, $isProhib);
                        }
                    }
                }
            }
        } 
        elseif ($type === 'ASSIGN' && !empty($modelData['agents'])) {
            $agentIds = [];
            foreach ($modelData['agents'] as $i => $agent) {
                $agentIds[$i] = $this->model->addNode($projectId, 'source', $agent['name'], 1, $i);
            }
            if (!empty($modelData['tasks'])) {
                $taskIds = [];
                foreach ($modelData['tasks'] as $j => $task) {
                    $taskIds[$j] = $this->model->addNode($projectId, 'destination', $task['name'], 1, $j);
                }
                if (!empty($modelData['cost_matrix'])) {
                    foreach ($modelData['cost_matrix'] as $i => $row) {
                        foreach ($row as $j => $cost) {
                            $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                            $costVal = $isProhib ? null : (float)$cost;
                            $this->model->setEdge($projectId, $agentIds[$i], $taskIds[$j], $costVal, $isProhib);
                        }
                    }
                }
            }
        }
    }

    // ═══════════════════════════════════════════════════════
    // ایجاد پروژه‌های پیشرفته (جداول اختصاصی)
    // ═══════════════════════════════════════════════════════
    private function createAdvancedProject(string $type, array $modelData, array $typeInfo): array
    {
        switch ($type) {
            case 'QUEUEING':
                return $this->createQueueingProject($modelData);
            case 'MARKOV':
                return $this->createMarkovProject($modelData);
            case 'GAME_THEORY':
                return $this->createGameProject($modelData);
            case 'MONTE_CARLO':
                return $this->createMonteCarloProject($modelData);
            case 'ILP':
                return $this->createILPProject($modelData);
            case 'DUAL':
                return $this->createDualProject($modelData);
            default:
                return ['success' => false, 'error' => 'نوع مسئله پیشرفته شناسایی نشد'];
        }
    }

    /**
     * ایجاد پروژه صف + اجرای آنی موتور حل
     */
    private function createQueueingProject(array $d): array
    {
        $modelCode = $d['model_code'] ?? 'MM1';
        $params = [
            'lambda'      => (float)($d['lambda'] ?? 10),
            'mu'          => (float)($d['mu'] ?? 15),
            'servers'     => (int)($d['servers'] ?? 1),
            'capacity'    => isset($d['capacity']) ? (int)$d['capacity'] : null,
            'service_std' => isset($d['service_std']) ? (float)$d['service_std'] : null,
        ];

        // ✅ اجرای موتور حل قبل از ذخیره
        try {
            $result = QueueingEngine::solve($modelCode, $params);
        } catch (\Throwable $e) {
            error_log('SmartModeler Queueing Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $model = new QueueingProject();
        $id = $model->createQueueing([
            'user_id'     => $this->currentUserId,
            'name'        => $d['name'] ?? 'پروژه صف (استخراج هوشمند)',
            'description' => $d['description'] ?? '',
            'model_code'  => $modelCode,
            'lambda'      => $params['lambda'],
            'mu'          => $params['mu'],
            'servers'     => $params['servers'],
            'capacity'    => $params['capacity'],
            'service_std' => $params['service_std'],
            'result_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * ایجاد پروژه مارکوف + اجرای آنی موتور حل
     */
    private function createMarkovProject(array $d): array
    {
        $states = $d['states'] ?? ['حالت 1', 'حالت 2'];
        $matrix = $d['transition_matrix'] ?? [[0.5, 0.5], [0.5, 0.5]];
        $initial = $d['initial_state'] ?? null;
        $n = count($states);
        
        if ($initial === null || count($initial) !== $n) {
            $initial = array_fill(0, $n, 0);
            $initial[0] = 1;
        }
        $steps = (int)($d['steps'] ?? 5);

        // ✅ اجرای موتور حل
        try {
            $result = MarkovEngine::solve($states, $matrix, $initial, $steps);
        } catch (\Throwable $e) {
            error_log('SmartModeler Markov Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'errors' => [$e->getMessage()]];
        }

        $model = new MarkovProject();
        $id = $model->createMarkov([
            'user_id'                => $this->currentUserId,
            'name'                   => $d['name'] ?? 'پروژه مارکوف (استخراج هوشمند)',
            'description'            => $d['description'] ?? '',
            'states_json'            => json_encode($states, JSON_UNESCAPED_UNICODE),
            'transition_matrix_json' => json_encode($matrix),
            'initial_state_json'     => json_encode($initial),
            'steps'                  => $steps,
            'result_json'            => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * ایجاد پروژه نظریه بازی‌ها + اجرای آنی موتور حل
     * ⚠️ نکته: فرمت payoff_matrix در دیتابیس {"A": [...], "B": [...]} است
     */
    private function createGameProject(array $d): array
    {
        $p1 = $d['player1_strategies'] ?? ['استراتژی 1', 'استراتژی 2'];
        $p2 = $d['player2_strategies'] ?? ['استراتژی 1', 'استراتژی 2'];
        $A  = $d['payoff_matrix'] ?? [[3, -2], [-1, 4]];
        $B  = $d['payoff_matrix_b'] ?? [];

        $gameType = empty($B) ? 'zero_sum' : 'bimatrix';

        // ✅ اجرای موتور حل
        try {
            $result = GameTheoryEngine::solve($gameType, $p1, $p2, $A, $B);
        } catch (\Throwable $e) {
            error_log('SmartModeler Game Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $model = new GameTheoryProject();
        $id = $model->createGame([
            'user_id'            => $this->currentUserId,
            'name'               => $d['name'] ?? 'پروژه نظریه بازی‌ها (استخراج هوشمند)',
            'description'        => $d['description'] ?? '',
            'player1_strategies' => json_encode($p1, JSON_UNESCAPED_UNICODE),
            'player2_strategies' => json_encode($p2, JSON_UNESCAPED_UNICODE),
            'payoff_matrix'      => json_encode(['A' => $A, 'B' => $B], JSON_UNESCAPED_UNICODE),
            'result_json'        => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * ایجاد پروژه مونت‌کارلو + اجرای آنی شبیه‌سازی
     */
    private function createMonteCarloProject(array $d): array
    {
        $variables  = $d['variables'] ?? [];
        $funcExpr   = $d['function_expr'] ?? '';
        $iterations = (int)($d['iterations'] ?? 10000);
        $seed       = isset($d['seed']) ? (int)$d['seed'] : null;

        // ✅ اجرای شبیه‌سازی قبل از ذخیره
        $result = null;
        try {
            if (!empty($variables) && $funcExpr !== '') {
                $result = MonteCarloEngine::simulate($variables, $funcExpr, $iterations, $seed);
            } else {
                $result = ['status' => 'error', 'message' => 'متغیرها یا تابع هدف استخراج نشد.'];
            }
        } catch (\Throwable $e) {
            error_log('SmartModeler MonteCarlo Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $model = new MonteCarloProject();
        $id = $model->createMonteCarlo([
            'user_id'        => $this->currentUserId,
            'name'           => $d['name'] ?? 'پروژه مونت‌کارلو (استخراج هوشمند)',
            'description'    => $d['description'] ?? '',
            'variables_json' => json_encode($variables, JSON_UNESCAPED_UNICODE),
            'function_expr'  => $funcExpr,
            'iterations'     => $iterations,
            'seed'           => $seed,
            'result_json'    => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * ایجاد پروژه ILP + اجرای آنی Branch & Bound
     */
    private function createILPProject(array $d): array
    {
        $c = []; $A = []; $b = []; $types = []; $intVars = [];

        if (!empty($d['variables'])) {
            foreach ($d['variables'] as $i => $var) {
                $c[] = (float)($var['coeff'] ?? 0);
                if (!empty($var['is_integer'])) $intVars[] = $i;
            }
        }
        if (!empty($d['constraints'])) {
            foreach ($d['constraints'] as $const) {
                $A[] = array_map('floatval', $const['coeffs'] ?? []);
                $b[] = (float)($const['capacity'] ?? 0);
                $types[] = $const['type'] ?? '<=';
            }
        }
        if (empty($intVars)) $intVars = array_keys($c); // همه صحیح
        $sense = $d['objective'] ?? 'maximize';

        // ✅ اجرای Branch and Bound
        try {
            $result = ILPEngine::solve($c, $A, $b, $types, $intVars, $sense);
        } catch (\Throwable $e) {
            error_log('SmartModeler ILP Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $model = new ILPProject();
        $id = $model->createILP([
            'user_id'           => $this->currentUserId,
            'name'              => $d['name'] ?? 'پروژه برنامه‌ریزی صحیح (استخراج هوشمند)',
            'description'       => $d['description'] ?? '',
            'objective_coeffs'  => json_encode($c),
            'constraint_matrix' => json_encode($A),
            'rhs_values'        => json_encode($b),
            'constraints_types' => json_encode($types),
            'integer_vars'      => json_encode($intVars),
            'sense'             => $sense,
            'result_json'       => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * ایجاد پروژه دوگان + اجرای آنی سیمپلکس دوگان
     */
    private function createDualProject(array $d): array
    {
        $c = []; $A = []; $b = []; $types = [];

        if (!empty($d['variables'])) {
            foreach ($d['variables'] as $var) {
                $c[] = (float)($var['coeff'] ?? 0);
            }
        }
        if (!empty($d['constraints'])) {
            foreach ($d['constraints'] as $const) {
                $A[] = array_map('floatval', $const['coeffs'] ?? []);
                $b[] = (float)($const['capacity'] ?? 0);
                $types[] = $const['type'] ?? '<=';
            }
        }
        $sense = $d['objective'] ?? 'maximize';

        // ✅ اجرای سیمپلکس دوگان
        try {
            $result = DualSimplexEngine::solve($c, $A, $b, $types, $sense);
        } catch (\Throwable $e) {
            error_log('SmartModeler Dual Error: ' . $e->getMessage());
            $result = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $model = new DualProject();
        $id = $model->createDual([
            'user_id'           => $this->currentUserId,
            'name'              => $d['name'] ?? 'پروژه نظریه دوگان (استخراج هوشمند)',
            'description'       => $d['description'] ?? '',
            'objective_coeffs'  => json_encode($c),
            'constraint_matrix' => json_encode($A),
            'rhs_values'        => json_encode($b),
            'constraints_types' => json_encode($types),
            'sense'             => $sense,
            'result_json'       => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);
        return ['success' => true, 'project_id' => $id];
    }

    /**
     * دریافت امن نوع مسئله (با مدیریت خطا)
     */
    private function getProblemTypeSafe(string $code): ?array
    {
        try {
            $pt = (new ProblemType())->getByCode($code);
            return $pt ?: null;
        } catch (\Exception $e) {
            error_log("ProblemType lookup failed for code '{$code}': " . $e->getMessage());
            return null;
        }
    }
}