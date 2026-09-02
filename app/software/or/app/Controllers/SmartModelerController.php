<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Helpers\SmartModeler;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;

class SmartModelerController extends Controller
{
    private $modeler;
    private $model;

    public function __construct()
    {
        parent::__construct();
        
        // بررسی ایمن وجود دیتابیس
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
     * ایجاد پروژه خودکار از تحلیل هوشمند (فقط یک بار تعریف شده است)
     */
    public function createProject()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $type = $payload['type'] ?? '';
            $modelData = $payload['model_data'] ?? [];

            $typeMap = [
                'TRANS' => ['controller' => 'transport', 'code' => 'TRANS'],
                'ASSIGN' => ['controller' => 'assignment', 'code' => 'ASSIGN'],
                'SHORTEST' => ['controller' => 'shortest', 'code' => 'SHORTEST'],
                'LP' => ['controller' => 'simplex', 'code' => 'LP'],
                'TRANSSHIP' => ['controller' => 'transship', 'code' => 'TRANSSHIP'],
            ];

            if (!isset($typeMap[$type])) {
                $this->json(['success' => false, 'error' => 'نوع مسئله نامعتبر است']);
                return;
            }

            $pt = (new ProblemType())->getByCode($typeMap[$type]['code']);
            
            // ۱. ایجاد پروژه اصلی
            $projectId = $this->model->create([
                'user_id' => $this->currentUserId,
                'name' => $modelData['name'] ?? "پروژه {$typeMap[$type]['code']} هوشمند",
                'description' => $modelData['description'] ?? '',
                'problem_type_id' => $pt['id'],
                'objective' => $modelData['objective'] ?? 'minimize',
                'status' => 'draft',
                'model_data' => json_encode($modelData, JSON_UNESCAPED_UNICODE),
            ]);

            // ۲. پیاده‌سازی خودکار گره‌ها و یال‌ها برای مدل‌های شبکه‌ای
            if ($type === 'SHORTEST' && !empty($modelData['nodes'])) {
                $nodeIds = [];
                foreach ($modelData['nodes'] as $i => $node) {
                    $nodeIds[$i] = $this->model->addNode($projectId, 'source', $node['name'], 0, $i);
                }
                if (!empty($modelData['edges'])) {
                    foreach ($modelData['edges'] as $edge) {
                        $fromIdx = $edge['from'];
                        $toIdx = $edge['to'];
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

            $this->logActivity('create_smart', $typeMap[$type]['controller'], $projectId);
            
            $this->json([
                'success' => true,
                'project_id' => $projectId,
                'redirect' => "?controller={$typeMap[$type]['controller']}&action=show&id={$projectId}",
                'message' => 'پروژه با موفقیت و با داده‌های استخراج‌شده ایجاد شد!'
            ]);

        } catch (\Exception $e) {
            error_log("SmartModeler CreateProject Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }
}