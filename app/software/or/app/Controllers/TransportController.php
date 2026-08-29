<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Helpers\TransportationSolver;

class TransportController extends Controller
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
        $projects = $this->model->getByProblemTypeCode('TRANS', $this->currentUserId);
        $this->view('transport/index', [
            'pageTitle'   => 'مسئله حمل و نقل',
            'currentPage' => 'transport',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('transport/create', [
            'pageTitle'   => 'ایجاد پروژه حمل و نقل جدید',
            'currentPage' => 'transport',
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
            $pt = (new ProblemType())->getByCode('TRANS');
            
            $pid = $this->model->create([
                'user_id'         => $this->currentUserId,
                'name'            => trim($payload['name'] ?? 'پروژه حمل و نقل'),
                'description'     => trim($payload['description'] ?? ''),
                'problem_type_id' => $pt['id'],
                'objective'       => 'minimize',
                'status'          => 'draft',
                'model_data'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            $totalSupply = 0; $totalDemand = 0;
            $sourceIds = []; $destIds = [];

            foreach ($payload['sources'] as $i => $src) {
                $nodeId = $this->model->addNode($pid, 'source', $src['name'], (int)$src['capacity'], $i);
                $sourceIds[] = $nodeId;
                $totalSupply += (int)$src['capacity'];
            }

            foreach ($payload['destinations'] as $j => $dst) {
                $nodeId = $this->model->addNode($pid, 'destination', $dst['name'], (int)$dst['capacity'], $j);
                $destIds[] = $nodeId;
                $totalDemand += (int)$dst['capacity'];
            }

            $matrix = $payload['cost_matrix'] ?? [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                    $costVal = $isProhib ? null : (float)$cost;
                    $this->model->setEdge($pid, $sourceIds[$i], $destIds[$j], $costVal, $isProhib);
                }
            }

            $isBalanced = ($totalSupply === $totalDemand) ? 1 : 0;
            $this->model->updateBalance($pid, $isBalanced, $totalSupply, $totalDemand);

            $this->logActivity('create', 'transport', $pid);
            $this->json(['success' => true, 'project_id' => $pid, 'message' => 'پروژه با موفقیت ذخیره شد.']);

        } catch (\Exception $e) {
            error_log("Transport Store Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    // ✅ نمایش اختصاصی پروژه حمل و نقل
    public function show($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'TRANS') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=transport');
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        if (!in_array($tab, ['info', 'nodes', 'matrix', 'solve', 'result'], true)) $tab = 'info';

        $this->view('transport/show', [
            'pageTitle'    => $project['name'],
            'currentPage'  => 'transport',
            'project'      => $project,
            'tab'          => $tab,
            'sources'      => $this->model->getSources((int)$id),
            'destinations' => $this->model->getDestinations((int)$id),
            'edges'        => $this->model->getEdges((int)$id),
            'balance'      => $this->model->getSupplyDemand((int)$id),
            'solution'     => json_decode($project['solution_data'] ?? 'null', true),
        ]);
    }

    // ✅ حل اختصاصی مسئله حمل و نقل
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
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $methodCode = $payload['method_code'] ?? 'VAM';

            $cd = $this->model->getCostMatrix((int)$id);
            $srcs = $cd['sources'];
            $dsts = $cd['dests'];
            $matrix = $cd['matrix'];

            if (empty($srcs) || empty($dsts)) {
                $this->json(['success' => false, 'error' => 'ابتدا مبادی و مقاصد را تعریف کنید.']);
                return;
            }

            $supply = array_map(fn($s) => (int)$s['capacity'], $srcs);
            $demand = array_map(fn($d) => (int)$d['capacity'], $dsts);

            // فراخوانی هسته حل‌کننده
            $result = TransportationSolver::solve($matrix, $supply, $demand, $methodCode, true);

            if (($result['status'] ?? 'error') === 'success') {
                // ذخیره تخصیص‌ها در دیتابیس
                $alloc = $result['allocation'];
                $dbAllocs = [];
                $m = count($srcs); $n = count($dsts);

                for ($i = 0; $i < $m; $i++) {
                    for ($j = 0; $j < $n; $j++) {
                        if ($alloc[$i][$j] > 0) {
                            $uc = ($matrix[$i][$j] !== null) ? (float)$matrix[$i][$j] : 0;
                            $dbAllocs[] = [
                                'source_id'        => $srcs[$i]['id'],
                                'destination_id'   => $dsts[$j]['id'],
                                'allocated_amount' => $alloc[$i][$j],
                                'unit_cost'        => $uc,
                                'total_cost'       => $alloc[$i][$j] * $uc,
                                'is_basic_cell'    => 1,
                            ];
                        }
                    }
                }

                $this->model->saveAllocations((int)$id, $dbAllocs);
                $this->model->saveResult((int)$id, $result['optimal_cost'], $result['iterations'], 'optimal');
                $this->model->update((int)$id, [
                    'status' => 'solved',
                    'solution_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'optimal_value' => $result['optimal_cost'] ?? 0,
                ]);

                $this->logActivity('solve_transport', 'project', (int)$id);
                $this->json(['success' => true, 'result' => $result, 'message' => 'مسئله با موفقیت حل شد.']);
            } else {
                $this->model->update((int)$id, ['status' => 'infeasible']);
                $this->json(['success' => false, 'error' => $result['message'] ?? 'خطا در حل مسئله']);
            }

        } catch (\Exception $e) {
            error_log("Transport Solve Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    // ✅ حذف اختصاصی
    public function delete($id)
    {
        $this->requireAuth();
        $p = $this->model->find((int)$id);
        if ($p && $p['user_id'] == $this->currentUserId && ($p['problem_type_code'] ?? '') === 'TRANS') {
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            $this->model->delete((int)$id);
            $this->logActivity('delete', 'transport', (int)$id);
            $this->flashSuccess('پروژه حمل و نقل حذف شد.');
        }
        $this->redirect('controller=transport');
    }

    // ✅ متد نمایش فرم ویرایش
    public function edit($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'TRANS') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=transport');
            return;
        }

        $this->view('transport/edit', [
            'pageTitle'    => 'ویرایش پروژه حمل و نقل: ' . $project['name'],
            'currentPage'  => 'transport',
            'project'      => $project,
            'sources'      => $this->model->getSources((int)$id),
            'destinations' => $this->model->getDestinations((int)$id),
            'edges'        => $this->model->getEdges((int)$id),
        ]);
    }

    // ✅ متد ذخیره تغییرات ویرایش
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

            // ۱. به‌روزرسانی اطلاعات پایه
            $this->model->update((int)$id, [
                'name'        => trim($payload['name'] ?? $project['name']),
                'description' => trim($payload['description'] ?? ''),
            ]);

            // ۲. پاک کردن داده‌های قدیمی گره‌ها و یال‌ها
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);

            // ۳. درج داده‌های جدید (مشابه فرآیند store)
            $totalSupply = 0; $totalDemand = 0;
            $sourceIds = []; $destIds = [];

            foreach ($payload['sources'] as $i => $src) {
                $nodeId = $this->model->addNode((int)$id, 'source', $src['name'], (int)$src['capacity'], $i);
                $sourceIds[] = $nodeId;
                $totalSupply += (int)$src['capacity'];
            }

            foreach ($payload['destinations'] as $j => $dst) {
                $nodeId = $this->model->addNode((int)$id, 'destination', $dst['name'], (int)$dst['capacity'], $j);
                $destIds[] = $nodeId;
                $totalDemand += (int)$dst['capacity'];
            }

            $matrix = $payload['cost_matrix'] ?? [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $cost) {
                    $isProhib = ($cost === '' || $cost === null) ? 1 : 0;
                    $costVal = $isProhib ? null : (float)$cost;
                    $this->model->setEdge((int)$id, $sourceIds[$i], $destIds[$j], $costVal, $isProhib);
                }
            }

            $isBalanced = ($totalSupply === $totalDemand) ? 1 : 0;
            $this->model->updateBalance((int)$id, $isBalanced, $totalSupply, $totalDemand);

            // اگر پروژه قبلاً حل شده بود، با ویرایش باید به حالت پیش‌نویس برگردد
            if ($project['status'] === 'solved') {
                $this->model->update((int)$id, ['status' => 'draft']);
                $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
                $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            }

            $this->logActivity('update', 'transport', (int)$id);
            $this->json(['success' => true, 'message' => 'پروژه با موفقیت به‌روزرسانی شد.']);

        } catch (\Exception $e) {
            error_log("Transport Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }
}