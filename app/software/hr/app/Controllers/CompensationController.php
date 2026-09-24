<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Compensation;
use App\Software\Hr\Models\Employee;
use App\Software\Hr\Models\Department;

/**
 * ============================================================
 * CompensationController - مدیریت حقوق و دستمزد
 * ============================================================
 */
class CompensationController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست احکام حقوقی
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Compensation();
        $filters = [
            'q'             => trim($_GET['q'] ?? ''),
            'employee_id'   => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'department_id' => !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null,
            'currency'      => trim($_GET['currency'] ?? ''),
            'active_only'   => !empty($_GET['active_only']) ? true : false,
        ];

        $empModel = new Employee();
        $deptModel = new Department();

        $this->renderSoftware('compensation/index', [
            'pageTitle'         => 'جبران خدمات',
            'softwareName'      => 'HR Analyzer',
            'compensations'     => $model->search($filters),
            'stats'             => $model->getStats(),
            'filters'           => $filters,
            'hr_employees'      => $empModel->getSelectList(),
            'hr_departments'    => $deptModel->getSelectList(),
            'currencyOptions'   => Compensation::getCurrencyOptions(),
            'salaryFields'      => Compensation::getSalaryFields(),
            'flash'             => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $empModel = new Employee();

        $preselectedEmployeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null;

        $this->renderSoftware('compensation/create', [
            'pageTitle'             => 'ایجاد حکم حقوقی',
            'softwareName'          => 'HR Analyzer',
            'hr_employees'          => $empModel->getSelectList(),
            'currencyOptions'       => Compensation::getCurrencyOptions(),
            'salaryFields'          => Compensation::getSalaryFields(),
            'preselectedEmployeeId' => $preselectedEmployeeId,
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('compensation'));
            return;
        }

        $this->verifyCsrf();

        $employeeId    = (int) ($_POST['employee_id'] ?? 0);
        $effectiveDate = trim($_POST['effective_date'] ?? '');

        if ($employeeId === 0 || $effectiveDate === '') {
            hr_flash_set('danger', 'کارمند و تاریخ اجرا الزامی است.');
            $this->redirect(hr_url('compensation', 'create'));
            return;
        }

        $effectiveG = $this->convertJalaliDate($effectiveDate);
        $endG       = $this->convertJalaliDate(trim($_POST['end_date'] ?? ''));

        if (!$effectiveG) {
            hr_flash_set('danger', 'تاریخ اجرا نامعتبر است.');
            $this->redirect(hr_url('compensation', 'create'));
            return;
        }

        $model = new Compensation();

        // بررسی تداخل تاریخ
        if ($model->hasDateConflict($employeeId, $effectiveG, $endG)) {
            hr_flash_set('warning', 'برای این کارمند در این بازه، حکم حقوقی دیگری وجود دارد.');
            $this->redirect(hr_url('compensation', 'create'));
            return;
        }

        // جمع‌بندی حقوق
        $payload = $this->collectSalaryPayload();
        $payload['employee_id']    = $employeeId;
        $payload['effective_date'] = $effectiveG;
        $payload['end_date']       = $endG;
        $payload['total_fixed']    = Compensation::calculateTotalFixed($payload);

        $newId = $model->create($payload);

        hr_flash_set('success', 'حکم حقوقی با موفقیت ایجاد شد.');
        $this->redirect(hr_url('compensation', 'show', ['id' => $newId]));
    }

    /**
     * نمایش
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Compensation();
        $compensation = $model->findWithDetails($id);

        if (!$compensation) {
            hr_flash_set('danger', 'حکم حقوقی یافت نشد.');
            $this->redirect(hr_url('compensation'));
            return;
        }

        // تاریخچه حقوق این کارمند
        $history = $model->getHistoryByEmployee((int) $compensation['employee_id']);

        $this->renderSoftware('compensation/show', [
            'pageTitle'     => 'جزئیات حکم حقوقی',
            'softwareName'  => 'HR Analyzer',
            'compensation'  => $compensation,
            'history'       => $history,
            'salaryFields'  => Compensation::getSalaryFields(),
            'currencyOptions' => Compensation::getCurrencyOptions(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Compensation();
        $compensation = $model->find($id);

        if (!$compensation) {
            hr_flash_set('danger', 'حکم حقوقی یافت نشد.');
            $this->redirect(hr_url('compensation'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('compensation/edit', [
            'pageTitle'       => 'ویرایش حکم حقوقی',
            'softwareName'    => 'HR Analyzer',
            'compensation'    => $compensation,
            'hr_employees'    => $empModel->getSelectList(),
            'currencyOptions' => Compensation::getCurrencyOptions(),
            'salaryFields'    => Compensation::getSalaryFields(),
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
            $this->redirect(hr_url('compensation'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Compensation();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'حکم حقوقی یافت نشد.');
            $this->redirect(hr_url('compensation'));
            return;
        }

        $employeeId    = (int) ($_POST['employee_id'] ?? 0);
        $effectiveDate = trim($_POST['effective_date'] ?? '');

        if ($employeeId === 0 || $effectiveDate === '') {
            hr_flash_set('danger', 'کارمند و تاریخ اجرا الزامی است.');
            $this->redirect(hr_url('compensation', 'edit', ['id' => $id]));
            return;
        }

        $effectiveG = $this->convertJalaliDate($effectiveDate);
        $endG       = $this->convertJalaliDate(trim($_POST['end_date'] ?? ''));

        if ($model->hasDateConflict($employeeId, $effectiveG, $endG, $id)) {
            hr_flash_set('warning', 'برای این کارمند در این بازه، حکم حقوقی دیگری وجود دارد.');
            $this->redirect(hr_url('compensation', 'edit', ['id' => $id]));
            return;
        }

        $payload = $this->collectSalaryPayload();
        $payload['employee_id']    = $employeeId;
        $payload['effective_date'] = $effectiveG;
        $payload['end_date']       = $endG;
        $payload['total_fixed']    = Compensation::calculateTotalFixed($payload);

        $model->update($id, $payload);

        hr_flash_set('success', 'حکم حقوقی به‌روزرسانی شد.');
        $this->redirect(hr_url('compensation', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Compensation();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'حکم حقوقی یافت نشد.');
            $this->redirect(hr_url('compensation'));
            return;
        }

        $model->delete($id);
        hr_flash_set('success', 'حکم حقوقی حذف شد.');
        $this->redirect(hr_url('compensation'));
    }

    /**
     * جمع‌آوری داده‌های مالی از POST
     */
    private function collectSalaryPayload(): array
    {
        return [
            'base_salary'              => $this->toDecimal($_POST['base_salary'] ?? null),
            'housing_allowance'        => $this->toDecimal($_POST['housing_allowance'] ?? null),
            'food_allowance'           => $this->toDecimal($_POST['food_allowance'] ?? null),
            'transportation_allowance' => $this->toDecimal($_POST['transportation_allowance'] ?? null),
            'child_allowance'          => $this->toDecimal($_POST['child_allowance'] ?? null),
            'seniority_allowance'      => $this->toDecimal($_POST['seniority_allowance'] ?? null),
            'other_allowances'         => $this->toDecimal($_POST['other_allowances'] ?? null),
            'overtime_rate'            => $this->toDecimal($_POST['overtime_rate'] ?? null),
            'currency'                 => trim($_POST['currency'] ?? 'IRR'),
            'change_reason'            => trim($_POST['change_reason'] ?? '') ?: null,
            'approved_by'              => !empty($_POST['approved_by']) ? (int) $_POST['approved_by'] : null,
            'document_ref'             => trim($_POST['document_ref'] ?? '') ?: null,
            'notes'                    => trim($_POST['notes'] ?? '') ?: null,
        ];
    }

    /**
     * تبدیل به عدد اعشاری امن
     */
    private function toDecimal($value): float
    {
        if ($value === null || $value === '') return 0.0;
        // حذف کاما و فاصله
        $value = str_replace([',', ' ', '٬'], '', (string) $value);
        return (float) $value;
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