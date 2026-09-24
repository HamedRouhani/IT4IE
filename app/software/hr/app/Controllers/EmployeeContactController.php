<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\EmployeeContact;
use App\Software\Hr\Models\Employee;

/**
 * ============================================================
 * EmployeeContactController - مدیریت تماس‌های اضطراری
 * ============================================================
 */
class EmployeeContactController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $empModel = new Employee();
        $preselectedEmployeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null;

        $this->renderSoftware('employee_contact/create', [
            'pageTitle'             => 'افزودن تماس اضطراری',
            'softwareName'          => 'HR Analyzer',
            'hr_employees'          => $empModel->getSelectList(),
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
            $this->redirect(hr_url('employee'));
            return;
        }

        $this->verifyCsrf();

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $contactName = trim($_POST['contact_name'] ?? '');
        $relation    = trim($_POST['relation'] ?? '');
        $mobile      = trim($_POST['mobile'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $isPrimary   = !empty($_POST['is_primary']) ? 1 : 0;

        if ($employeeId === 0 || empty($contactName)) {
            hr_flash_set('danger', 'کارمند و نام مخاطب الزامی است.');
            $this->redirect(hr_url('employee_contact', 'create', ['employee_id' => $employeeId]));
            return;
        }

        $model = new EmployeeContact();

        // اگر is_primary، بقیه را غیرفعال کن
        if ($isPrimary) {
            $stmt = $this->db->prepare(
                "UPDATE hr_employee_contacts SET is_primary = 0 
                 WHERE employee_id = ? AND system_id = ?"
            );
            $stmt->execute([$employeeId, hr_active_system_id()]);
        }

        $model->create([
            'employee_id'  => $employeeId,
            'contact_name' => $contactName,
            'relation'     => $relation ?: null,
            'mobile'       => $mobile ?: null,
            'phone'        => $phone ?: null,
            'address'      => $address ?: null,
            'is_primary'   => $isPrimary,
        ]);

        hr_flash_set('success', 'تماس اضطراری با موفقیت ثبت شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeContact();
        $contact = $model->find($id);

        if (!$contact) {
            hr_flash_set('danger', 'تماس یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('employee_contact/edit', [
            'pageTitle'    => 'ویرایش تماس اضطراری',
            'softwareName' => 'HR Analyzer',
            'contact'      => $contact,
            'hr_employees' => $empModel->getSelectList(),
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
        $model = new EmployeeContact();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'تماس یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $contactName = trim($_POST['contact_name'] ?? '');
        $relation    = trim($_POST['relation'] ?? '');
        $mobile      = trim($_POST['mobile'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $isPrimary   = !empty($_POST['is_primary']) ? 1 : 0;

        if ($employeeId === 0 || empty($contactName)) {
            hr_flash_set('danger', 'کارمند و نام مخاطب الزامی است.');
            $this->redirect(hr_url('employee_contact', 'edit', ['id' => $id]));
            return;
        }

        if ($isPrimary) {
            $stmt = $this->db->prepare(
                "UPDATE hr_employee_contacts SET is_primary = 0 
                 WHERE employee_id = ? AND system_id = ? AND id != ?"
            );
            $stmt->execute([$employeeId, hr_active_system_id(), $id]);
        }

        $model->update($id, [
            'employee_id'  => $employeeId,
            'contact_name' => $contactName,
            'relation'     => $relation ?: null,
            'mobile'       => $mobile ?: null,
            'phone'        => $phone ?: null,
            'address'      => $address ?: null,
            'is_primary'   => $isPrimary,
        ]);

        hr_flash_set('success', 'تماس اضطراری به‌روزرسانی شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeContact();
        $contact = $model->find($id);

        if (!$contact) {
            hr_flash_set('danger', 'تماس یافت نشد.');
            $this->redirect(hr_url('employee'));
            return;
        }

        $employeeId = (int) $contact['employee_id'];
        $model->delete($id);

        hr_flash_set('success', 'تماس اضطراری حذف شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }
}