<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\MaintenancePlan;
use App\Software\Pdm\Models\MaintenanceType;
use App\Software\Pdm\Models\Asset;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * MaintenanceController - مدیریت برنامه‌های نگهداری پیشگیرانه
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/MaintenanceController.php
 * ============================================================
 */
class MaintenanceController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست برنامه‌های نگهداری
     */
    public function index()
    {
        $this->requireAuth();

        $model = new MaintenancePlan();
        $plans = $model->getAllWithDetails();

        $stats = [
            'total'    => $model->count(),
            'active'   => $model->count("status = 'active'"),
            'inactive' => $model->count("status = 'inactive'"),
            'due'      => count($model->getDuePlans()),
        ];

        // برنامه‌های سررسید شده
        $duePlans = $model->getDuePlans();

        $this->renderSoftware('maintenance/index', [
            'pageTitle'    => 'برنامه‌های نگهداری پیشگیرانه',
            'softwareName' => 'PdM Analyzer',
            'plans'        => $plans,
            'stats'        => $stats,
            'duePlans'     => $duePlans,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد برنامه
     */
    public function create()
    {
        $this->requireAuth();

        $assetModel = new Asset();
        $typeModel  = new MaintenanceType();

        $this->renderSoftware('maintenance/create', [
            'pageTitle'    => 'افزودن برنامه نگهداری',
            'softwareName' => 'PdM Analyzer',
            'pdm_assets'   => $assetModel->getSelectList(),
            'pdm_types'    => $typeModel->getSelectList(),
            'frequencyUnits' => MaintenancePlan::getFrequencyUnits(),
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره برنامه
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('maintenance'));
            return;
        }

        $this->verifyCsrf();

        $assetId   = (int) ($_POST['asset_id'] ?? 0);
        $typeId    = (int) ($_POST['maintenance_type_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $freqValue = (int) ($_POST['frequency_value'] ?? 0);
        $freqUnit  = trim($_POST['frequency_unit'] ?? 'days');
        $startDateJalali = trim($_POST['start_date'] ?? '');
        $priority  = trim($_POST['priority'] ?? 'normal');
        $status    = trim($_POST['status'] ?? 'active');

        // اعتبارسنجی
        if (empty($title) || $assetId === 0 || $typeId === 0 || $freqValue <= 0) {
            pdm_flash_set('danger', 'عنوان، دارایی، نوع نگهداری و فرکانس الزامی است.');
            $this->redirect(pdm_url('maintenance', 'create'));
            return;
        }

        // تبدیل تاریخ شمسی → میلادی
        $startDateGregorian = null;
        if (!empty($startDateJalali)) {
            $startDateGregorian = DateHelper::toGregorian($startDateJalali);
            if ($startDateGregorian === null) {
                pdm_flash_set('danger', 'تاریخ شروع نامعتبر است.');
                $this->redirect(pdm_url('maintenance', 'create'));
                return;
            }
        } else {
            $startDateGregorian = date('Y-m-d');
        }

        // محاسبه تاریخ اجرای بعدی
        $model = new MaintenancePlan();
        $nextExecution = $model->calculateNextExecution($startDateGregorian, $freqValue, $freqUnit);

        $model->create([
            'asset_id'            => $assetId,
            'maintenance_type_id' => $typeId,
            'title'               => $title,
            'description'         => $desc ?: null,
            'frequency_value'     => $freqValue,
            'frequency_unit'      => $freqUnit,
            'start_date'          => $startDateGregorian,
            'next_execution'      => $nextExecution,
            'priority'            => $priority,
            'status'              => $status,
        ]);

        pdm_flash_set('success', 'برنامه نگهداری با موفقیت ثبت شد.');
        $this->redirect(pdm_url('maintenance'));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new MaintenancePlan();
        $plan = $model->findWithDetails($id);

        if (!$plan) {
            pdm_flash_set('danger', 'برنامه یافت نشد.');
            $this->redirect(pdm_url('maintenance'));
            return;
        }

        $assetModel = new Asset();
        $typeModel  = new MaintenanceType();

        $this->renderSoftware('maintenance/edit', [
            'pageTitle'    => 'ویرایش برنامه نگهداری',
            'softwareName' => 'PdM Analyzer',
            'plan'         => $plan,
            'pdm_assets'   => $assetModel->getSelectList(),
            'pdm_types'    => $typeModel->getSelectList(),
            'frequencyUnits' => MaintenancePlan::getFrequencyUnits(),
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی برنامه
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('maintenance'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new MaintenancePlan();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'برنامه یافت نشد.');
            $this->redirect(pdm_url('maintenance'));
            return;
        }

        $assetId   = (int) ($_POST['asset_id'] ?? 0);
        $typeId    = (int) ($_POST['maintenance_type_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $freqValue = (int) ($_POST['frequency_value'] ?? 0);
        $freqUnit  = trim($_POST['frequency_unit'] ?? 'days');
        $startDateJalali = trim($_POST['start_date'] ?? '');
        $nextDateJalali  = trim($_POST['next_execution'] ?? '');
        $priority  = trim($_POST['priority'] ?? 'normal');
        $status    = trim($_POST['status'] ?? 'active');

        if (empty($title) || $assetId === 0 || $typeId === 0 || $freqValue <= 0) {
            pdm_flash_set('danger', 'عنوان، دارایی، نوع نگهداری و فرکانس الزامی است.');
            $this->redirect(pdm_url('maintenance', 'edit', ['id' => $id]));
            return;
        }

        // تبدیل تاریخ‌ها
        $startDateGregorian = null;
        if (!empty($startDateJalali)) {
            $startDateGregorian = DateHelper::toGregorian($startDateJalali);
        }

        $nextExecutionGregorian = null;
        if (!empty($nextDateJalali)) {
            $nextExecutionGregorian = DateHelper::toGregorian($nextDateJalali);
        }

        $model->update($id, [
            'asset_id'            => $assetId,
            'maintenance_type_id' => $typeId,
            'title'               => $title,
            'description'         => $desc ?: null,
            'frequency_value'     => $freqValue,
            'frequency_unit'      => $freqUnit,
            'start_date'          => $startDateGregorian,
            'next_execution'      => $nextExecutionGregorian,
            'priority'            => $priority,
            'status'              => $status,
        ]);

        pdm_flash_set('success', 'برنامه نگهداری به‌روزرسانی شد.');
        $this->redirect(pdm_url('maintenance'));
    }

    /**
     * حذف برنامه
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new MaintenancePlan();

        $plan = $model->find($id);
        if (!$plan) {
            pdm_flash_set('danger', 'برنامه یافت نشد.');
            $this->redirect(pdm_url('maintenance'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'برنامه نگهداری حذف شد.');
        $this->redirect(pdm_url('maintenance'));
    }
}