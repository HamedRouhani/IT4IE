<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Employee;
use App\Software\Hr\Models\EmployeeDocument;
use App\Software\Hr\Models\EmployeeContact;
use App\Software\Hr\Models\EmployeeHistory;
use App\Software\Hr\Models\Department;
use App\Software\Hr\Models\Position;
use App\Software\Hr\Models\JobGrade;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * EmployeeController - مدیریت کارکنان (قلب ماژول HR)
 * ============================================================
 */
class EmployeeController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست کارکنان با فیلتر
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Employee();

        $filters = [
            'q'                  => trim($_GET['q'] ?? ''),
            'department_id'      => !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null,
            'position_id'        => !empty($_GET['position_id']) ? (int) $_GET['position_id'] : null,
            'grade_id'           => !empty($_GET['grade_id']) ? (int) $_GET['grade_id'] : null,
            'employment_status'  => trim($_GET['employment_status'] ?? ''),
            'gender'             => trim($_GET['gender'] ?? ''),
            'education_level'    => trim($_GET['education_level'] ?? ''),
            'expiring_contract'  => !empty($_GET['expiring_contract']),
            'expiring_probation' => !empty($_GET['expiring_probation']),
        ];

        $employees = $model->search($filters);
        $stats = $model->getStats();

        $deptModel = new Department();
        $posModel = new Position();
        $gradeModel = new JobGrade();

        $this->renderSoftware('employee/index', [
            'pageTitle'         => 'کارکنان',
            'softwareName'      => 'HR Analyzer',
            'employees'         => $employees,
            'filters'           => $filters,
            'stats'             => $stats,
            'hr_departments'    => $deptModel->getSelectList(),
            'hr_positions'      => $posModel->getSelectList(),
            'hr_grades'         => $gradeModel->getSelectList(),
            'flash'             => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $deptModel = new Department();
        $posModel = new Position();
        $gradeModel = new JobGrade();
        $empModel = new Employee();

        $this->renderSoftware('employee/create', [
            'pageTitle'         => 'افزودن کارمند جدید',
            'softwareName'      => 'HR Analyzer',
            'hr_departments'    => $deptModel->getSelectList(),
            'hr_positions'      => $posModel->getSelectList(),
            'hr_grades'         => $gradeModel->getSelectList(),
            'hr_managers'       => $empModel->getSelectList(),
            'suggested_code'    => $empModel->generateEmployeeCode(),
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
            $this->redirect(hr_url('employee'));
            return;
        }

        $this->verifyCsrf();

        $data = $this->collectPostData();

        // اعتبارسنجی
        if (empty($data['first_name']) || empty($data['last_name'])) {
            hr_flash_set('danger', 'نام و نام خانوادگی الزامی است.');
            $this->redirect(hr_url('employee', 'create'));
            return;
        }

        if (empty($data['employee_code'])) {
            hr_flash_set('danger', 'کد پرسنلی الزامی است.');
            $this->redirect(hr_url('employee', 'create'));
            return;
        }

        $model = new Employee();

        if ($model->codeExists($data['employee_code'])) {
            hr_flash_set('danger', 'کد پرسنلی تکراری است.');
            $this->redirect(hr_url('employee', 'create'));
            return;
        }

        if (!empty($data['national_id']) && $model->nationalIdExists($data['national_id'])) {
            hr_flash_set('danger', 'کد ملی تکراری است.');
            $this->redirect(hr_url('employee', 'create'));
            return;
        }

        // ذخیره
        $newId = $model->create($data);

        // ثبت تاریخچه استخدام
        if (!empty($data['hire_date'])) {
            $historyModel = new EmployeeHistory();
            $historyModel->record(
                $newId,
                'hire',
                $data['hire_date'],
                null,
                $data['employment_status'] ?? 'active',
                'ثبت اولیه کارمند'
            );
        }

        // به‌روزرسانی filled_count پست
        $model->syncPositionCount($data['position_id'] ?? null);

        hr_flash_set('success', 'کارمند با موفقیت ثبت شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Employee();
        $employee = $model->findWithDetails($id);

        if (!$employee) {
            hr_flash_set('danger', 'کارمند یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        // اسناد
        $docModel = new EmployeeDocument();
        $documents = $docModel->getByEmployee($id);

        // تماس‌های اضطراری
        $contactModel = new EmployeeContact();
        $contacts = $contactModel->getByEmployee($id);

        // تاریخچه
        $historyModel = new EmployeeHistory();
        $history = $historyModel->getByEmployee($id);

        // آمار سن و سابقه
        $age = hr_calculate_age($employee['birth_date'] ?? null);
        $tenure = hr_calculate_tenure($employee['hire_date'] ?? null);

        $this->renderSoftware('employee/show', [
            'pageTitle'    => 'جزئیات کارمند: ' . hr_full_name($employee),
            'softwareName' => 'HR Analyzer',
            'employee'     => $employee,
            'documents'    => $documents,
            'contacts'     => $contacts,
            'history'      => $history,
            'age'          => $age,
            'tenure'       => $tenure,
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
        $model = new Employee();
        $employee = $model->find($id);

        if (!$employee) {
            hr_flash_set('danger', 'کارمند یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $deptModel = new Department();
        $posModel = new Position();
        $gradeModel = new JobGrade();

        // حذف خود کارمند از لیست مدیران
        $managers = array_filter(
            $model->getSelectList(),
            fn($m) => (int) $m['id'] !== $id
        );

        $this->renderSoftware('employee/edit', [
            'pageTitle'       => 'ویرایش کارمند',
            'softwareName'    => 'HR Analyzer',
            'employee'        => $employee,
            'hr_departments'  => $deptModel->getSelectList(),
            'hr_positions'    => $posModel->getSelectList(),
            'hr_grades'       => $gradeModel->getSelectList(),
            'hr_managers'     => $managers,
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
            $this->redirect(hr_url('employee'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Employee();

        $oldEmployee = $model->find($id);
        if (!$oldEmployee) {
            hr_flash_set('danger', 'کارمند یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $data = $this->collectPostData();

        if (empty($data['first_name']) || empty($data['last_name'])) {
            hr_flash_set('danger', 'نام و نام خانوادگی الزامی است.');
            $this->redirect(hr_url('employee', 'edit', ['id' => $id]));
            return;
        }

        if ($model->codeExists($data['employee_code'], $id)) {
            hr_flash_set('danger', 'کد پرسنلی تکراری است.');
            $this->redirect(hr_url('employee', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['national_id']) && $model->nationalIdExists($data['national_id'], $id)) {
            hr_flash_set('danger', 'کد ملی تکراری است.');
            $this->redirect(hr_url('employee', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['manager_id']) && (int) $data['manager_id'] === $id) {
            hr_flash_set('danger', 'کارمند نمی‌تواند مدیر خودش باشد.');
            $this->redirect(hr_url('employee', 'edit', ['id' => $id]));
            return;
        }

        // ثبت تغییرات در تاریخچه
        $historyModel = new EmployeeHistory();
        $changeDate = date('Y-m-d');

        // تغییر پست
        if ((int) $oldEmployee['position_id'] !== (int) ($data['position_id'] ?? 0)) {
            $historyModel->record($id, 'position_change', $changeDate,
                (string) $oldEmployee['position_id'], (string) ($data['position_id'] ?? ''), 'ویرایش کارمند');
        }

        // تغییر دپارتمان
        if ((int) $oldEmployee['department_id'] !== (int) ($data['department_id'] ?? 0)) {
            $historyModel->record($id, 'department_change', $changeDate,
                (string) $oldEmployee['department_id'], (string) ($data['department_id'] ?? ''), 'ویرایش کارمند');
        }

        // تغییر وضعیت
        if ($oldEmployee['employment_status'] !== $data['employment_status']) {
            $historyModel->record($id, 'status_change', $changeDate,
                $oldEmployee['employment_status'], $data['employment_status'], 'ویرایش کارمند');
        }

        // به‌روزرسانی
        $model->update($id, $data);

        // sync position counts برای پست قبلی و جدید
        $model->syncPositionCount($oldEmployee['position_id'] ?? null);
        $model->syncPositionCount($data['position_id'] ?? null);

        hr_flash_set('success', 'کارمند با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Employee();

        $employee = $model->find($id);
        if (!$employee) {
            hr_flash_set('danger', 'کارمند یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        // ذخیره position_id برای sync
        $positionId = $employee['position_id'] ?? null;

        // حذف (CASCADE تمام وابستگی‌ها را پاک می‌کند)
        $model->delete($id);

        // sync position count
        $model->syncPositionCount($positionId);

        hr_flash_set('success', 'کارمند با موفقیت حذف شد.');
        $this->redirect(hr_url('employee'));
    }

    /**
     * جمع‌آوری داده‌های POST (با تبدیل تاریخ شمسی → میلادی)
     */
    private function collectPostData(): array
    {
        // تبدیل تاریخ‌های شمسی به میلادی
        $birthDate = $this->jalaliToGregorian($_POST['birth_date'] ?? '');
        $hireDate = $this->jalaliToGregorian($_POST['hire_date'] ?? '');
        $contractStartDate = $this->jalaliToGregorian($_POST['contract_start_date'] ?? '');
        $contractEndDate = $this->jalaliToGregorian($_POST['contract_end_date'] ?? '');
        $probationEndDate = $this->jalaliToGregorian($_POST['probation_end_date'] ?? '');
        $terminationDate = $this->jalaliToGregorian($_POST['termination_date'] ?? '');

        return [
            'department_id'      => !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null,
            'position_id'        => !empty($_POST['position_id']) ? (int) $_POST['position_id'] : null,
            'grade_id'           => !empty($_POST['grade_id']) ? (int) $_POST['grade_id'] : null,
            'manager_id'         => !empty($_POST['manager_id']) ? (int) $_POST['manager_id'] : null,
            'employee_code'      => trim($_POST['employee_code'] ?? ''),
            'national_id'        => trim($_POST['national_id'] ?? '') ?: null,
            'personnel_number'   => trim($_POST['personnel_number'] ?? '') ?: null,
            'first_name'         => trim($_POST['first_name'] ?? ''),
            'last_name'          => trim($_POST['last_name'] ?? ''),
            'father_name'        => trim($_POST['father_name'] ?? '') ?: null,
            'gender'             => trim($_POST['gender'] ?? 'male'),
            'birth_date'         => $birthDate,
            'birth_place'        => trim($_POST['birth_place'] ?? '') ?: null,
            'marital_status'     => trim($_POST['marital_status'] ?? 'single'),
            'dependents_count'   => max(0, (int) ($_POST['dependents_count'] ?? 0)),
            'military_status'    => trim($_POST['military_status'] ?? 'not_applicable'),
            'mobile'             => trim($_POST['mobile'] ?? '') ?: null,
            'phone'              => trim($_POST['phone'] ?? '') ?: null,
            'email'              => trim($_POST['email'] ?? '') ?: null,
            'address'            => trim($_POST['address'] ?? '') ?: null,
            'postal_code'        => trim($_POST['postal_code'] ?? '') ?: null,
            'education_level'    => trim($_POST['education_level'] ?? 'bachelor'),
            'field_of_study'     => trim($_POST['field_of_study'] ?? '') ?: null,
            'university'         => trim($_POST['university'] ?? '') ?: null,
            'graduation_year'    => !empty($_POST['graduation_year']) ? (int) $_POST['graduation_year'] : null,
            'hire_date'          => $hireDate ?: date('Y-m-d'),
            'contract_start_date'=> $contractStartDate,
            'contract_end_date'  => $contractEndDate,
            'contract_type'      => trim($_POST['contract_type'] ?? 'permanent'),
            'probation_end_date' => $probationEndDate,
            'employment_status'  => trim($_POST['employment_status'] ?? 'active'),
            'termination_date'   => $terminationDate,
            'termination_reason' => trim($_POST['termination_reason'] ?? '') ?: null,
            'bank_name'          => trim($_POST['bank_name'] ?? '') ?: null,
            'bank_account'       => trim($_POST['bank_account'] ?? '') ?: null,
            'iban'               => trim($_POST['iban'] ?? '') ?: null,
            'card_number'        => trim($_POST['card_number'] ?? '') ?: null,
            'insurance_number'   => trim($_POST['insurance_number'] ?? '') ?: null,
            'insurance_type'     => trim($_POST['insurance_type'] ?? '') ?: null,
            'tax_code'           => trim($_POST['tax_code'] ?? '') ?: null,
            'notes'              => trim($_POST['notes'] ?? '') ?: null,
            'status'             => 'active',
        ];
    }

    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    private function jalaliToGregorian(string $jalaliDate): ?string
    {
        $jalaliDate = trim($jalaliDate);
        if (empty($jalaliDate)) return null;

        if (class_exists('\App\Helpers\DateHelper')) {
            return \App\Helpers\DateHelper::toGregorian($jalaliDate);
        }

        // فال‌بک
        $parts = explode('/', str_replace('-', '/', $jalaliDate));
        if (count($parts) !== 3) return null;

        $g = hr_jalali_to_gregorian((int) $parts[0], (int) $parts[1], (int) $parts[2]);
        return sprintf('%04d-%02d-%02d', $g[0], $g[1], $g[2]);
    }
}