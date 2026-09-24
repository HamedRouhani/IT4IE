<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Enrollment;
use App\Software\Hr\Models\Training;
use App\Software\Hr\Models\Employee;

/**
 * ============================================================
 * EnrollmentController - مدیریت ثبت‌نام‌های آموزشی
 * ============================================================
 */
class EnrollmentController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست ثبت‌نام‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Enrollment();
        $filters = [
            'q'           => trim($_GET['q'] ?? ''),
            'training_id' => !empty($_GET['training_id']) ? (int) $_GET['training_id'] : null,
            'employee_id' => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'status'      => trim($_GET['status'] ?? ''),
        ];

        $trainModel = new Training();
        $empModel   = new Employee();

        $this->renderSoftware('enrollment/index', [
            'pageTitle'     => 'ثبت‌نام‌های آموزشی',
            'softwareName'  => 'HR Analyzer',
            'enrollments'   => $model->search($filters),
            'stats'         => $model->getStats(),
            'filters'       => $filters,
            'hr_trainings'  => $trainModel->getSelectList(),
            'hr_employees'  => $empModel->getSelectList(),
            'statusOptions' => Enrollment::getStatusOptions(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد ثبت‌نام
     */
    public function create()
    {
        $this->requireAuth();

        $trainModel = new Training();
        $empModel   = new Employee();

        $preselectedTrainingId = !empty($_GET['training_id']) ? (int) $_GET['training_id'] : null;
        $preselectedEmployeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null;

        $this->renderSoftware('enrollment/create', [
            'pageTitle'             => 'ثبت‌نام در دوره',
            'softwareName'          => 'HR Analyzer',
            'hr_trainings'          => $trainModel->getSelectList(),
            'hr_employees'          => $empModel->getSelectList(),
            'statusOptions'         => Enrollment::getStatusOptions(),
            'ratingOptions'         => Enrollment::getRatingOptions(),
            'preselectedTrainingId' => $preselectedTrainingId,
            'preselectedEmployeeId' => $preselectedEmployeeId,
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره ثبت‌نام جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $this->verifyCsrf();

        $trainingId   = (int) ($_POST['training_id'] ?? 0);
        $employeeId   = (int) ($_POST['employee_id'] ?? 0);
        $enrolledDate = trim($_POST['enrolled_date'] ?? '');

        if ($trainingId === 0 || $employeeId === 0 || $enrolledDate === '') {
            hr_flash_set('danger', 'دوره، کارمند و تاریخ ثبت‌نام الزامی است.');
            $this->redirect(hr_url('enrollment', 'create'));
            return;
        }

        $enrolledG = $this->convertJalaliDate($enrolledDate);
        if (!$enrolledG) {
            hr_flash_set('danger', 'تاریخ نامعتبر است.');
            $this->redirect(hr_url('enrollment', 'create'));
            return;
        }

        $model = new Enrollment();

        // بررسی تکراری بودن ثبت‌نام
        if ($model->isDuplicate($trainingId, $employeeId)) {
            hr_flash_set('warning', 'این کارمند قبلاً در این دوره ثبت‌نام کرده است.');
            $this->redirect(hr_url('enrollment', 'create'));
            return;
        }

        $newId = $model->create([
            'training_id'        => $trainingId,
            'employee_id'        => $employeeId,
            'enrolled_date'      => $enrolledG,
            'status'             => trim($_POST['status'] ?? 'pending'),
            'attendance_percent' => isset($_POST['attendance_percent']) && $_POST['attendance_percent'] !== ''
                                        ? max(0, min(100, (int) $_POST['attendance_percent']))
                                        : null,
            'score'              => isset($_POST['score']) && $_POST['score'] !== ''
                                        ? (float) $_POST['score']
                                        : null,
            'rating'             => trim($_POST['rating'] ?? '') ?: null,
            'certificate_issued' => !empty($_POST['certificate_issued']) ? 1 : 0,
            'certificate_number' => trim($_POST['certificate_number'] ?? '') ?: null,
            'feedback'           => trim($_POST['feedback'] ?? '') ?: null,
            'employee_notes'     => trim($_POST['employee_notes'] ?? '') ?: null,
            'manager_notes'      => trim($_POST['manager_notes'] ?? '') ?: null,
            'completed_at'       => isset($_POST['completed_at']) && $_POST['completed_at'] !== ''
                                        ? $this->convertJalaliDate($_POST['completed_at'])
                                        : null,
        ]);

        // هماهنگ‌سازی شمارنده ثبت‌نام در جدول دوره‌ها
        $trainingModel = new Training();
        $trainingModel->syncEnrolledCount($trainingId);

        hr_flash_set('success', 'ثبت‌نام با موفقیت انجام شد.');
        $this->redirect(hr_url('enrollment', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات ثبت‌نام
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Enrollment();
        $enrollment = $model->findWithDetails($id);

        if (!$enrollment) {
            hr_flash_set('danger', 'ثبت‌نام یافت نشد.');
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $this->renderSoftware('enrollment/show', [
            'pageTitle'      => 'جزئیات ثبت‌نام',
            'softwareName'   => 'HR Analyzer',
            'enrollment'     => $enrollment,
            'statusOptions'  => Enrollment::getStatusOptions(),
            'ratingOptions'  => Enrollment::getRatingOptions(),
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
        $model = new Enrollment();
        $enrollment = $model->find($id);

        if (!$enrollment) {
            hr_flash_set('danger', 'ثبت‌نام یافت نشد.');
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $trainModel = new Training();
        $empModel   = new Employee();

        $this->renderSoftware('enrollment/edit', [
            'pageTitle'     => 'ویرایش ثبت‌نام',
            'softwareName'  => 'HR Analyzer',
            'enrollment'    => $enrollment,
            'hr_trainings'  => $trainModel->getSelectList(),
            'hr_employees'  => $empModel->getSelectList(),
            'statusOptions' => Enrollment::getStatusOptions(),
            'ratingOptions' => Enrollment::getRatingOptions(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Enrollment();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'ثبت‌نام یافت نشد.');
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $trainingId   = (int) ($_POST['training_id'] ?? 0);
        $employeeId   = (int) ($_POST['employee_id'] ?? 0);
        $enrolledDate = trim($_POST['enrolled_date'] ?? '');

        if ($trainingId === 0 || $employeeId === 0 || $enrolledDate === '') {
            hr_flash_set('danger', 'دوره، کارمند و تاریخ ثبت‌نام الزامی است.');
            $this->redirect(hr_url('enrollment', 'edit', ['id' => $id]));
            return;
        }

        $enrolledG = $this->convertJalaliDate($enrolledDate);

        // بررسی تکراری بودن (به جز خود رکورد)
        if ($model->isDuplicate($trainingId, $employeeId, $id)) {
            hr_flash_set('warning', 'این کارمند قبلاً در این دوره ثبت‌نام کرده است.');
            $this->redirect(hr_url('enrollment', 'edit', ['id' => $id]));
            return;
        }

        // دریافت اطلاعات قدیمی برای هماهنگ‌سازی
        $old = $model->find($id);

        $model->update($id, [
            'training_id'        => $trainingId,
            'employee_id'        => $employeeId,
            'enrolled_date'      => $enrolledG,
            'status'             => trim($_POST['status'] ?? 'pending'),
            'attendance_percent' => isset($_POST['attendance_percent']) && $_POST['attendance_percent'] !== ''
                                        ? max(0, min(100, (int) $_POST['attendance_percent']))
                                        : null,
            'score'              => isset($_POST['score']) && $_POST['score'] !== ''
                                        ? (float) $_POST['score']
                                        : null,
            'rating'             => trim($_POST['rating'] ?? '') ?: null,
            'certificate_issued' => !empty($_POST['certificate_issued']) ? 1 : 0,
            'certificate_number' => trim($_POST['certificate_number'] ?? '') ?: null,
            'feedback'           => trim($_POST['feedback'] ?? '') ?: null,
            'employee_notes'     => trim($_POST['employee_notes'] ?? '') ?: null,
            'manager_notes'      => trim($_POST['manager_notes'] ?? '') ?: null,
            'completed_at'       => isset($_POST['completed_at']) && $_POST['completed_at'] !== ''
                                        ? $this->convertJalaliDate($_POST['completed_at'])
                                        : null,
        ]);

        // هماهنگ‌سازی هر دو دوره (اگر دوره عوض شده باشد)
        $trainingModel = new Training();
        $trainingModel->syncEnrolledCount($trainingId);
        if ($old && (int) $old['training_id'] !== $trainingId) {
            $trainingModel->syncEnrolledCount((int) $old['training_id']);
        }

        hr_flash_set('success', 'ثبت‌نام به‌روزرسانی شد.');
        $this->redirect(hr_url('enrollment', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Enrollment();

        $enrollment = $model->find($id);
        if (!$enrollment) {
            hr_flash_set('danger', 'ثبت‌نام یافت نشد.');
            $this->redirect(hr_url('enrollment'));
            return;
        }

        $trainingId = (int) $enrollment['training_id'];
        $model->delete($id);

        // هماهنگ‌سازی شمارنده ثبت‌نام
        $trainingModel = new Training();
        $trainingModel->syncEnrolledCount($trainingId);

        hr_flash_set('success', 'ثبت‌نام حذف شد.');
        $this->redirect(hr_url('enrollment'));
    }

    /**
     * تبدیل تاریخ شمسی به میلادی (Y/m/d)
     */
    private function convertJalaliDate(string $jalali): ?string
    {
        $jalali = trim($jalali);
        if ($jalali === '') return null;

        // نرمال‌سازی اعداد فارسی/عربی
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