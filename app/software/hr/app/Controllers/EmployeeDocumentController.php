<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\EmployeeDocument;
use App\Software\Hr\Models\Employee;
use App\Software\Hr\Helpers\FileUploader;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * EmployeeDocumentController - مدیریت اسناد پرسنلی
 * ============================================================
 */
class EmployeeDocumentController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست همه اسناد
     */
    public function index()
    {
        $this->requireAuth();

        $model = new EmployeeDocument();
        $employeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null;

        if ($employeeId) {
            $documents = $model->getByEmployee($employeeId);
            $empModel = new Employee();
            $employee = $empModel->find($employeeId);
        } else {
            $stmt = $this->db->prepare(
                "SELECT d.*, 
                        CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                        e.employee_code
                 FROM hr_employee_documents d
                 LEFT JOIN hr_employees e ON d.employee_id = e.id
                 WHERE d.system_id = ?
                 ORDER BY d.created_at DESC"
            );
            $stmt->execute([hr_active_system_id()]);
            $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $employee = null;
        }

        $this->renderSoftware('employee_document/index', [
            'pageTitle'    => 'اسناد پرسنلی',
            'softwareName' => 'HR Analyzer',
            'documents'    => $documents,
            'employee'     => $employee,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد سند
     */
    public function create()
    {
        $this->requireAuth();

        $empModel = new Employee();
        $preselectedEmployeeId = !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null;

        $this->renderSoftware('employee_document/create', [
            'pageTitle'             => 'افزودن سند پرسنلی',
            'softwareName'          => 'HR Analyzer',
            'hr_employees'          => $empModel->getSelectList(),
            'preselectedEmployeeId' => $preselectedEmployeeId,
            'documentTypes'         => EmployeeDocument::getDocumentTypes(),
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره سند (با آپلود فایل)
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('employee_document'));
            return;
        }

        $this->verifyCsrf();

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $docType     = trim($_POST['document_type'] ?? 'other');
        $title       = trim($_POST['title'] ?? '');
        $issueDate   = trim($_POST['issue_date'] ?? '');
        $expiryDate  = trim($_POST['expiry_date'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($employeeId === 0 || empty($title)) {
            hr_flash_set('danger', 'کارمند و عنوان سند الزامی است.');
            $this->redirect(hr_url('employee_document', 'create', ['employee_id' => $employeeId]));
            return;
        }

        // آپلود فایل (اگر ارسال شده)
        $fileData = null;
        if (!empty($_FILES['document_file']['name'])) {
            $uploadResult = FileUploader::upload(
                $_FILES['document_file'],
                hr_active_system_id(),
                $employeeId
            );

            if (!$uploadResult['success']) {
                hr_flash_set('danger', 'خطا در آپلود فایل: ' . $uploadResult['error']);
                $this->redirect(hr_url('employee_document', 'create', ['employee_id' => $employeeId]));
                return;
            }

            $fileData = $uploadResult;
        }

        // تبدیل تاریخ شمسی → میلادی
        $issueDateG = $issueDate ? DateHelper::toGregorian($issueDate) : null;
        $expiryDateG = $expiryDate ? DateHelper::toGregorian($expiryDate) : null;

        $model = new EmployeeDocument();
        $newId = $model->create([
            'employee_id'   => $employeeId,
            'document_type' => $docType,
            'title'         => $title,
            'file_name'     => $fileData['file_name'] ?? null,
            'file_path'     => $fileData['relative_path'] ?? null,
            'file_type'     => $fileData['file_type'] ?? null,
            'file_size'     => $fileData['file_size'] ?? null,
            'issue_date'    => $issueDateG,
            'expiry_date'   => $expiryDateG,
            'description'   => $description ?: null,
            'uploaded_by'   => $_SESSION['user_id'] ?? null,
        ]);

        hr_flash_set('success', 'سند با موفقیت ثبت شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * دانلود امن فایل
     */
    public function download($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeDocument();
        $document = $model->find($id);

        if (!$document) {
            hr_flash_set('danger', 'سند یافت نشد.');
            $this->redirect(hr_url('employee_document'));
            return;
        }

        if (empty($document['file_path'])) {
            hr_flash_set('danger', 'فایلی برای این سند ثبت نشده است.');
            $this->redirect(hr_url('employee', 'show', ['id' => $document['employee_id']]));
            return;
        }

        // بررسی امنیتی: فایل باید در پوشه امن باشد
        $fullPath = FileUploader::getFullPath($document['file_path']);

        if ($fullPath === null) {
            hr_flash_set('danger', 'فایل مورد نظر یافت نشد.');
            $this->redirect(hr_url('employee', 'show', ['id' => $document['employee_id']]));
            return;
        }

        // تنظیم هدرهای دانلود
        $fileName = $document['file_name'] ?? basename($fullPath);
        $fileSize = filesize($fullPath);
        $mimeType = $document['file_type'] ?: 'application/octet-stream';

        // پاک کردن هر output قبلی
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . rawurlencode($fileName) . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($fullPath);
        exit;
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeDocument();
        $document = $model->find($id);

        if (!$document) {
            hr_flash_set('danger', 'سند یافت نشد.');
            $this->redirect(hr_url('employee_document'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('employee_document/edit', [
            'pageTitle'     => 'ویرایش سند',
            'softwareName'  => 'HR Analyzer',
            'document'      => $document,
            'hr_employees'  => $empModel->getSelectList(),
            'documentTypes' => EmployeeDocument::getDocumentTypes(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی (با امکان جایگزینی فایل)
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('employee_document'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new EmployeeDocument();
        $document = $model->find($id);

        if (!$document) {
            hr_flash_set('danger', 'سند یافت نشد.');
            $this->redirect(hr_url('employee_document'));
            return;
        }

        $employeeId  = (int) ($_POST['employee_id'] ?? 0);
        $docType     = trim($_POST['document_type'] ?? 'other');
        $title       = trim($_POST['title'] ?? '');
        $issueDate   = trim($_POST['issue_date'] ?? '');
        $expiryDate  = trim($_POST['expiry_date'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($employeeId === 0 || empty($title)) {
            hr_flash_set('danger', 'کارمند و عنوان سند الزامی است.');
            $this->redirect(hr_url('employee_document', 'edit', ['id' => $id]));
            return;
        }

        // آپلود فایل جدید (اگر ارسال شده)
        $fileData = [
            'file_name' => $document['file_name'],
            'file_path' => $document['file_path'],
            'file_type' => $document['file_type'],
            'file_size' => $document['file_size'],
        ];

        if (!empty($_FILES['document_file']['name'])) {
            // حذف فایل قدیمی
            if (!empty($document['file_path'])) {
                FileUploader::delete($document['file_path']);
            }

            $uploadResult = FileUploader::upload(
                $_FILES['document_file'],
                hr_active_system_id(),
                $employeeId
            );

            if (!$uploadResult['success']) {
                hr_flash_set('danger', 'خطا در آپلود فایل: ' . $uploadResult['error']);
                $this->redirect(hr_url('employee_document', 'edit', ['id' => $id]));
                return;
            }

            $fileData = [
                'file_name' => $uploadResult['file_name'],
                'file_path' => $uploadResult['relative_path'],
                'file_type' => $uploadResult['file_type'],
                'file_size' => $uploadResult['file_size'],
            ];
        }

        $issueDateG = $issueDate ? DateHelper::toGregorian($issueDate) : null;
        $expiryDateG = $expiryDate ? DateHelper::toGregorian($expiryDate) : null;

        $model->update($id, [
            'employee_id'   => $employeeId,
            'document_type' => $docType,
            'title'         => $title,
            'file_name'     => $fileData['file_name'],
            'file_path'     => $fileData['file_path'],
            'file_type'     => $fileData['file_type'],
            'file_size'     => $fileData['file_size'],
            'issue_date'    => $issueDateG,
            'expiry_date'   => $expiryDateG,
            'description'   => $description ?: null,
        ]);

        hr_flash_set('success', 'سند با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }

    /**
     * حذف (با پاک کردن فایل فیزیکی)
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new EmployeeDocument();
        $document = $model->find($id);

        if (!$document) {
            hr_flash_set('danger', 'سند یافت نشد.');
            $this->redirect(hr_url('employee_document'));
            return;
        }

        $employeeId = (int) $document['employee_id'];

        // حذف فایل فیزیکی
        if (!empty($document['file_path'])) {
            FileUploader::delete($document['file_path']);
        }

        // حذف رکورد دیتابیس
        $model->delete($id);

        hr_flash_set('success', 'سند و فایل مربوطه حذف شد.');
        $this->redirect(hr_url('employee', 'show', ['id' => $employeeId]));
    }
}