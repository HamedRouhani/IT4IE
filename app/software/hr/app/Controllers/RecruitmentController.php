<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Recruitment;
use App\Software\Hr\Models\Position;
use App\Software\Hr\Models\Department;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * RecruitmentController - مدیریت نیازهای استخدامی
 * ============================================================
 */
class RecruitmentController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست نیازها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Recruitment();

        $filters = [
            'q'             => trim($_GET['q'] ?? ''),
            'status'        => trim($_GET['status'] ?? ''),
            'priority'      => trim($_GET['priority'] ?? ''),
            'department_id' => !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null,
        ];

        $recruitments = $model->search($filters);
        $stats = $model->getStats();

        $deptModel = new Department();

        $this->renderSoftware('recruitment/index', [
            'pageTitle'      => 'نیازهای استخدامی',
            'softwareName'   => 'HR Analyzer',
            'recruitments'   => $recruitments,
            'filters'        => $filters,
            'stats'          => $stats,
            'hr_departments' => $deptModel->getSelectList(),
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $posModel = new Position();
        $deptModel = new Department();
        $model = new Recruitment();

        $this->renderSoftware('recruitment/create', [
            'pageTitle'       => 'ایجاد نیاز استخدامی',
            'softwareName'    => 'HR Analyzer',
            'hr_positions'    => $posModel->getSelectList(),
            'hr_departments'  => $deptModel->getSelectList(),
            'suggested_number'=> $model->generateRequestNumber(),
            'statusOptions'   => Recruitment::getStatusOptions(),
            'priorityOptions' => Recruitment::getPriorityOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('recruitment'));
            return;
        }

        $this->verifyCsrf();

        $data = $this->collectPostData();

        if (empty($data['title']) || empty($data['request_number'])) {
            hr_flash_set('danger', 'عنوان و شماره درخواست الزامی است.');
            $this->redirect(hr_url('recruitment', 'create'));
            return;
        }

        $model = new Recruitment();

        if ($model->requestNumberExists($data['request_number'])) {
            hr_flash_set('danger', 'شماره درخواست تکراری است.');
            $this->redirect(hr_url('recruitment', 'create'));
            return;
        }

        $newId = $model->create($data);

        hr_flash_set('success', 'نیاز استخدامی با موفقیت ثبت شد.');
        $this->redirect(hr_url('recruitment', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Recruitment();
        $recruitment = $model->findWithDetails($id);

        if (!$recruitment) {
            hr_flash_set('danger', 'نیاز استخدامی یافت نشد.');
            $this->redirect(hr_url('recruitment'));
            return;
        }

        // متقاضیان این آگهی
        $candidateModel = new \App\Software\Hr\Models\Candidate();
        $candidates = $candidateModel->getByRecruitment($id);

        // آمار متقاضیان
        $candidateStats = [
            'total' => count($candidates),
        ];

        $this->renderSoftware('recruitment/show', [
            'pageTitle'      => 'جزئیات نیاز استخدامی',
            'softwareName'   => 'HR Analyzer',
            'recruitment'    => $recruitment,
            'candidates'     => $candidates,
            'candidateStats' => $candidateStats,
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Recruitment();
        $recruitment = $model->find($id);

        if (!$recruitment) {
            hr_flash_set('danger', 'نیاز استخدامی یافت نشد.');
            $this->redirect(hr_url('recruitment'));
            return;
        }

        $posModel = new Position();
        $deptModel = new Department();

        $this->renderSoftware('recruitment/edit', [
            'pageTitle'       => 'ویرایش نیاز استخدامی',
            'softwareName'    => 'HR Analyzer',
            'recruitment'     => $recruitment,
            'hr_positions'    => $posModel->getSelectList(),
            'hr_departments'  => $deptModel->getSelectList(),
            'statusOptions'   => Recruitment::getStatusOptions(),
            'priorityOptions' => Recruitment::getPriorityOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('recruitment'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Recruitment();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'نیاز استخدامی یافت نشد.');
            $this->redirect(hr_url('recruitment'));
            return;
        }

        $data = $this->collectPostData();

        if (empty($data['title']) || empty($data['request_number'])) {
            hr_flash_set('danger', 'عنوان و شماره درخواست الزامی است.');
            $this->redirect(hr_url('recruitment', 'edit', ['id' => $id]));
            return;
        }

        if ($model->requestNumberExists($data['request_number'], $id)) {
            hr_flash_set('danger', 'شماره درخواست تکراری است.');
            $this->redirect(hr_url('recruitment', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, $data);

        hr_flash_set('success', 'نیاز استخدامی به‌روزرسانی شد.');
        $this->redirect(hr_url('recruitment', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Recruitment();

        $recruitment = $model->find($id);
        if (!$recruitment) {
            hr_flash_set('danger', 'نیاز استخدامی یافت نشد.');
            $this->redirect(hr_url('recruitment'));
            return;
        }

        // بررسی وجود متقاضی
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_candidates 
             WHERE recruitment_id = ? AND system_id = ?"
        );
        $stmt->execute([$id, hr_active_system_id()]);
        $candidatesCount = (int) $stmt->fetchColumn();

        if ($candidatesCount > 0) {
            hr_flash_set('danger', 'این نیاز استخدامی دارای متقاضی است و قابل حذف نیست.');
            $this->redirect(hr_url('recruitment'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'نیاز استخدامی حذف شد.');
        $this->redirect(hr_url('recruitment'));
    }

    /**
     * جمع‌آوری داده‌ها
     */
    private function collectPostData(): array
    {
        $openedDate = trim($_POST['opened_date'] ?? '');
        $targetDate = trim($_POST['target_date'] ?? '');
        $closedDate = trim($_POST['closed_date'] ?? '');

        return [
            'position_id'      => !empty($_POST['position_id']) ? (int) $_POST['position_id'] : null,
            'department_id'    => !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null,
            'request_number'   => trim($_POST['request_number'] ?? ''),
            'title'            => trim($_POST['title'] ?? ''),
            'description'      => trim($_POST['description'] ?? '') ?: null,
            'requirements'     => trim($_POST['requirements'] ?? '') ?: null,
            'headcount'        => max(1, (int) ($_POST['headcount'] ?? 1)),
            'employment_type'  => trim($_POST['employment_type'] ?? 'full_time'),
            'min_salary'       => !empty($_POST['min_salary']) ? (float) str_replace(',', '', $_POST['min_salary']) : null,
            'max_salary'       => !empty($_POST['max_salary']) ? (float) str_replace(',', '', $_POST['max_salary']) : null,
            'priority'         => trim($_POST['priority'] ?? 'normal'),
            'status'           => trim($_POST['status'] ?? 'open'),
            'opened_date'      => $openedDate ? DateHelper::toGregorian($openedDate) : date('Y-m-d'),
            'target_date'      => $targetDate ? DateHelper::toGregorian($targetDate) : null,
            'closed_date'      => $closedDate ? DateHelper::toGregorian($closedDate) : null,
            'created_by'       => $_SESSION['user_id'] ?? null,
        ];
    }
}