<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\WorkOrder;
use App\Software\Pdm\Models\MaintenanceType;
use App\Software\Pdm\Models\MaintenancePlan;
use App\Software\Pdm\Models\Asset;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * WorkOrderController - مدیریت دستورکارها (قلب سیستم)
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/WorkOrderController.php
 * ============================================================
 */
class WorkOrderController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست دستورکارها با فیلتر
     */
    public function index()
    {
        $this->requireAuth();

        $model = new WorkOrder();

        $filters = [
            'q'                    => trim($_GET['q'] ?? ''),
            'status'               => trim($_GET['status'] ?? ''),
            'priority'             => trim($_GET['priority'] ?? ''),
            'asset_id'             => !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null,
            'maintenance_type_id'  => !empty($_GET['maintenance_type_id']) ? (int) $_GET['maintenance_type_id'] : null,
        ];

        $workOrders = $model->search($filters);

        $stats = [
            'total'       => $model->count(),
            'open'        => $model->count("status = 'open'"),
            'in_progress' => $model->count("status = 'in_progress'"),
            'completed'   => $model->count("status = 'completed'"),
            'urgent'      => $model->count("priority = 'urgent' AND status IN ('open', 'in_progress')"),
        ];

        $assetModel = new Asset();
        $typeModel  = new MaintenanceType();

        $this->renderSoftware('workorder/index', [
            'pageTitle'    => 'دستورکارها',
            'softwareName' => 'PdM Analyzer',
            'workOrders'   => $workOrders,
            'filters'      => $filters,
            'stats'        => $stats,
            'pdm_assets'   => $assetModel->getSelectList(),
            'pdm_types'    => $typeModel->getSelectList(),
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد دستورکار
     */
    public function create()
    {
        $this->requireAuth();

        $assetModel = new Asset();
        $typeModel  = new MaintenanceType();

        // اگر از صفحه دارایی آمده، asset_id از قبل انتخاب شود
        $preselectedAssetId = !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null;

        // اگر از صفحه برنامه آمده، برنامه انتخاب شود
        $preselectedPlanId = !empty($_GET['plan_id']) ? (int) $_GET['plan_id'] : null;

        $this->renderSoftware('workorder/create', [
            'pageTitle'           => 'ایجاد دستورکار جدید',
            'softwareName'        => 'PdM Analyzer',
            'pdm_assets'          => $assetModel->getSelectList(),
            'pdm_types'           => $typeModel->getSelectList(),
            'preselectedAssetId'  => $preselectedAssetId,
            'preselectedPlanId'   => $preselectedPlanId,
            'priorityOptions'     => WorkOrder::getPriorityOptions(),
            'flash'               => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره دستورکار
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $this->verifyCsrf();

        $assetId   = (int) ($_POST['asset_id'] ?? 0);
        $typeId    = (int) ($_POST['maintenance_type_id'] ?? 0);
        $planId    = !empty($_POST['maintenance_plan_id']) ? (int) $_POST['maintenance_plan_id'] : null;
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $priority  = trim($_POST['priority'] ?? 'normal');
        $plannedDateJalali = trim($_POST['planned_date'] ?? '');

        if (empty($title) || $assetId === 0) {
            pdm_flash_set('danger', 'عنوان و دارایی الزامی است.');
            $this->redirect(pdm_url('workorder', 'create'));
            return;
        }

        // تبدیل تاریخ
        $plannedDateGregorian = null;
        if (!empty($plannedDateJalali)) {
            $plannedDateGregorian = DateHelper::toGregorian($plannedDateJalali);
        }

        $model = new WorkOrder();
        $woNumber = $model->generateWoNumber();

        $newId = $model->create([
            'wo_number'            => $woNumber,
            'asset_id'             => $assetId,
            'maintenance_type_id'  => $typeId ?: null,
            'maintenance_plan_id'  => $planId,
            'title'                => $title,
            'description'          => $desc ?: null,
            'priority'             => $priority,
            'status'               => 'open',
            'planned_date'         => $plannedDateGregorian,
        ]);

        pdm_flash_set('success', 'دستورکار ' . $woNumber . ' با موفقیت ثبت شد.');
        $this->redirect(pdm_url('workorder', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات دستورکار
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new WorkOrder();
        $wo = $model->findWithDetails($id);

        if (!$wo) {
            pdm_flash_set('danger', 'دستورکار یافت نشد.');
            $this->redirect(pdm_url('workorder'));
            return;
        }

        // لاگ‌های دستورکار
        $stmt = $this->db->prepare(
            "SELECT * FROM pm_work_order_logs 
             WHERE work_order_id = ? 
             ORDER BY created_at DESC"
        );
        $stmt->execute([$id]);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // محاسبه مدت زمان انجام (اگر تکمیل شده)
        $duration = null;
        if (!empty($wo['started_at']) && !empty($wo['completed_at'])) {
            $duration = round(
                (strtotime($wo['completed_at']) - strtotime($wo['started_at'])) / 3600,
                2
            );
        }

        $this->renderSoftware('workorder/show', [
            'pageTitle'    => 'دستورکار ' . $wo['wo_number'],
            'softwareName' => 'PdM Analyzer',
            'wo'           => $wo,
            'logs'         => $logs,
            'duration'     => $duration,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new WorkOrder();
        $wo = $model->findWithDetails($id);

        if (!$wo) {
            pdm_flash_set('danger', 'دستورکار یافت نشد.');
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $assetModel = new Asset();
        $typeModel  = new MaintenanceType();

        $this->renderSoftware('workorder/edit', [
            'pageTitle'    => 'ویرایش دستورکار',
            'softwareName' => 'PdM Analyzer',
            'wo'           => $wo,
            'pdm_assets'   => $assetModel->getSelectList(),
            'pdm_types'    => $typeModel->getSelectList(),
            'priorityOptions' => WorkOrder::getPriorityOptions(),
            'statusOptions'   => WorkOrder::getStatusOptions(),
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی دستورکار
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new WorkOrder();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'دستورکار یافت نشد.');
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $assetId   = (int) ($_POST['asset_id'] ?? 0);
        $typeId    = (int) ($_POST['maintenance_type_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $priority  = trim($_POST['priority'] ?? 'normal');
        $status    = trim($_POST['status'] ?? 'open');
        $resolution = trim($_POST['resolution'] ?? '');
        $plannedDateJalali = trim($_POST['planned_date'] ?? '');

        if (empty($title) || $assetId === 0) {
            pdm_flash_set('danger', 'عنوان و دارایی الزامی است.');
            $this->redirect(pdm_url('workorder', 'edit', ['id' => $id]));
            return;
        }

        $plannedDateGregorian = null;
        if (!empty($plannedDateJalali)) {
            $plannedDateGregorian = DateHelper::toGregorian($plannedDateJalali);
        }

        $model->update($id, [
            'asset_id'             => $assetId,
            'maintenance_type_id'  => $typeId ?: null,
            'title'                => $title,
            'description'          => $desc ?: null,
            'priority'             => $priority,
            'status'               => $status,
            'planned_date'         => $plannedDateGregorian,
            'resolution'           => $resolution ?: null,
        ]);

        pdm_flash_set('success', 'دستورکار به‌روزرسانی شد.');
        $this->redirect(pdm_url('workorder', 'show', ['id' => $id]));
    }

    /**
     * تغییر وضعیت دستورکار (AJAX یا POST)
     */
    public function changeStatus($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $newStatus = trim($_POST['status'] ?? '');
        $resolution = trim($_POST['resolution'] ?? '');

        if (!in_array($newStatus, ['open', 'in_progress', 'completed', 'cancelled'])) {
            pdm_flash_set('danger', 'وضعیت نامعتبر است.');
            $this->redirect(pdm_url('workorder', 'show', ['id' => $id]));
            return;
        }

        $model = new WorkOrder();
        $model->changeStatus($id, $newStatus, $resolution ?: null);

        pdm_flash_set('success', 'وضعیت دستورکار تغییر کرد.');
        $this->redirect(pdm_url('workorder', 'show', ['id' => $id]));
    }

    /**
     * حذف دستورکار
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new WorkOrder();

        $wo = $model->find($id);
        if (!$wo) {
            pdm_flash_set('danger', 'دستورکار یافت نشد.');
            $this->redirect(pdm_url('workorder'));
            return;
        }

        // فقط دستورکارهای باز یا لغو شده قابل حذف هستند
        if (!in_array($wo['status'], ['open', 'cancelled'])) {
            pdm_flash_set('danger', 'فقط دستورکارهای باز یا لغو شده قابل حذف هستند.');
            $this->redirect(pdm_url('workorder'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'دستورکار حذف شد.');
        $this->redirect(pdm_url('workorder'));
    }
}