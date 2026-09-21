<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\KPI;
use App\Software\Pdm\Models\Asset;
use App\Software\Pdm\Models\WorkOrder;

/**
 * ============================================================
 * KPIController - مدیریت شاخص‌های کلیدی (CRUD + محاسبه)
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/KPIController.php
 * ============================================================
 */
class KPIController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * صفحه اصلی: نمایش کارت‌های KPI + جدول
     */
    public function index()
    {
        $this->requireAuth();

        $kpiModel = new KPI();
        $workOrderModel = new WorkOrder();
        $assetModel = new Asset();

        // همه KPIها (فعال و غیرفعال) برای جدول
        $allKPIs = $kpiModel->all();

        // مقادیر محاسبه‌شده
        $kpiValues = $kpiModel->calculateAll();

        // KPIهای فعال (برای کارت‌ها)
        $activeKPIs = $kpiModel->getActiveWithValues();

        // آمار پایه
        $baseStats = [
            'total_assets'      => $assetModel->count(),
            'total_work_orders' => $workOrderModel->count(),
            'completed_wos'     => $workOrderModel->count("status = 'completed'"),
            'open_wos'          => $workOrderModel->count("status IN ('open', 'in_progress')"),
        ];

        $this->renderSoftware('kpi/index', [
            'pageTitle'      => 'شاخص‌های کلیدی (KPI)',
            'softwareName'   => 'PdM Analyzer',
            'allKPIs'        => $allKPIs,
            'activeKPIs'     => $activeKPIs,
            'kpiValues'      => $kpiValues,
            'baseStats'      => $baseStats,
            'flash'          => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد KPI جدید
     */
    public function create()
    {
        $this->requireAuth();

        $this->renderSoftware('kpi/create', [
            'pageTitle'    => 'افزودن شاخص جدید',
            'softwareName' => 'PdM Analyzer',
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره KPI جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $this->verifyCsrf();

        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $formula     = trim($_POST['formula'] ?? '');
        $unit        = trim($_POST['unit'] ?? '');
        $color       = trim($_POST['color'] ?? '#0F766E');
        $icon        = trim($_POST['icon'] ?? 'fas fa-chart-line');
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);

        if (empty($code) || empty($name)) {
            pdm_flash_set('danger', 'کد و نام شاخص الزامی است.');
            $this->redirect(pdm_url('kpi', 'create'));
            return;
        }

        // اعتبارسنجی فرمت کد (فقط حروف، اعداد و _)
        if (!preg_match('/^[A-Z0-9_]+$/', $code)) {
            pdm_flash_set('danger', 'کد شاخص فقط می‌تواند شامل حروف انگلیسی بزرگ، اعداد و _ باشد.');
            $this->redirect(pdm_url('kpi', 'create'));
            return;
        }

        $model = new KPI();

        if ($model->codeExists($code)) {
            pdm_flash_set('danger', 'کد شاخص تکراری است.');
            $this->redirect(pdm_url('kpi', 'create'));
            return;
        }

        $model->create([
            'code'        => $code,
            'name'        => $name,
            'description' => $description ?: null,
            'formula'     => $formula ?: null,
            'unit'        => $unit ?: null,
            'kpi_type'    => 'custom',
            'is_builtin'  => 0,
            'is_active'   => 1,
            'sort_order'  => $sortOrder,
            'color'       => $color,
            'icon'        => $icon,
        ]);

        pdm_flash_set('success', 'شاخص با موفقیت ثبت شد.');
        $this->redirect(pdm_url('kpi'));
    }

    /**
     * فرم ویرایش KPI
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            pdm_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $this->renderSoftware('kpi/edit', [
            'pageTitle'    => 'ویرایش شاخص',
            'softwareName' => 'PdM Analyzer',
            'kpi'          => $kpi,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی KPI
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            pdm_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'unit'        => trim($_POST['unit'] ?? '') ?: null,
            'color'       => trim($_POST['color'] ?? '#0F766E'),
            'icon'        => trim($_POST['icon'] ?? 'fas fa-chart-line'),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ];

        // فرمول و کد فقط برای KPIهای غیر builtin قابل تغییر است
        if ((int) $kpi['is_builtin'] !== 1) {
            $data['code']    = strtoupper(trim($_POST['code'] ?? ''));
            $data['formula'] = trim($_POST['formula'] ?? '') ?: null;

            if (empty($data['code'])) {
                pdm_flash_set('danger', 'کد شاخص الزامی است.');
                $this->redirect(pdm_url('kpi', 'edit', ['id' => $id]));
                return;
            }

            if (!preg_match('/^[A-Z0-9_]+$/', $data['code'])) {
                pdm_flash_set('danger', 'کد شاخص فقط می‌تواند شامل حروف انگلیسی بزرگ، اعداد و _ باشد.');
                $this->redirect(pdm_url('kpi', 'edit', ['id' => $id]));
                return;
            }

            if ($model->codeExists($data['code'], $id)) {
                pdm_flash_set('danger', 'کد شاخص تکراری است.');
                $this->redirect(pdm_url('kpi', 'edit', ['id' => $id]));
                return;
            }
        }

        if (empty($data['name'])) {
            pdm_flash_set('danger', 'نام شاخص الزامی است.');
            $this->redirect(pdm_url('kpi', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, $data);

        pdm_flash_set('success', 'شاخص به‌روزرسانی شد.');
        $this->redirect(pdm_url('kpi'));
    }

    /**
     * فعال/غیرفعال کردن KPI
     */
    public function toggle($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            pdm_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $newState = (int) $kpi['is_active'] === 1 ? 0 : 1;
        $model->update($id, ['is_active' => $newState]);

        pdm_flash_set('success', $newState === 1 ? 'شاخص فعال شد.' : 'شاخص غیرفعال شد.');
        $this->redirect(pdm_url('kpi'));
    }

    /**
     * حذف KPI (فقط KPIهای غیر builtin)
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            pdm_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(pdm_url('kpi'));
            return;
        }

        if ((int) $kpi['is_builtin'] === 1) {
            pdm_flash_set('danger', 'شاخص‌های داخلی سیستم قابل حذف نیستند. می‌توانید آن‌ها را غیرفعال کنید.');
            $this->redirect(pdm_url('kpi'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'شاخص حذف شد.');
        $this->redirect(pdm_url('kpi'));
    }

    /**
     * بازنشانی KPIهای داخلی (اگر گم شده باشند)
     */
    public function seedBuiltin()
    {
        $this->requireAuth();

        $model = new KPI();
        $builtins = KPI::getBuiltinKPIs();

        $added = 0;
        foreach ($builtins as $kpi) {
            if (!$model->codeExists($kpi['code'])) {
                $model->create($kpi);
                $added++;
            }
        }

        if ($added > 0) {
            pdm_flash_set('success', $added . ' شاخص داخلی اضافه شد.');
        } else {
            pdm_flash_set('info', 'همه شاخص‌های داخلی از قبل وجود دارند.');
        }

        $this->redirect(pdm_url('kpi'));
    }
}