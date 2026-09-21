<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\FailureMode;
use App\Software\Pdm\Models\Asset;

/**
 * ============================================================
 * FailureController - مدیریت حالات خرابی و FMEA
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/FailureController.php
 * ============================================================
 */
class FailureController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست حالات خرابی
     */
    public function index()
    {
        $this->requireAuth();

        $model = new FailureMode();
        $failureModes = $model->getAllWithDetails();

        $stats = [
            'total'    => $model->count(),
            'critical' => count(array_filter($failureModes, fn($fm) => $fm['rpn'] >= 200)),
            'high'     => count(array_filter($failureModes, fn($fm) => $fm['rpn'] >= 100 && $fm['rpn'] < 200)),
            'medium'   => count(array_filter($failureModes, fn($fm) => $fm['rpn'] >= 50 && $fm['rpn'] < 100)),
        ];

        $riskDistribution = $model->getRiskDistribution();

        $this->renderSoftware('failure/index', [
            'pageTitle'        => 'خرابی‌ها و تحلیل FMEA',
            'softwareName'     => 'PdM Analyzer',
            'failureModes'     => $failureModes,
            'stats'            => $stats,
            'riskDistribution' => $riskDistribution,
            'flash'            => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد حالت خرابی
     */
    public function create()
    {
        $this->requireAuth();

        $assetModel = new Asset();

        $preselectedAssetId = !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null;

        $this->renderSoftware('failure/create', [
            'pageTitle'          => 'افزودن حالت خرابی (FMEA)',
            'softwareName'       => 'PdM Analyzer',
            'pdm_assets'         => $assetModel->getSelectList(),
            'preselectedAssetId' => $preselectedAssetId,
            'flash'              => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره حالت خرابی
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('failure'));
            return;
        }

        $this->verifyCsrf();

        $assetId  = (int) ($_POST['asset_id'] ?? 0);
        $code     = trim($_POST['code'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $severity = (int) ($_POST['severity'] ?? 5);
        $occurrence = (int) ($_POST['occurrence'] ?? 5);
        $detection  = (int) ($_POST['detection'] ?? 5);
        $recommended = trim($_POST['recommended_action'] ?? '');

        if (empty($name) || $assetId === 0) {
            pdm_flash_set('danger', 'نام و دارایی الزامی است.');
            $this->redirect(pdm_url('failure', 'create'));
            return;
        }

        // اعتبارسنجی محدوده
        $severity   = max(1, min(10, $severity));
        $occurrence = max(1, min(10, $occurrence));
        $detection  = max(1, min(10, $detection));

        $model = new FailureMode();
        $model->create([
            'asset_id'           => $assetId,
            'code'               => $code ?: null,
            'name'               => $name,
            'description'        => $desc ?: null,
            'severity'           => $severity,
            'occurrence'         => $occurrence,
            'detection'          => $detection,
            'recommended_action' => $recommended ?: null,
        ]);

        pdm_flash_set('success', 'حالت خرابی با موفقیت ثبت شد.');
        $this->redirect(pdm_url('failure'));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new FailureMode();
        $fm = $model->findWithDetails($id);

        if (!$fm) {
            pdm_flash_set('danger', 'حالت خرابی یافت نشد.');
            $this->redirect(pdm_url('failure'));
            return;
        }

        $this->renderSoftware('failure/show', [
            'pageTitle'    => 'جزئیات حالت خرابی',
            'softwareName' => 'PdM Analyzer',
            'fm'           => $fm,
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
        $model = new FailureMode();
        $fm = $model->find($id);

        if (!$fm) {
            pdm_flash_set('danger', 'حالت خرابی یافت نشد.');
            $this->redirect(pdm_url('failure'));
            return;
        }

        $assetModel = new Asset();

        $this->renderSoftware('failure/edit', [
            'pageTitle'    => 'ویرایش حالت خرابی',
            'softwareName' => 'PdM Analyzer',
            'fm'           => $fm,
            'pdm_assets'   => $assetModel->getSelectList(),
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('failure'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new FailureMode();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'حالت خرابی یافت نشد.');
            $this->redirect(pdm_url('failure'));
            return;
        }

        $assetId  = (int) ($_POST['asset_id'] ?? 0);
        $code     = trim($_POST['code'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $severity = max(1, min(10, (int) ($_POST['severity'] ?? 5)));
        $occurrence = max(1, min(10, (int) ($_POST['occurrence'] ?? 5)));
        $detection  = max(1, min(10, (int) ($_POST['detection'] ?? 5)));
        $recommended = trim($_POST['recommended_action'] ?? '');

        if (empty($name) || $assetId === 0) {
            pdm_flash_set('danger', 'نام و دارایی الزامی است.');
            $this->redirect(pdm_url('failure', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'asset_id'           => $assetId,
            'code'               => $code ?: null,
            'name'               => $name,
            'description'        => $desc ?: null,
            'severity'           => $severity,
            'occurrence'         => $occurrence,
            'detection'          => $detection,
            'recommended_action' => $recommended ?: null,
        ]);

        pdm_flash_set('success', 'حالت خرابی به‌روزرسانی شد.');
        $this->redirect(pdm_url('failure', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new FailureMode();

        $fm = $model->find($id);
        if (!$fm) {
            pdm_flash_set('danger', 'حالت خرابی یافت نشد.');
            $this->redirect(pdm_url('failure'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'حالت خرابی حذف شد.');
        $this->redirect(pdm_url('failure'));
    }
}