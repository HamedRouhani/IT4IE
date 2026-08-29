<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Helpers\SimplexSolver;

class SimplexController extends Controller
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
        $projects = $this->model->getByProblemTypeCode('LP', $this->currentUserId);
        $this->view('simplex/index', [
            'pageTitle'   => 'برنامه‌ریزی خطی (Simplex)',
            'currentPage' => 'simplex',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('simplex/create', [
            'pageTitle'   => 'ایجاد مدل برنامه‌ریزی خطی',
            'currentPage' => 'simplex',
        ]);
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $pt = (new ProblemType())->getByCode('LP');

        $modelData = [
            'c'     => $payload['c'] ?? [],
            'A'     => $payload['A'] ?? [],
            'b'     => $payload['b'] ?? [],
            'types' => $payload['types'] ?? [],
        ];

        $pid = $this->model->create([
            'user_id'         => $this->currentUserId,
            'name'            => trim($payload['name'] ?? 'مدل برنامه‌ریزی خطی'),
            'description'     => trim($payload['description'] ?? ''),
            'problem_type_id' => $pt['id'],
            'objective'       => $payload['objective'] ?? 'maximize',
            'status'          => 'draft',
            'model_data'      => json_encode($modelData, JSON_UNESCAPED_UNICODE),
        ]);

        $this->logActivity('create', 'simplex', $pid);
        $this->json(['success' => true, 'project_id' => $pid, 'message' => 'مدل با موفقیت ذخیره شد.']);
    }

    public function show($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'LP') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=simplex');
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        if (!in_array($tab, ['info', 'nodes', 'matrix', 'solve', 'result'], true)) {
            $tab = 'info';
        }

        $this->view('simplex/show', [
            'pageTitle'   => $project['name'],
            'currentPage' => 'simplex',
            'project'     => $project,
            'tab'         => $tab,
            'modelData'   => json_decode($project['model_data'] ?? '{}', true),
            'solution'    => json_decode($project['solution_data'] ?? 'null', true),
        ]);
    }

    public function edit($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId || ($project['problem_type_code'] ?? '') !== 'LP') {
            $this->flashError('پروژه یافت نشد یا دسترسی مجاز نیست.');
            $this->redirect('controller=simplex');
            return;
        }

        $this->view('simplex/edit', [
            'pageTitle'   => 'ویرایش مدل برنامه‌ریزی خطی',
            'currentPage' => 'simplex',
            'project'     => $project,
            'modelData'   => json_decode($project['model_data'] ?? '{}', true),
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
            $shouldSolve = (bool)($payload['solve_after_update'] ?? false);
            
            $modelData = [
                'c'     => $payload['c'] ?? [],
                'A'     => $payload['A'] ?? [],
                'b'     => $payload['b'] ?? [],
                'types' => $payload['types'] ?? [],
            ];

            $this->model->update((int)$id, [
                'name'          => trim($payload['name'] ?? $project['name']),
                'description'   => trim($payload['description'] ?? ''),
                'objective'     => $payload['objective'] ?? $project['objective'],
                'model_data'    => json_encode($modelData, JSON_UNESCAPED_UNICODE),
                'status'        => 'draft',
                'solution_data' => null,
                'optimal_value' => null,
            ]);

            // ✅ اگر کاربر درخواست حل بعد از ذخیره را داده باشد
            if ($shouldSolve) {
                $solveResult = SimplexSolver::solve(
                    $modelData['c'],
                    $modelData['A'],
                    $modelData['b'],
                    $modelData['types'],
                    $payload['objective'] ?? $project['objective']
                );

                if (($solveResult['status'] ?? '') === 'optimal') {
                    $this->model->update((int)$id, [
                        'status'        => 'solved',
                        'solution_data' => json_encode($solveResult, JSON_UNESCAPED_UNICODE),
                        'optimal_value' => $solveResult['optimal_value'] ?? 0,
                    ]);
                    
                    $this->json([
                        'success' => true, 
                        'solved'  => true,
                        'result'  => $solveResult,
                        'message' => 'تغییرات ذخیره و مسئله با موفقیت حل شد.',
                        'redirect' => or_url("controller=simplex&action=show&id={$id}&tab=result")
                    ]);
                    return;
                } else {
                    $this->model->update((int)$id, ['status' => $solveResult['status'] ?? 'infeasible']);
                    $this->json([
                        'success' => true,
                        'solved'  => false,
                        'error'   => $solveResult['message'] ?? 'مسئله قابل حل نیست.',
                        'redirect' => or_url("controller=simplex&action=show&id={$id}")
                    ]);
                    return;
                }
            }

            $this->logActivity('update', 'simplex', (int)$id);
            $this->json([
                'success'  => true, 
                'solved'   => false,
                'message'  => 'تغییرات با موفقیت ذخیره شد.',
                'redirect' => or_url("controller=simplex&action=show&id={$id}")
            ]);

        } catch (\Exception $e) {
            error_log("Simplex Update Error: " . $e->getMessage());
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
            $modelData = json_decode($project['model_data'] ?? '{}', true);
            if (empty($modelData['c']) || empty($modelData['A'])) {
                $this->json(['success' => false, 'error' => 'داده‌های مدل ناقص است.'], 400);
                return;
            }

            $result = SimplexSolver::solve(
                $modelData['c'],
                $modelData['A'],
                $modelData['b'],
                $modelData['types'],
                $project['objective'] ?? 'maximize'
            );

            if (($result['status'] ?? '') === 'optimal') {
                $this->model->update((int)$id, [
                    'status'        => 'solved',
                    'solution_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'optimal_value' => $result['optimal_value'] ?? 0,
                ]);
                $this->logActivity('solve', 'simplex', (int)$id);
                $this->json(['success' => true, 'result' => $result, 'message' => 'مسئله با موفقیت حل شد.']);
            } else {
                $this->model->update((int)$id, ['status' => $result['status'] ?? 'infeasible']);
                $this->json(['success' => false, 'error' => $result['message'] ?? 'مسئله قابل حل نیست.']);
            }

        } catch (\Exception $e) {
            error_log("Simplex Solve Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'خطای داخلی: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ این متد برای سازگاری با لینک‌های قدیمی result باقی می‌ماند
     * اما فقط به show با تب result ریدایرکت می‌کند
     */
    public function result($id)
    {
        $this->requireAuth();
        $this->redirect('controller=simplex&action=show&id=' . (int)$id . '&tab=result');
    }

    public function delete($id)
    {
        $this->requireAuth();
        $p = $this->model->getWithDetails((int)$id);
        
        if ($p && $p['user_id'] == $this->currentUserId && ($p['problem_type_code'] ?? '') === 'LP') {
            $this->model->query("DELETE FROM or_project_nodes WHERE project_id=?", [(int)$id]);
            $this->model->query("DELETE FROM or_project_edges WHERE project_id=?", [(int)$id]);
            $this->model->delete((int)$id);
            $this->logActivity('delete', 'simplex', (int)$id);
            $this->flashSuccess('مدل برنامه‌ریزی خطی حذف شد.');
        } else {
            $this->flashError('دسترسی مجاز نیست یا مدل یافت نشد.');
        }
        
        $this->redirect('controller=simplex');
    }
}