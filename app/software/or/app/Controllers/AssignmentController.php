<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Helpers\AssignmentSolver;

class AssignmentController extends Controller
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
        $projects = $this->model->getByProblemTypeCode('ASSIGN', $this->currentUserId);
        $this->view('assignment/index', [
            'pageTitle'   => 'مسئله تخصیص',
            'currentPage' => 'assignment',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('assignment/create', [
            'pageTitle'   => 'ایجاد پروژه تخصیص جدید',
            'currentPage' => 'assignment',
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
            $pt = (new ProblemType())->getByCode('ASSIGN');
            
            $pid = $this->model->create([
                'user_id'         => $this->currentUserId,
                'name'            => trim($payload['name'] ?? 'پروژه تخصیص'),
                'description'     => trim($payload['description'] ?? ''),
                'problem_type_id' => $pt['id'],
                'objective'       => $payload['objective'] ?? 'minimize',
                'status'          => 'draft',
                'model_data'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            $agentIds = []; $taskIds = [];
            foreach ($payload['agents'] as $i => $agent) {
                $agentIds[] = $this->model->addNode($pid, 'source', $agent['name'], 1, $i);
            }
            foreach ($payload['tasks'] as $j => $task) {
                $taskIds[] = $this->model->addNode($pid, 'destination', $task['name'], 1, $j);
            }

            $matrix = $payload['cost_matrix'];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                    $costVal = $isProhib ? null : (float)$cost;
                    $this->model->setEdge($pid, $agentIds[$i], $taskIds[$j], $costVal, $isProhib);
                }
            }

            $count = max(count($agentIds), count($taskIds));
            $this->model->updateBalance($pid, 1, $count, $count);

            $this->logActivity('create', 'assignment', $pid);
            $this->json(['success' => true, 'project_id' => $pid, 'message' => 'پروژه تخصیص ذخیره شد.']);

        } catch (\Exception $e) {
            error_log("Assignment Store Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'ASSIGN') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=assignment');
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        if (!in_array($tab, ['info', 'nodes', 'matrix', 'solve', 'result'], true)) $tab = 'info';

        $this->view('assignment/show', [
            'pageTitle'    => $project['name'],
            'currentPage'  => 'assignment',
            'project'      => $project,
            'tab'          => $tab,
            'sources'      => $this->model->getSources((int)$id),
            'destinations' => $this->model->getDestinations((int)$id),
            'edges'        => $this->model->getEdges((int)$id),
            'solution'     => json_decode($project['solution_data'] ?? 'null', true),
        ]);
    }

    public function edit($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'ASSIGN') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=assignment');
            return;
        }

        // دریافت ماتریس هزینه‌ها و وضعیت ممنوعیت‌ها به صورت ساختاریافته
        $cd = $this->model->getCostMatrix((int)$id);

        $this->view('assignment/edit', [
            'pageTitle'    => 'ویرایش پروژه تخصیص: ' . $project['name'],
            'currentPage'  => 'assignment',
            'project'      => $project,
            'sources'      => $cd['sources'],
            'destinations' => $cd['dests'],
            'costMatrix'   => $cd['matrix'],     
            'prohibited'   => $cd['prohibited'], 
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
                'objective'   => $payload['objective'] ?? $project['objective'],
            ]);

            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);

            $agentIds = []; $taskIds = [];
            foreach ($payload['agents'] as $i => $agent) {
                $agentIds[] = $this->model->addNode((int)$id, 'source', $agent['name'], 1, $i);
            }
            foreach ($payload['tasks'] as $j => $task) {
                $taskIds[] = $this->model->addNode((int)$id, 'destination', $task['name'], 1, $j);
            }

            $matrix = $payload['cost_matrix'] ?? [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                    $costVal = $isProhib ? null : (float)$cost;
                    $this->model->setEdge((int)$id, $agentIds[$i], $taskIds[$j], $costVal, $isProhib);
                }
            }

            $count = max(count($agentIds), count($taskIds));
            $this->model->updateBalance((int)$id, 1, $count, $count);

            if ($project['status'] === 'solved') {
                $this->model->update((int)$id, ['status' => 'draft']);
                $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
                $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            }

            $this->logActivity('update', 'assignment', (int)$id);
            $this->json(['success' => true, 'message' => 'پروژه با موفقیت به‌روزرسانی شد.']);

        } catch (\Exception $e) {
            error_log("Assignment Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
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
            $cd = $this->model->getCostMatrix((int)$id);
            $srcs = $cd['sources'];
            $dsts = $cd['dests'];
            $matrix = $cd['matrix'];

            if (empty($srcs) || empty($dsts)) {
                $this->json(['success' => false, 'error' => 'ابتدا عوامل و وظایف را تعریف کنید.']);
                return;
            }

            if (count($srcs) !== count($dsts)) {
                $this->json(['success' => false, 'error' => 'ماتریس باید مربع باشد. تعداد عوامل: ' . count($srcs) . '، تعداد وظایف: ' . count($dsts)]);
                return;
            }

            $isMin = ($project['objective'] ?? 'minimize') === 'minimize';
            $result = AssignmentSolver::solve($matrix, $isMin);

            if (($result['status'] ?? 'error') === 'success') {
                $dbAllocs = [];
                foreach ($result['assignments'] as $a) {
                    $i = $a['agent_index']; $j = $a['task_index'];
                    $uc = ($matrix[$i][$j] !== null) ? (float)$matrix[$i][$j] : 0;
                    $dbAllocs[] = [
                        'source_id'        => $srcs[$i]['id'],
                        'destination_id'   => $dsts[$j]['id'],
                        'allocated_amount' => 1,
                        'unit_cost'        => $uc,
                        'total_cost'       => $uc,
                        'is_basic_cell'    => 1,
                    ];
                }

                $this->model->saveAllocations((int)$id, $dbAllocs);
                $this->model->saveResult((int)$id, $result['total_cost'], 0, 'optimal');
                $this->model->update((int)$id, [
                    'status'        => 'solved',
                    'solution_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'optimal_value' => $result['total_cost'],
                ]);
                
                $this->logActivity('solve_assignment', 'project', (int)$id);
                $this->json(['success' => true, 'result' => $result, 'message' => 'مسئله با موفقیت حل شد.']);
            } else {
                $this->model->update((int)$id, ['status' => 'infeasible']);
                $this->json(['success' => false, 'error' => $result['message'] ?? 'خطا در حل مسئله']);
            }

        } catch (\Exception $e) {
            error_log("Assignment Solve Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $this->requireAuth();
        $p = $this->model->find((int)$id);
        if ($p && $p['user_id'] == $this->currentUserId && ($p['problem_type_code'] ?? '') === 'ASSIGN') {
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            $this->model->delete((int)$id);
            $this->logActivity('delete', 'assignment', (int)$id);
            $this->flashSuccess('پروژه تخصیص حذف شد.');
        }
        $this->redirect('controller=assignment');
    }
}