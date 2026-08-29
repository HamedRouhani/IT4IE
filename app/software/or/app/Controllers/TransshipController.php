<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;

class TransshipController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->requireAuth();
        $projects = $this->model->getByProblemTypeCode('TRANSSHIP', $this->currentUserId);
        $this->view('transship/index', [
            'pageTitle'   => 'مسئله ترانشیپمنت (Transshipment)',
            'currentPage' => 'transship',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('transship/create', [
            'pageTitle'   => 'ایجاد پروژه ترانشیپمنت',
            'currentPage' => 'transship',
        ]);
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $pt = (new ProblemType())->getByCode('TRANSSHIP');

            $pid = $this->model->create([
                'user_id'         => $this->currentUserId,
                'name'            => trim($payload['name'] ?? 'پروژه ترانشیپمنت'),
                'description'     => trim($payload['description'] ?? ''),
                'problem_type_id' => $pt['id'],
                'objective'       => 'minimize',
                'status'          => 'draft',
                'model_data'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            // ذخیره گره‌ها (مبدأ، مقصد، میانی)
            $nodeIds = [];
            $totalSupply = 0; $totalDemand = 0;
            
            foreach ($payload['sources'] as $i => $src) {
                $nodeIds['source'][$i] = $this->model->addNode($pid, 'source', $src['name'], (int)$src['capacity'], $i);
                $totalSupply += (int)$src['capacity'];
            }
            foreach ($payload['destinations'] as $j => $dst) {
                $nodeIds['destination'][$j] = $this->model->addNode($pid, 'destination', $dst['name'], (int)$dst['capacity'], count($payload['sources']) + $j);
                $totalDemand += (int)$dst['capacity'];
            }
            foreach ($payload['intermediates'] as $k => $mid) {
                $nodeIds['intermediate'][$k] = $this->model->addNode($pid, 'transshipment', $mid['name'], (int)$mid['capacity'], count($payload['sources']) + count($payload['destinations']) + $k);
            }

            // ذخیره ماتریس هزینه (یال‌ها)
            $allNodeIds = array_merge($nodeIds['source'] ?? [], $nodeIds['destination'] ?? [], $nodeIds['intermediate'] ?? []);
            $matrix = $payload['cost_matrix'] ?? [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    if (isset($allNodeIds[$i]) && isset($allNodeIds[$j])) {
                        $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                        $costVal = $isProhib ? null : (float)$cost;
                        $this->model->setEdge($pid, $allNodeIds[$i], $allNodeIds[$j], $costVal, $isProhib);
                    }
                }
            }

            $this->model->updateBalance($pid, ($totalSupply === $totalDemand) ? 1 : 0, $totalSupply, $totalDemand);
            $this->logActivity('create', 'transship', $pid);
            $this->json(['success' => true, 'project_id' => $pid, 'message' => 'پروژه ذخیره شد.']);

        } catch (\Exception $e) {
            error_log("Transship Store Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'TRANSSHIP') {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=transship');
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        
        // ✅ اصلاح مهم: اضافه کردن 'result' به لیست تب‌های مجاز
        if (!in_array($tab, ['info', 'nodes', 'matrix', 'solve', 'result'], true)) {
            $tab = 'info';
        }

        $this->view('transship/show', [
            'pageTitle'    => $project['name'],
            'currentPage'  => 'transship',
            'project'      => $project,
            'tab'          => $tab,
            'sources'      => $this->model->getNodes((int)$id, 'source'),
            'destinations' => $this->model->getNodes((int)$id, 'destination'),
            'intermediates'=> $this->model->getNodes((int)$id, 'transshipment'),
            'edges'        => $this->model->getEdges((int)$id),
        ]);
    }

    public function edit($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'TRANSSHIP') {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=transship');
            return;
        }

        $this->view('transship/edit', [
            'pageTitle'    => 'ویرایش پروژه ترانشیپمنت: ' . $project['name'],
            'currentPage'  => 'transship',
            'project'      => $project,
            'sources'      => $this->model->getNodes((int)$id, 'source'),
            'destinations' => $this->model->getNodes((int)$id, 'destination'),
            'intermediates'=> $this->model->getNodes((int)$id, 'transshipment'),
            'edges'        => $this->model->getEdges((int)$id),
        ]);
    }

    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        $project = $this->model->getWithDetails((int)$id);
        if (!$project || $project['user_id'] != $this->currentUserId) {
            $this->json(['success' => false, 'error' => 'دسترسی مجاز نیست'], 403);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $this->model->update((int)$id, [
                'name'        => trim($payload['name'] ?? $project['name']),
                'description' => trim($payload['description'] ?? ''),
            ]);

            // پاک کردن داده‌های قدیمی
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);

            // ذخیره مجدد گره‌ها و یال‌ها (مشابه store)
            $nodeIds = [];
            $totalSupply = 0; $totalDemand = 0;
            
            foreach ($payload['sources'] as $i => $src) {
                $nodeIds['source'][$i] = $this->model->addNode((int)$id, 'source', $src['name'], (int)$src['capacity'], $i);
                $totalSupply += (int)$src['capacity'];
            }
            foreach ($payload['destinations'] as $j => $dst) {
                $nodeIds['destination'][$j] = $this->model->addNode((int)$id, 'destination', $dst['name'], (int)$dst['capacity'], count($payload['sources']) + $j);
                $totalDemand += (int)$dst['capacity'];
            }
            foreach ($payload['intermediates'] as $k => $mid) {
                $nodeIds['intermediate'][$k] = $this->model->addNode((int)$id, 'transshipment', $mid['name'], (int)$mid['capacity'], count($payload['sources']) + count($payload['destinations']) + $k);
            }

            $allNodeIds = array_merge($nodeIds['source'] ?? [], $nodeIds['destination'] ?? [], $nodeIds['intermediate'] ?? []);
            $matrix = $payload['cost_matrix'] ?? [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    if (isset($allNodeIds[$i]) && isset($allNodeIds[$j])) {
                        $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                        $costVal = $isProhib ? null : (float)$cost;
                        $this->model->setEdge((int)$id, $allNodeIds[$i], $allNodeIds[$j], $costVal, $isProhib);
                    }
                }
            }

            $this->model->updateBalance((int)$id, ($totalSupply === $totalDemand) ? 1 : 0, $totalSupply, $totalDemand);
            $this->logActivity('update', 'transship', (int)$id);
            $this->json(['success' => true, 'message' => 'پروژه به‌روزرسانی شد.']);

        } catch (\Exception $e) {
            error_log("Transship Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $this->requireAuth();
        
        $project = $this->model->getWithDetails((int)$id);
        
        if ($project && $project['user_id'] == $this->currentUserId && ($project['problem_type_code'] ?? '') === 'TRANSSHIP') {
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            $this->model->delete((int)$id);
            
            $this->logActivity('delete', 'transship', (int)$id);
            $this->flashSuccess('پروژه ترانشیپمنت با موفقیت حذف شد.');
        } else {
            $this->flashError('دسترسی مجاز نیست یا پروژه یافت نشد.');
        }
        
        $this->redirect('controller=transship');
    }

    public function solve($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'فقط POST مجاز است.'], 405);
            return;
        }

        $project = $this->model->getWithDetails((int)$id);
        if (!$project || $project['user_id'] != $this->currentUserId) {
            $this->json(['success' => false, 'error' => 'دسترسی مجاز نیست.'], 403);
            return;
        }

        try {
            $nodes = $this->model->getNodes((int)$id);
            $edges = $this->model->getEdges((int)$id);

            if (empty($nodes) || empty($edges)) {
                $this->json(['success' => false, 'error' => 'ابتدا گره‌ها و یال‌ها را تعریف کنید.']);
                return;
            }

            $result = \App\Software\Or\Helpers\TransshipmentSolver::solve($nodes, $edges);

            if (($result['status'] ?? 'error') === 'success') {
                $this->model->update((int)$id, [
                    'status' => 'solved',
                    'solution_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'optimal_value' => $result['total_cost'] ?? 0,
                ]);
                
                $this->logActivity('solve_transship', 'project', (int)$id);
                $this->json(['success' => true, 'result' => $result, 'message' => 'مسئله با موفقیت حل شد.']);
            } else {
                $this->model->update((int)$id, ['status' => 'infeasible']);
                $this->json(['success' => false, 'error' => $result['message'] ?? 'خطا در حل مسئله']);
            }

        } catch (\Exception $e) {
            error_log("Transship Solve Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }
}