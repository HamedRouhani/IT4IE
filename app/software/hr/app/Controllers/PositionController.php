<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Position;
use App\Software\Hr\Models\Department;
use App\Software\Hr\Models\JobGrade;

/**
 * ============================================================
 * PositionController - مدیریت پست‌های سازمانی
 * ============================================================
 * مسیر: app/software/hr/app/Controllers/PositionController.php
 * ============================================================
 */
class PositionController extends Controller
{
    /** @var \PDO */
    private $db;

    public function __construct()
    {
        // ⚠️ Controller والد __construct ندارد
        $this->db = Database::getInstance();
    }

    /**
     * لیست پست‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Position();

        $filters = [
            'q'             => trim($_GET['q'] ?? ''),
            'department_id' => !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null,
            'grade_id'      => !empty($_GET['grade_id']) ? (int) $_GET['grade_id'] : null,
            'is_active'     => !empty($_GET['is_active']),
            'has_open'      => !empty($_GET['has_open']),
            'is_critical'   => !empty($_GET['is_critical']),
        ];

        $positions = $model->search($filters);
        $stats = $model->getStats();

        $deptModel = new Department();
        $gradeModel = new JobGrade();

        $this->renderSoftware('position/index', [
            'pageTitle'        => 'پست‌های سازمانی',
            'softwareName'     => 'HR Analyzer',
            'positions'        => $positions,
            'filters'          => $filters,
            'stats'            => $stats,
            'hr_departments'   => $deptModel->getSelectList(),
            'hr_grades'        => $gradeModel->getSelectList(),
            'flash'            => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $deptModel = new Department();
        $gradeModel = new JobGrade();
        $model = new Position();

        $this->renderSoftware('position/create', [
            'pageTitle'         => 'افزودن پست جدید',
            'softwareName'      => 'HR Analyzer',
            'hr_departments'    => $deptModel->getSelectList(),
            'hr_grades'         => $gradeModel->getSelectList(),
            'hr_parents'        => $model->getSelectList(),
            'flash'             => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('position'));
            return;
        }

        $this->verifyCsrf();

        $data = $this->collectPostData();

        if (empty($data['title'])) {
            hr_flash_set('danger', 'عنوان پست الزامی است.');
            $this->redirect(hr_url('position', 'create'));
            return;
        }

        $model = new Position();

        if (!empty($data['code']) && $model->codeExists($data['code'])) {
            hr_flash_set('danger', 'کد پست تکراری است.');
            $this->redirect(hr_url('position', 'create'));
            return;
        }

        $model->create($data);

        hr_flash_set('success', 'پست با موفقیت ثبت شد.');
        $this->redirect(hr_url('position'));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Position();
        $position = $model->findWithDetails($id);

        if (!$position) {
            hr_flash_set('danger', 'پست یافت نشد.');
            $this->redirect(hr_url('position'));
            return;
        }

        // ✅ استفاده از hr_active_system_id() به جای $this->systemId
        $systemId = hr_active_system_id();

        // کارکنان این پست
        $stmt = $this->db->prepare(
            "SELECT id, employee_code, first_name, last_name, hire_date, employment_status
             FROM hr_employees 
             WHERE position_id = ? AND system_id = ?
             ORDER BY last_name ASC"
        );
        $stmt->execute([$id, $systemId]);
        $employees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->renderSoftware('position/show', [
            'pageTitle'    => 'جزئیات پست: ' . $position['title'],
            'softwareName' => 'HR Analyzer',
            'position'     => $position,
            'employees'    => $employees,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Position();
        $position = $model->find($id);

        if (!$position) {
            hr_flash_set('danger', 'پست یافت نشد.');
            $this->redirect(hr_url('position'));
            return;
        }

        $deptModel = new Department();
        $gradeModel = new JobGrade();

        $parentPositions = array_filter(
            $model->getSelectList(),
            fn($p) => (int) $p['id'] !== $id
        );

        $this->renderSoftware('position/edit', [
            'pageTitle'         => 'ویرایش پست',
            'softwareName'      => 'HR Analyzer',
            'position'          => $position,
            'hr_departments'    => $deptModel->getSelectList(),
            'hr_grades'         => $gradeModel->getSelectList(),
            'hr_parents'        => $parentPositions,
            'flash'             => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('position'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Position();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'پست یافت نشد.');
            $this->redirect(hr_url('position'));
            return;
        }

        $data = $this->collectPostData();

        if (empty($data['title'])) {
            hr_flash_set('danger', 'عنوان پست الزامی است.');
            $this->redirect(hr_url('position', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['code']) && $model->codeExists($data['code'], $id)) {
            hr_flash_set('danger', 'کد پست تکراری است.');
            $this->redirect(hr_url('position', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['parent_position_id']) && (int) $data['parent_position_id'] === $id) {
            hr_flash_set('danger', 'یک پست نمی‌تواند والد خودش باشد.');
            $this->redirect(hr_url('position', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, $data);

        hr_flash_set('success', 'پست با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('position'));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Position();

        $position = $model->find($id);
        if (!$position) {
            hr_flash_set('danger', 'پست یافت نشد.');
            $this->redirect(hr_url('position'));
            return;
        }

        if ($model->isUsedInEmployees($id)) {
            hr_flash_set('danger', 'این پست در کارکنان استفاده شده است.');
            $this->redirect(hr_url('position'));
            return;
        }

        if ($model->hasChildren($id)) {
            hr_flash_set('danger', 'این پست دارای زیرمجموعه است.');
            $this->redirect(hr_url('position'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'پست با موفقیت حذف شد.');
        $this->redirect(hr_url('position'));
    }

    /**
     * جمع‌آوری داده‌های POST
     */
    private function collectPostData(): array
    {
        return [
            'department_id'        => !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null,
            'grade_id'             => !empty($_POST['grade_id']) ? (int) $_POST['grade_id'] : null,
            'parent_position_id'   => !empty($_POST['parent_position_id']) ? (int) $_POST['parent_position_id'] : null,
            'code'                 => trim($_POST['code'] ?? '') ?: null,
            'title'                => trim($_POST['title'] ?? ''),
            'position_type'        => trim($_POST['position_type'] ?? 'permanent'),
            'employment_type'      => trim($_POST['employment_type'] ?? 'full_time'),
            'headcount'            => max(1, (int) ($_POST['headcount'] ?? 1)),
            'min_education'        => trim($_POST['min_education'] ?? '') ?: null,
            'min_experience_years' => max(0, (int) ($_POST['min_experience_years'] ?? 0)),
            'is_managerial'        => !empty($_POST['is_managerial']) ? 1 : 0,
            'is_critical'          => !empty($_POST['is_critical']) ? 1 : 0,
            'is_active'            => !empty($_POST['is_active']) ? 1 : 0,
            'description'          => trim($_POST['description'] ?? '') ?: null,
        ];
    }
}