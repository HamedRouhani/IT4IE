<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
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
        // نمایش فقط پروژه‌های نوع LP (برنامه‌ریزی خطی)
        $projects = $this->model->getByProblemTypeCode('LP', $this->currentUserId);
        
        $this->view('simplex/index', [
            'pageTitle' => 'برنامه‌ریزی خطی (Simplex)',
            'currentPage' => 'simplex',
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $this->view('simplex/create', [
            'pageTitle' => 'ایجاد مدل برنامه‌ریزی خطی',
            'currentPage' => 'simplex',
        ]);
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر است'], 405);
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($payload['name'] ?? 'مدل LP جدید');
        $objType = in_array($payload['obj_type'] ?? '', ['maximize', 'minimize']) ? $payload['obj_type'] : 'maximize';
        
        // بررسی وجود ستون model_data در دیتابیس (در صورت عدم وجود، خطا می‌دهد)
        $pid = $this->model->create([
            'user_id'         => $this->currentUserId,
            'name'            => $name,
            'description'     => trim($payload['description'] ?? ''),
            'problem_type_id' => 5, // فرض: ID=5 مربوط به LP است (باید با دیتابیس چک شود)
            'objective'       => $objType,
            'status'          => 'draft',
            'model_data'      => json_encode([
                'c' => $payload['c'], 
                'A' => $payload['A'], 
                'b' => $payload['b'], 
                'types' => $payload['types']
            ]),
        ]);

        $this->logActivity('create', 'simplex', $pid);
        $this->json(['success' => true, 'project_id' => $pid, 'message' => 'مدل با موفقیت ذخیره شد.']);
    }

    public function solve($id)
    {
        $this->requireAuth();
        $p = $this->model->getWithDetails((int)$id);
        
        if (!$p || $p['user_id'] != $this->currentUserId) {
            $this->json(['success' => false, 'error' => 'دسترسی مجاز نیست یا پروژه یافت نشد.'], 403);
            return;
        }

        $metadata = json_decode($p['model_data'] ?? '[]', true);
        if (!$metadata || !isset($metadata['c'])) {
            $this->json(['success' => false, 'error' => 'داده‌های مدل (model_data) یافت نشد.'], 404);
            return;
        }

        try {
            // فراخوانی موتور حل‌کننده سیمپلکس
            $result = SimplexSolver::solve(
                $metadata['c'],
                $metadata['A'],
                $metadata['b'],
                $metadata['types'],
                $p['objective']
            );

            // نگاشت وضعیت‌های خروجی سیمپلکس به مقادیر مجاز ENUM در دیتابیس
            $statusMap = [
                'optimal'          => 'solved',
                'infeasible'       => 'infeasible',
                'unbounded'        => 'infeasible', // نامحدود را هم غیرممکن در نظر می‌گیریم
                'max_iter_reached' => 'solving',
                'error'            => 'draft',
            ];
            $dbStatus = $statusMap[$result['status']] ?? 'draft';

            // به‌روزرسانی دیتابیس
            $this->model->update($p['id'], [
                'status'        => $dbStatus,
                'solution_data' => json_encode($result),
            ]);

            // بازگرداندن پاسخ به فرانت‌اند
            $this->json([
                'success' => true,
                'result'  => $result
            ]);

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error'   => 'خطای داخلی در حل مدل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * نمایش نتایج حل مدل
     */
    public function result($id)
    {
        $this->requireAuth();
        
        $project = $this->model->getWithDetails((int)$id);

        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=simplex');
            return;
        }

        if (!$this->authorizeOwnership($project['user_id'])) {
            return; // متد authorizeOwnership خودش ریدایرکت و پیام خطا را مدیریت می‌کند
        }

        // decode کردن داده‌های حل شده اگر وجود داشته باشد
        $solutionData = null;
        if (!empty($project['solution_data'])) {
            $solutionData = json_decode($project['solution_data'], true);
        }

        $this->view('simplex/result', [
            'pageTitle'    => 'نتایج حل مدل: ' . $project['name'],
            'currentPage'  => 'simplex',
            'project'      => $project,
            'solutionData' => $solutionData
        ]);
    }
}