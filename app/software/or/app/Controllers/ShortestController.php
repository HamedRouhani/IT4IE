<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;

class ShortestController extends Controller
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
        $projects = $this->model->getByProblemTypeCode('SHORTEST', $this->currentUserId);
        $this->view('shortest/index', [
            'pageTitle'   => 'کوتاه‌ترین مسیر (Shortest Path)',
            'currentPage' => 'shortest',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('shortest/create', [
            'pageTitle'   => 'ایجاد پروژه کوتاه‌ترین مسیر',
            'currentPage' => 'shortest',
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
            $pt = (new ProblemType())->getByCode('SHORTEST');

            $pid = $this->model->create([
                'user_id'         => $this->currentUserId,
                'name'            => trim($payload['name'] ?? 'پروژه کوتاه‌ترین مسیر'),
                'description'     => trim($payload['description'] ?? ''),
                'problem_type_id' => $pt['id'],
                'objective'       => 'minimize',
                'status'          => 'draft',
                'model_data'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            // ذخیره گره‌ها (همه به عنوان source با ظرفیت 0)
            $nodeIds = [];
            foreach ($payload['nodes'] as $i => $node) {
                $nodeIds[$i] = $this->model->addNode($pid, 'source', $node['name'], 0, $i);
            }

            // ذخیره یال‌ها (edges)
            foreach ($payload['edges'] as $edge) {
                $fromIdx = $edge['from'];
                $toIdx = $edge['to'];
                $weight = (float)$edge['weight'];
                if (isset($nodeIds[$fromIdx]) && isset($nodeIds[$toIdx])) {
                    $this->model->setEdge($pid, $nodeIds[$fromIdx], $nodeIds[$toIdx], $weight, 0);
                }
            }

            $this->logActivity('create', 'shortest', $pid);
            $this->json(['success' => true, 'project_id' => $pid, 'message' => 'پروژه ذخیره شد.']);

        } catch (\Exception $e) {
            error_log("Shortest Store Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'SHORTEST') {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=shortest');
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        if (!in_array($tab, ['info', 'graph', 'solve', 'result'], true)) $tab = 'info';

        $solution = json_decode($project['solution_data'] ?? 'null', true);

        $this->view('shortest/show', [
            'pageTitle'    => $project['name'],
            'currentPage'  => 'shortest',
            'project'      => $project,
            'tab'          => $tab,
            'nodes'        => $this->model->getNodes((int)$id),
            'edges'        => $this->model->getEdges((int)$id),
            'solution'     => $solution,  // ✅ ارسال solution به view
        ]);
    }

    public function edit($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'SHORTEST') {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=shortest');
            return;
        }

        // دریافت گره‌ها و یال‌ها
        $nodes = $this->model->getNodes((int)$id);
        $edges = $this->model->getEdges((int)$id);
        
        // دیباگ: بررسی کنید آیا داده‌ها لود می‌شوند؟
        error_log("Nodes count: " . count($nodes));
        error_log("Edges count: " . count($edges));

        $this->view('shortest/edit', [
            'pageTitle'    => 'ویرایش: ' . $project['name'],
            'currentPage'  => 'shortest',
            'project'      => $project,
            'nodes'        => $nodes,  // ← مطمئن شوید این آرایه خالی نیست
            'edges'        => $edges,  // ← مطمئن شوید این آرایه خالی نیست
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

            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);

            $nodeIds = [];
            foreach ($payload['nodes'] as $i => $node) {
                $nodeIds[$i] = $this->model->addNode((int)$id, 'source', $node['name'], 0, $i);
            }

            foreach ($payload['edges'] as $edge) {
                $fromIdx = $edge['from'];
                $toIdx = $edge['to'];
                $weight = (float)$edge['weight'];
                if (isset($nodeIds[$fromIdx]) && isset($nodeIds[$toIdx])) {
                    $this->model->setEdge((int)$id, $nodeIds[$fromIdx], $nodeIds[$toIdx], $weight, 0);
                }
            }

            $this->logActivity('update', 'shortest', (int)$id);
            $this->json(['success' => true, 'message' => 'پروژه به‌روزرسانی شد.']);

        } catch (\Exception $e) {
            error_log("Shortest Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    // ✅ حذف اختصاصی (اصلاح‌شده برای کوتاه‌ترین مسیر)
    public function delete($id)
    {
        $this->requireAuth();
        
        // ✅ تغییر کلیدی: استفاده از getWithDetails به جای find برای دریافت problem_type_code
        $p = $this->model->getWithDetails((int)$id);
        
        if ($p && $p['user_id'] == $this->currentUserId && ($p['problem_type_code'] ?? '') === 'SHORTEST') {
            // ۱. حذف وابستگی‌ها
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            
            // ۲. حذف خود پروژه
            $this->model->delete((int)$id);
            
            $this->logActivity('delete', 'shortest', (int)$id);
            
            // نمایش پیام موفقیت
            if (method_exists($this, 'flashSuccess')) {
                $this->flashSuccess('پروژه کوتاه‌ترین مسیر با موفقیت حذف شد.');
            } else {
                $this->flash('success', 'پروژه کوتاه‌ترین مسیر با موفقیت حذف شد.');
            }
        } else {
            // نمایش پیام خطا در صورت عدم تطابق
            if (method_exists($this, 'flashError')) {
                $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            } else {
                $this->flash('error', 'پروژه یافت نشد یا دسترسی مجاز نیست.');
            }
        }
        
        $this->redirect('controller=shortest');
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
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $algorithm = $payload['algorithm'] ?? 'dijkstra';
            $sourceId = $payload['source_id'] ?? null;

            $nodes = $this->model->getNodes((int)$id);
            $edges = $this->model->getEdges((int)$id);

            if (empty($nodes) || empty($edges)) {
                $this->json(['success' => false, 'error' => 'ابتدا گره‌ها و یال‌ها را تعریف کنید.']);
                return;
            }

            if (count($nodes) < 2) {
                $this->json(['success' => false, 'error' => 'حداقل به ۲ گره نیاز است.']);
                return;
            }

            // انتخاب الگوریتم
            switch ($algorithm) {
                case 'dijkstra':
                    if (!$sourceId) {
                        $sourceId = $nodes[0]['id']; // استفاده از اولین گره به عنوان مبدأ
                    }
                    $result = \App\Software\Or\Helpers\ShortestPathSolver::dijkstra($nodes, $edges, $sourceId);
                    break;
                    
                case 'floyd':
                    $result = \App\Software\Or\Helpers\ShortestPathSolver::floydWarshall($nodes, $edges);
                    break;
                    
                default:
                    $this->json(['success' => false, 'error' => 'الگوریتم نامعتبر است.']);
                    return;
            }

            if (($result['status'] ?? 'error') === 'success') {
                $this->model->update((int)$id, [
                    'status' => 'solved',
                    'solution_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'optimal_value' => null, // برای کوتاه‌ترین مسیر چندگانه است
                ]);
                
                $this->logActivity('solve_shortest', 'project', (int)$id);
                $this->json(['success' => true, 'result' => $result, 'message' => 'مسئله با موفقیت حل شد.']);
            } else {
                $this->model->update((int)$id, ['status' => 'infeasible']);
                $this->json(['success' => false, 'error' => $result['message'] ?? 'خطا در حل مسئله']);
            }

        } catch (\Exception $e) {
            error_log("Shortest Solve Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }
}