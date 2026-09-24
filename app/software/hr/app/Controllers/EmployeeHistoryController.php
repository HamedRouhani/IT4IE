<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\EmployeeHistory;
use App\Software\Hr\Models\Employee;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * EmployeeHistoryController - مدیریت تاریخچه تغییرات کارمند
 * ============================================================
 */
class EmployeeHistoryController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست تاریخچه یک کارمند
     */
    public function index()
    {
        $this->requireAuth();

        $employeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;

        if ($employeeId === 0) {
            hr_flash_set('danger', 'شناسه کارمند مشخص نیست.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $model = new EmployeeHistory();
        $history = $model->getByEmployee($employeeId);

        $empModel = new Employee();
        $employee = $empModel->find($employeeId);

        if (!$employee) {
            hr_flash_set('danger', 'کارمند یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $this->renderSoftware('employee_history/index', [
            'pageTitle'    => 'تاریخچه کارمند: ' . hr_full_name($employee),
            'softwareName' => 'HR Analyzer',
            'employee'     => $employee,
            'history'      => $history,
            'flash'        => hr_flash_get(),
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

        $this->renderSoftware('employee_history/create', [
            'pageTitle'             => 'افزودن رکورد تاریخچه',
            'softwareName'          => 'HR Analyzer',
            'hr_employees'          => $empModel->getSelectList(),
            'preselectedEmployeeId' => $preselectedEmployeeId,
            'changeTypes'           => [
                'hire'              => 'استخدام',
                'promotion'         => 'ارتقاء',
                'transfer'          => 'انتقال',
                'salary_change'     => 'تغییر حقوق',
                'position_change'   => 'تغییر پست',
                'department_change' => 'تغییر دپارتمان',
                'contract_renewal'  => 'تمدید قرارداد',
                'status_change'     => 'تغییر وضعیت',
                'termination'       => 'خاتمه همکاری',
                'retirement'        => 'بازنشستگی',
                'other'             => 'سایر',
            ],
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
            $this->redirect(hr_url('employee'));
            return;
        }

        $this->verifyCsrf();

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $changeType  = trim($_POST['change_type'] ?? 'other');
        $changeDate  = trim($_POST['change_date'] ?? '');
        $fromValue   = trim($_POST['from_value'] ?? '');
        $toValue     = trim($_POST['to_value'] ?? '');
        $reason      = trim($_POST['reason'] ?? '');
        $documentRef = trim($_POST['document_ref'] ?? '');

        if ($employeeId === 0 || empty($changeDate)) {
            hr_flash_set('danger', 'کارمند و تاریخ تغییر الزامی است.');
            $this->redirect(hr_url('employee_history', 'create', ['employee_id' => $employeeId]));
            return;
        }

        $changeDateG = DateHelper::toGregorian($changeDate);
        if (!$changeDateG) {
            hr_flash_set('danger', 'تاریخ تغییر نامعتبر است.');
            $this->redirect(hr_url('employee_history', 'create', ['employee_id' => $employeeId]));
            return;
        }

        $model = new EmployeeHistory();
        $model->record(
            $employeeId,
            $changeType,
            $changeDateG,
            $fromValue ?: null,
            $toValue ?: null,
            $reason ?: null,
            $documentRef ?: null
        );

        hr_flash_set('success', 'رکورد تاریخچه با موفقیت ثبت شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeHistory();
        $history = $model->find($id);

        if (!$history) {
            hr_flash_set('danger', 'رکورد یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('employee_history/edit', [
            'pageTitle'    => 'ویرایش رکورد تاریخچه',
            'softwareName' => 'HR Analyzer',
            'history'      => $history,
            'hr_employees' => $empModel->getSelectList(),
            'changeTypes'  => [
                'hire' => 'استخدام', 'promotion' => 'ارتقاء', 'transfer' => 'انتقال',
                'salary_change' => 'تغییر حقوق', 'position_change' => 'تغییر پست',
                'department_change' => 'تغییر دپارتمان', 'contract_renewal' => 'تمدید قرارداد',
                'status_change' => 'تغییر وضعیت', 'termination' => 'خاتمه همکاری',
                'retirement' => 'بازنشستگی', 'other' => 'سایر',
            ],
            'flash'        => hr_flash_get(),
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
        $model = new EmployeeHistory();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'رکورد یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $changeType  = trim($_POST['change_type'] ?? 'other');
        $changeDate  = trim($_POST['change_date'] ?? '');
        $fromValue   = trim($_POST['from_value'] ?? '');
        $toValue     = trim($_POST['to_value'] ?? '');
        $reason      = trim($_POST['reason'] ?? '');
        $documentRef = trim($_POST['document_ref'] ?? '');

        if ($employeeId === 0 || empty($changeDate)) {
            hr_flash_set('danger', 'کارمند و تاریخ تغییر الزامی است.');
            $this->redirect(hr_url('employee_history', 'edit', ['id' => $id]));
            return;
        }

        $changeDateG = DateHelper::toGregorian($changeDate);

        $model->update($id, [
            'employee_id'  => $employeeId,
            'change_type'  => $changeType,
            'change_date'  => $changeDateG,
            'from_value'   => $fromValue ?: null,
            'to_value'     => $toValue ?: null,
            'reason'       => $reason ?: null,
            'document_ref' => $documentRef ?: null,
        ]);

        hr_flash_set('success', 'رکورد تاریخچه به‌روزرسانی شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeHistory();
        $history = $model->find($id);

        if (!$history) {
            hr_flash_set('danger', 'رکورد یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $employeeId = (int) $history['employee_id'];
        $model->delete($id);

        hr_flash_set('success', 'رکورد تاریخچه حذف شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }
}