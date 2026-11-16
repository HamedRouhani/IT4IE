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

        $inventoryContext = $this->inventoryContext();
        $availableParts = $inventoryContext
            ? $this->getInventorySpareParts($inventoryContext['workspace_id'])
            : [];
        $issuedMaterials = $this->getWorkOrderInventoryIssues($id);

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
            'availableParts' => $availableParts,
            'issuedMaterials' => $issuedMaterials,
            'inventoryAvailable' => $inventoryContext !== null,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /** Issue a linked spare part from Inventory and record the matching stock movement atomically. */
    public function issueMaterial($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('workorder', 'show', ['id' => (int) $id]));
            return;
        }
        $this->verifyCsrf();

        $workOrderId = (int) $id;
        $sparePartId = (int) ($_POST['spare_part_id'] ?? 0);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_FLOAT);
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $systemId = (int) (pdm_active_system_id() ?? 0);
        $context = $this->inventoryContext();
        if (!$context || $userId <= 0 || $systemId <= 0 || $sparePartId <= 0 || $quantity === false || !is_finite((float) $quantity) || $quantity <= 0) {
            pdm_flash_set('danger', 'ارتباط انبار یا اطلاعات مصرف قطعه معتبر نیست.');
            $this->redirect(pdm_url('workorder', 'show', ['id' => $workOrderId]));
            return;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT id FROM pm_work_orders WHERE id=? AND system_id=? AND status IN ('open','in_progress') FOR UPDATE");
            $stmt->execute([$workOrderId, $systemId]);
            if (!$stmt->fetchColumn()) throw new \RuntimeException('فقط برای دستورکار باز یا در حال انجام می‌توان قطعه ثبت کرد.');

            // A strict code/SKU match is the link. Scope both sides to the current user's PdM system and inventory workspace.
            $stmt = $this->db->prepare(
                'SELECT sp.id AS spare_part_id, sp.code, sp.name AS spare_part_name,
                        i.id AS item_id, i.sku, i.name AS item_name, i.unit, i.on_hand
                 FROM pm_spare_parts sp
                 JOIN inv_items i ON i.sku=sp.code AND i.workspace_id=? AND i.is_active=1
                 WHERE sp.id=? AND sp.system_id=? FOR UPDATE'
            );
            $stmt->execute([$context['workspace_id'], $sparePartId, $systemId]);
            $part = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$part) throw new \RuntimeException('قطعه به قلم فعالی با SKU یکسان در انبار متصل نیست. کد قطعه و SKU باید دقیقاً برابر باشند.');
            if ((float) $part['on_hand'] + 0.000001 < (float) $quantity) throw new \RuntimeException('موجودی انبار برای این مقدار کافی نیست.');

            $balance = max(0.0, (float) $part['on_hand'] - (float) $quantity);
            $stmt = $this->db->prepare('UPDATE inv_items SET on_hand=? WHERE id=? AND workspace_id=? AND is_active=1');
            $stmt->execute([$balance, $part['item_id'], $context['workspace_id']]);
            $note = 'PdM work order #' . $workOrderId . ' | ' . (string) ($_POST['note'] ?? '');
            $stmt = $this->db->prepare("INSERT INTO inv_movements (workspace_id,item_id,user_id,movement_type,quantity,balance_after,note) VALUES (?,?,?,'issue',?,?,?)");
            $stmt->execute([$context['workspace_id'], $part['item_id'], $userId, -(float) $quantity, $balance, mb_substr($note, 0, 500)]);
            $movementId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                'INSERT INTO pm_work_order_inventory_issues
                 (system_id,work_order_id,spare_part_id,workspace_id,inventory_item_id,inventory_movement_id,quantity,issued_by)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$systemId, $workOrderId, $sparePartId, $context['workspace_id'], $part['item_id'], $movementId, $quantity, $userId]);
            $this->db->commit();
            pdm_flash_set('success', 'مصرف قطعه ثبت شد و موجودی انبار به‌روزرسانی شد.');
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('PdM inventory issue failed: ' . $e->getMessage());
            pdm_flash_set('danger', $e instanceof \RuntimeException ? $e->getMessage() : 'ثبت مصرف انجام نشد؛ هیچ تغییری در موجودی ذخیره نشد.');
        }
        $this->redirect(pdm_url('workorder', 'show', ['id' => $workOrderId]));
    }

    private function inventoryContext(): ?array
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $systemId = (int) (pdm_active_system_id() ?? 0);
        if ($userId <= 0 || $systemId <= 0) return null;
        $stmt = $this->db->prepare('SELECT id FROM pm_systems WHERE id=? AND owner_user_id=? LIMIT 1');
        $stmt->execute([$systemId, $userId]);
        if (!$stmt->fetchColumn()) return null;
        $stmt = $this->db->prepare('SELECT id FROM inv_workspaces WHERE user_id=? LIMIT 1');
        $stmt->execute([$userId]);
        $workspaceId = $stmt->fetchColumn();
        return $workspaceId ? ['workspace_id' => (int) $workspaceId] : null;
    }

    private function getInventorySpareParts(int $workspaceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT sp.id,sp.code,sp.name,sp.unit,i.on_hand,i.sku
             FROM pm_spare_parts sp JOIN inv_items i ON i.sku=sp.code AND i.workspace_id=? AND i.is_active=1
             WHERE sp.system_id=? ORDER BY sp.name'
        );
        $stmt->execute([$workspaceId, (int) pdm_active_system_id()]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getWorkOrderInventoryIssues(int $workOrderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT x.quantity,x.created_at,sp.code,sp.name AS spare_part_name,i.sku,i.name AS item_name,i.unit
             FROM pm_work_order_inventory_issues x
             JOIN pm_spare_parts sp ON sp.id=x.spare_part_id AND sp.system_id=x.system_id
             JOIN inv_items i ON i.id=x.inventory_item_id AND i.workspace_id=x.workspace_id
             WHERE x.system_id=? AND x.work_order_id=? ORDER BY x.created_at DESC,x.id DESC'
        );
        $stmt->execute([(int) pdm_active_system_id(), $workOrderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
