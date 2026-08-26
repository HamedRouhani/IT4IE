<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\Method;

class ProjectController extends Controller
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
        $this->view('project/index', [
            'pageTitle'   => 'پروژه‌های OR',
            'currentPage' => 'project',
            'projects'    => $this->model->getWithType($this->currentUserId),
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('project/create', [
            'pageTitle'    => 'ایجاد پروژه جدید',
            'currentPage'  => 'project',
            'problemTypes' => (new ProblemType())->getAll(),
            'methods'      => (new Method())->getWithProblemType(),
        ]);
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->redirect('controller=project&action=create');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flashError('نام پروژه الزامی است.');
            $this->redirect('controller=project&action=create');
        }

        $ptId = (int)($_POST['problem_type_id'] ?? 0);
        if ($ptId <= 0) {
            $this->flashError('نوع مسئله را انتخاب کنید.');
            $this->redirect('controller=project&action=create');
        }

        $objective = $_POST['objective'] ?? 'minimize';
        $methodId  = (int)($_POST['method_id'] ?? 0);

        $pid = $this->model->create([
            'user_id'         => $this->currentUserId,
            'name'            => $name,
            'description'     => trim($_POST['description'] ?? ''),
            'problem_type_id' => $ptId,
            'method_id'       => $methodId > 0 ? $methodId : null,
            'objective'       => in_array($objective, ['minimize','maximize']) ? $objective : 'minimize',
            'status'          => 'draft',
        ]);

        $this->logActivity('create', 'project', $pid);
        $this->flashSuccess('پروژه با موفقیت ایجاد شد.');
        $this->redirect('controller=project&action=show&id=' . (int)$pid);
    }

    public function show($id)
    {
        $this->requireAuth();
        $p = $this->model->getWithDetails((int)$id);
        if (!$p) { $this->flashError('پروژه یافت نشد.'); $this->redirect('controller=project'); }
        if (!$this->authorizeOwnership($p['user_id'])) return;

        $tab = $_GET['tab'] ?? 'info';
        if (!in_array($tab, ['info','nodes','matrix','solve','result'], true)) $tab = 'info';

        $this->view('project/view', [
            'pageTitle'    => $p['name'],
            'currentPage'  => 'project',
            'project'      => $p,
            'tab'          => $tab,
            'sources'      => $this->model->getSources((int)$id),
            'destinations' => $this->model->getDestinations((int)$id),
            'edges'        => $this->model->getEdges((int)$id),
            'allocations'  => $this->model->getAllocations((int)$id),
            'result'       => $this->model->getResult((int)$id),
            'balance'      => $this->model->getSupplyDemand((int)$id),
            'methods'      => (new Method())->getByProblemType($p['problem_type_id']),
        ]);
    }

    public function edit($id)
    {
        $this->requireAuth();
        $p = $this->model->find((int)$id);
        if (!$p) { $this->flashError('پروژه یافت نشد.'); $this->redirect('controller=project'); }
        if (!$this->authorizeOwnership($p['user_id'])) return;

        $this->view('project/edit', [
            'pageTitle'    => 'ویرایش پروژه',
            'currentPage'  => 'project',
            'project'      => $p,
            'problemTypes' => (new ProblemType())->getAll(),
            'methods'      => (new Method())->getWithProblemType(),
        ]);
    }

    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('controller=project');

        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id'])) return;

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flashError('نام الزامی است.');
            $this->redirect('controller=project&action=edit&id=' . (int)$id);
        }

        $this->model->update((int)$id, [
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
            'objective'   => $_POST['objective'] ?? 'minimize',
            'method_id'   => (int)($_POST['method_id'] ?? 0) ?: null,
        ]);

        $this->logActivity('update', 'project', (int)$id);
        $this->flashSuccess('پروژه به‌روزرسانی شد.');
        $this->redirect('controller=project&action=show&id=' . (int)$id);
    }

    public function delete($id)
    {
        $this->requireAuth();
        $p = $this->model->find((int)$id);
        if ($p && $this->authorizeOwnership($p['user_id'])) {
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_allocations WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_results WHERE project_id=?", [(int)$id]);
            $this->model->delete((int)$id);
            $this->logActivity('delete', 'project', (int)$id);
            $this->flashSuccess('پروژه حذف شد.');
        }
        $this->redirect('controller=project');
    }

    // ---- AJAX: افزودن گره ----
    public function addNode($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'Method not allowed'], 405);

        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id']))
            $this->json(['success'=>false,'error'=>'دسترسی مجاز نیست.'], 403);

        $type = $_POST['type'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $cap  = (int)($_POST['capacity'] ?? 0);

        if (!in_array($type, ['source','destination']))
            $this->json(['success'=>false,'error'=>'نوع گره نامعتبر است.']);
        if ($name === '')
            $this->json(['success'=>false,'error'=>'نام گره الزامی است.']);
        if ($cap < 0)
            $this->json(['success'=>false,'error'=>'ظرفیت نمی‌تواند منفی باشد.']);

        $existing  = $this->model->getNodes((int)$id, $type);
        $sortOrder = count($existing);

        $this->model->addNode((int)$id, $type, $name, $cap, $sortOrder);
        $this->refreshBalance((int)$id);
        $this->logActivity('add_node', 'project', (int)$id);
        $this->json(['success'=>true, 'message'=>'گره اضافه شد.']);
    }

    // ---- AJAX: حذف گره ----
    public function deleteNode($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'Method not allowed'], 405);

        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id']))
            $this->json(['success'=>false,'error'=>'دسترسی مجاز نیست.'], 403);

        $nodeId = (int)($_POST['node_id'] ?? 0);
        if ($nodeId <= 0)
            $this->json(['success'=>false,'error'=>'شناسه گره نامعتبر است.']);

        $this->model->query("DELETE FROM or_project_edges WHERE project_id=? AND (source_id=? OR destination_id=?)",
            [(int)$id, $nodeId, $nodeId]);
        $this->model->deleteNode($nodeId, (int)$id);
        $this->refreshBalance((int)$id);
        $this->logActivity('delete_node', 'project', (int)$id);
        $this->json(['success'=>true, 'message'=>'گره حذف شد.']);
    }

    // ---- AJAX: ذخیره ماتریس هزینه ----
    public function saveMatrix($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'Method not allowed'], 405);

        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id']))
            $this->json(['success'=>false,'error'=>'دسترسی مجاز نیست.'], 403);

        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;
        $matrix  = $payload['matrix'] ?? [];

        $srcs = $this->model->getSources((int)$id);
        $dsts = $this->model->getDestinations((int)$id);

        if (empty($matrix) || empty($srcs) || empty($dsts))
            $this->json(['success'=>false,'error'=>'ابتدا منابع و مقاصد را تعریف کنید.']);

        $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);

        foreach ($srcs as $i => $src) {
            foreach ($dsts as $j => $dst) {
                $cost     = $matrix[$i][$j] ?? null;
                $isProhib = ($cost === null || $cost === '') ? 1 : 0;
                $costVal  = $isProhib ? null : (float)$cost;
                $this->model->setEdge((int)$id, $src['id'], $dst['id'], $costVal, $isProhib);
            }
        }

        $this->logActivity('save_matrix', 'project', (int)$id);
        $this->json(['success'=>true, 'message'=>'ماتریس هزینه ذخیره شد.']);
    }

    // ---- AJAX: متوازن‌سازی ----
    public function balance($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'Method not allowed'], 405);

        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id']))
            $this->json(['success'=>false,'error'=>'دسترسی مجاز نیست.'], 403);

        $bal = $this->model->getSupplyDemand((int)$id);
        $s = $bal['supply']; $d = $bal['demand'];

        if ($s === $d)
            $this->json(['success'=>true, 'message'=>'مسئله از قبل متوازن است.', 'balanced'=>true]);

        $diff = abs($s - $d);
        if ($s > $d) {
            $this->model->addNode((int)$id, 'dummy', 'مقصد مجازی', $diff, 99);
            $msg = "مقصد مجازی با تقاضای {$diff} اضافه شد.";
        } else {
            $this->model->addNode((int)$id, 'dummy', 'مبدأ مجازی', $diff, 99);
            $msg = "مبدأ مجازی با عرضه {$diff} اضافه شد.";
        }

        $this->refreshBalance((int)$id);
        $this->logActivity('balance', 'project', (int)$id);
        $this->json(['success'=>true, 'message'=>$msg, 'balanced'=>true]);
    }

    private function refreshBalance($pid)
    {
        $b = $this->model->getSupplyDemand($pid);
        $this->model->updateBalance($pid,
            ($b['supply'] === $b['demand']) ? 1 : 0,
            $b['supply'], $b['demand']);
    }
}