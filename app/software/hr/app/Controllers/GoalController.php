<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Goal;
use App\Software\Hr\Models\Employee;

/**
 * ============================================================
 * GoalController - مدیریت اهداف (OKR / MBO)
 * ============================================================
 */
class GoalController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requireAuth();

        $model = new Goal();
        $filters = [
            'q'           => trim($_GET['q'] ?? ''),
            'employee_id' => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'goal_type'   => trim($_GET['goal_type'] ?? ''),
            'status'      => trim($_GET['status'] ?? ''),
            'priority'    => trim($_GET['priority'] ?? ''),
            'period'      => trim($_GET['period'] ?? ''),
        ];

        $empModel = new Employee();

        $this->renderSoftware('goal/index', [
            'pageTitle'      => 'اهداف سازمانی',
            'softwareName'   => 'HR Analyzer',
            'goals'          => $model->search($filters),
            'stats'          => $model->getStats(),
            'filters'        => $filters,
            'hr_employees'   => $empModel->getSelectList(),
            'typeOptions'    => Goal::getTypeOptions(),
            'statusOptions'  => Goal::getStatusOptions(),
            'priorityOptions'=> [
                'low' => 'پایین', 'normal' => 'عادی', 'high' => 'بالا',
                'urgent' => 'فوری', 'critical' => 'بحرانی',
            ],
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    public function create()
    {
        $this->requireAuth();

        $empModel = new Employee();
        $model = new Goal();

        $this->renderSoftware('goal/create', [
            'pageTitle'       => 'ایجاد هدف جدید',
            'softwareName'    => 'HR Analyzer',
            'hr_employees'    => $empModel->getSelectList(),
            'hr_parent_goals' => $model->search(['status' => 'active']),
            'typeOptions'     => Goal::getTypeOptions(),
            'statusOptions'   => Goal::getStatusOptions(),
            'priorityOptions' => [
                'low' => 'پایین', 'normal' => 'عادی', 'high' => 'بالا',
                'urgent' => 'فوری', 'critical' => 'بحرانی',
            ],
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('goal'));
            return;
        }
        $this->verifyCsrf();

        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $title      = trim($_POST['title'] ?? '');
        $startDate  = trim($_POST['start_date'] ?? '');
        $dueDate    = trim($_POST['due_date'] ?? '');

        if ($employeeId === 0 || $title === '' || $startDate === '' || $dueDate === '') {
            hr_flash_set('danger', 'کارمند، عنوان، تاریخ شروع و پایان الزامی است.');
            $this->redirect(hr_url('goal', 'create'));
            return;
        }

        $startG = $this->convertJalaliDate($startDate);
        $dueG   = $this->convertJalaliDate($dueDate);
        if (!$startG || !$dueG) {
            hr_flash_set('danger', 'تاریخ نامعتبر است.');
            $this->redirect(hr_url('goal', 'create'));
            return;
        }

        $model = new Goal();
        $newId = $model->create([
            'employee_id'    => $employeeId,
            'parent_goal_id' => !empty($_POST['parent_goal_id']) ? (int) $_POST['parent_goal_id'] : null,
            'title'          => $title,
            'description'    => trim($_POST['description'] ?? '') ?: null,
            'goal_type'      => trim($_POST['goal_type'] ?? 'okr'),
            'category'       => trim($_POST['category'] ?? '') ?: null,
            'weight'         => (float) ($_POST['weight'] ?? 100),
            'target_value'   => $_POST['target_value'] !== '' ? (float) $_POST['target_value'] : null,
            'current_value'  => (float) ($_POST['current_value'] ?? 0),
            'unit'           => trim($_POST['unit'] ?? '') ?: null,
            'start_date'     => $startG,
            'due_date'       => $dueG,
            'priority'       => trim($_POST['priority'] ?? 'normal'),
            'progress'       => max(0, min(100, (int) ($_POST['progress'] ?? 0))),
            'status'         => trim($_POST['status'] ?? 'draft'),
            'review_period'  => trim($_POST['review_period'] ?? '') ?: null,
            'notes'          => trim($_POST['notes'] ?? '') ?: null,
            'created_by'     => (int) ($_SESSION['user_id'] ?? 0) ?: null,
        ]);

        hr_flash_set('success', 'هدف با موفقیت ایجاد شد.');
        $this->redirect(hr_url('goal', 'show', ['id' => $newId]));
    }

    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Goal();
        $goal = $model->findWithDetails($id);

        if (!$goal) {
            hr_flash_set('danger', 'هدف یافت نشد.');
            $this->redirect(hr_url('goal'));
            return;
        }

        $this->renderSoftware('goal/show', [
            'pageTitle'    => 'جزئیات هدف',
            'softwareName' => 'HR Analyzer',
            'goal'         => $goal,
            'children'     => $model->getChildren($id),
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Goal();
        $goal = $model->find($id);

        if (!$goal) {
            hr_flash_set('danger', 'هدف یافت نشد.');
            $this->redirect(hr_url('goal'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('goal/edit', [
            'pageTitle'       => 'ویرایش هدف',
            'softwareName'    => 'HR Analyzer',
            'goal'            => $goal,
            'hr_employees'    => $empModel->getSelectList(),
            'hr_parent_goals' => $model->search(['status' => 'active']),
            'typeOptions'     => Goal::getTypeOptions(),
            'statusOptions'   => Goal::getStatusOptions(),
            'priorityOptions' => [
                'low' => 'پایین', 'normal' => 'عادی', 'high' => 'بالا',
                'urgent' => 'فوری', 'critical' => 'بحرانی',
            ],
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('goal'));
            return;
        }
        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Goal();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'هدف یافت نشد.');
            $this->redirect(hr_url('goal'));
            return;
        }

        $startG = $this->convertJalaliDate(trim($_POST['start_date'] ?? ''));
        $dueG   = $this->convertJalaliDate(trim($_POST['due_date'] ?? ''));

        $model->update($id, [
            'employee_id'    => (int) ($_POST['employee_id'] ?? 0),
            'parent_goal_id' => !empty($_POST['parent_goal_id']) ? (int) $_POST['parent_goal_id'] : null,
            'title'          => trim($_POST['title'] ?? ''),
            'description'    => trim($_POST['description'] ?? '') ?: null,
            'goal_type'      => trim($_POST['goal_type'] ?? 'okr'),
            'category'       => trim($_POST['category'] ?? '') ?: null,
            'weight'         => (float) ($_POST['weight'] ?? 100),
            'target_value'   => $_POST['target_value'] !== '' ? (float) $_POST['target_value'] : null,
            'current_value'  => (float) ($_POST['current_value'] ?? 0),
            'unit'           => trim($_POST['unit'] ?? '') ?: null,
            'start_date'     => $startG,
            'due_date'       => $dueG,
            'priority'       => trim($_POST['priority'] ?? 'normal'),
            'progress'       => max(0, min(100, (int) ($_POST['progress'] ?? 0))),
            'status'         => trim($_POST['status'] ?? 'draft'),
            'review_period'  => trim($_POST['review_period'] ?? '') ?: null,
            'notes'          => trim($_POST['notes'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'هدف به‌روزرسانی شد.');
        $this->redirect(hr_url('goal', 'show', ['id' => $id]));
    }

    public function delete($id)
    {
        $this->requireAuth();
        $id = (int) $id;
        $model = new Goal();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'هدف یافت نشد.');
            $this->redirect(hr_url('goal'));
            return;
        }
        $model->delete($id);
        hr_flash_set('success', 'هدف حذف شد.');
        $this->redirect(hr_url('goal'));
    }

    /**
     * تبدیل تاریخ شمسی به میلادی (Y/m/d)
     */
    private function convertJalaliDate(string $jalali): ?string
    {
        $jalali = trim($jalali);
        if ($jalali === '') return null;

        $jalali = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $jalali
        );
        $jalali = str_replace('-', '/', $jalali);
        $parts = explode('/', $jalali);
        if (count($parts) !== 3) return null;

        list($jy, $jm, $jd) = array_map('intval', $parts);
        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        list($gy, $gm, $gd) = hr_jalali_to_gregorian($jy, $jm, $jd);
        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }
}