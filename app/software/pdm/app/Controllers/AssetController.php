<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\Asset;
use App\Software\Pdm\Models\Location;
use App\Software\Pdm\Models\AssetCategory;

/**
 * ============================================================
 * AssetController - مدیریت دارایی‌ها (تجهیزات)
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/AssetController.php
 * ============================================================
 */
class AssetController extends Controller
{
    /** @var \PDO */
    private $db;

    public function __construct()
    {
        // ⚠️ Controller والد __construct ندارد، پس parent::__construct() را صدا نمی‌زنیم
        $this->db = Database::getInstance();
    }

    /**
     * لیست دارایی‌ها با فیلتر و جستجو
     */
    public function index()
    {
        $this->requireAuth();

        $assetModel    = new Asset();
        $locationModel = new Location();
        $categoryModel = new AssetCategory();

        $filters = [
            'q'            => trim($_GET['q'] ?? ''),
            'status'       => trim($_GET['status'] ?? ''),
            'criticality'  => trim($_GET['criticality'] ?? ''),
            'location_id'  => !empty($_GET['location_id']) ? (int) $_GET['location_id'] : null,
            'category_id'  => !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null,
        ];

        $assets = $assetModel->search($filters);

        $stats = [
            'total'       => $assetModel->count(),
            'active'      => $assetModel->countByStatus('active'),
            'critical'    => $assetModel->countByCriticality('critical'),
            'maintenance' => $assetModel->countByStatus('maintenance'),
        ];

        // ⚠️ نام‌های اختصاصی برای جلوگیری از تداخل با layout مشترک
        $pdm_locations  = $locationModel->getFlatList();
        $pdm_categories = $categoryModel->getFlatList();

        $this->renderSoftware('asset/index', [
            'pageTitle'      => 'تجهیزات و دارایی‌ها',
            'softwareName'   => 'PdM Analyzer',
            'assets'         => $assets,
            'filters'        => $filters,
            'stats'          => $stats,
            'pdm_locations'  => $pdm_locations,
            'pdm_categories' => $pdm_categories,
            'flash'          => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد دارایی جدید
     */
    public function create()
    {
        $this->requireAuth();

        $locationModel = new Location();
        $categoryModel = new AssetCategory();
        $assetModel    = new Asset();

        $this->renderSoftware('asset/create', [
            'pageTitle'      => 'افزودن دارایی جدید',
            'softwareName'   => 'PdM Analyzer',
            'pdm_locations'  => $locationModel->getFlatList(),
            'pdm_categories' => $categoryModel->getFlatList(),
            'parentAssets'   => $assetModel->getSelectList(),
            'flash'          => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره دارایی جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('asset'));
            return;
        }

        $this->verifyCsrf();

        $data = $this->collectPostData();

        if (empty($data['name']) || empty($data['asset_code'])) {
            pdm_flash_set('danger', 'نام و کد دارایی الزامی است.');
            $this->redirect(pdm_url('asset', 'create'));
            return;
        }

        $assetModel = new Asset();

        if ($assetModel->codeExists($data['asset_code'])) {
            pdm_flash_set('danger', 'کد دارایی تکراری است.');
            $this->redirect(pdm_url('asset', 'create'));
            return;
        }

        $assetModel->create($data);

        pdm_flash_set('success', 'دارایی با موفقیت ثبت شد.');
        $this->redirect(pdm_url('asset'));
    }

    /**
     * نمایش جزئیات دارایی
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $assetModel = new Asset();
        $asset = $assetModel->findWithDetails($id);

        if (!$asset) {
            pdm_flash_set('danger', 'دارایی یافت نشد.');
            $this->redirect(pdm_url('asset'));
            return;
        }

        $systemId = pdm_active_system_id();

        // آخرین دستورکارهای این دارایی
        $stmt = $this->db->prepare(
            "SELECT wo.*, mt.name AS maintenance_type_name
             FROM pm_work_orders wo
             LEFT JOIN pm_maintenance_types mt ON wo.maintenance_type_id = mt.id
             WHERE wo.asset_id = ? AND wo.system_id = ?
             ORDER BY wo.created_at DESC
             LIMIT 10"
        );
        $stmt->execute([$id, $systemId]);
        $workOrders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // حالات خرابی این دارایی
        $stmt = $this->db->prepare(
            "SELECT * FROM pm_failure_modes 
             WHERE asset_id = ? AND system_id = ?
             ORDER BY id DESC"
        );
        $stmt->execute([$id, $systemId]);
        $failureModes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // قطعات یدکی مرتبط
        $stmt = $this->db->prepare(
            "SELECT asp.*, sp.code AS spare_code, sp.name AS spare_name, sp.stock_quantity
             FROM pm_asset_spare_parts asp
             LEFT JOIN pm_spare_parts sp ON asp.spare_part_id = sp.id
             WHERE asp.asset_id = ?
             ORDER BY sp.name ASC"
        );
        $stmt->execute([$id]);
        $spareParts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // اسناد
        $stmt = $this->db->prepare(
            "SELECT * FROM pm_asset_documents 
             WHERE asset_id = ? AND system_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$id, $systemId]);
        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stats = [
            'total_work_orders' => count($workOrders),
            'total_failures'    => count($failureModes),
            'total_spare_parts' => count($spareParts),
        ];

        $this->renderSoftware('asset/show', [
            'pageTitle'    => 'جزئیات دارایی: ' . $asset['name'],
            'softwareName' => 'PdM Analyzer',
            'asset'        => $asset,
            'workOrders'   => $workOrders,
            'failureModes' => $failureModes,
            'spareParts'   => $spareParts,
            'documents'    => $documents,
            'stats'        => $stats,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ویرایش دارایی
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $assetModel = new Asset();
        $asset = $assetModel->find($id);

        if (!$asset) {
            pdm_flash_set('danger', 'دارایی یافت نشد.');
            $this->redirect(pdm_url('asset'));
            return;
        }

        $locationModel = new Location();
        $categoryModel = new AssetCategory();

        $parentAssets = array_filter(
            $assetModel->getSelectList(),
            fn($a) => (int) $a['id'] !== $id
        );

        $this->renderSoftware('asset/edit', [
            'pageTitle'      => 'ویرایش دارایی',
            'softwareName'   => 'PdM Analyzer',
            'asset'          => $asset,
            'pdm_locations'  => $locationModel->getFlatList(),
            'pdm_categories' => $categoryModel->getFlatList(),
            'parentAssets'   => $parentAssets,
            'flash'          => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی دارایی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('asset'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $assetModel = new Asset();

        if (!$assetModel->exists($id)) {
            pdm_flash_set('danger', 'دارایی یافت نشد.');
            $this->redirect(pdm_url('asset'));
            return;
        }

        $data = $this->collectPostData();

        if (empty($data['name']) || empty($data['asset_code'])) {
            pdm_flash_set('danger', 'نام و کد دارایی الزامی است.');
            $this->redirect(pdm_url('asset', 'edit', ['id' => $id]));
            return;
        }

        if ($assetModel->codeExists($data['asset_code'], $id)) {
            pdm_flash_set('danger', 'کد دارایی تکراری است.');
            $this->redirect(pdm_url('asset', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['parent_asset_id']) && (int) $data['parent_asset_id'] === $id) {
            pdm_flash_set('danger', 'یک دارایی نمی‌تواند والد خودش باشد.');
            $this->redirect(pdm_url('asset', 'edit', ['id' => $id]));
            return;
        }

        $assetModel->update($id, $data);

        pdm_flash_set('success', 'دارایی با موفقیت به‌روزرسانی شد.');
        $this->redirect(pdm_url('asset', 'show', ['id' => $id]));
    }

    /**
     * حذف دارایی
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $assetModel = new Asset();

        $asset = $assetModel->find($id);
        if (!$asset) {
            pdm_flash_set('danger', 'دارایی یافت نشد.');
            $this->redirect(pdm_url('asset'));
            return;
        }

        if ($assetModel->isUsedInWorkOrders($id)) {
            pdm_flash_set('danger', 'این دارایی در دستورکارها استفاده شده و قابل حذف نیست.');
            $this->redirect(pdm_url('asset'));
            return;
        }

        $assetModel->delete($id);

        pdm_flash_set('success', 'دارایی با موفقیت حذف شد.');
        $this->redirect(pdm_url('asset'));
    }

    /**
     * جمع‌آوری داده‌های POST (با تبدیل تاریخ شمسی → میلادی)
     */
    private function collectPostData(): array
    {
        $installationDateJalali = trim($_POST['installation_date'] ?? '');
        $installationDateGregorian = null;

        if (!empty($installationDateJalali)) {
            if (class_exists('\App\Helpers\DateHelper')) {
                $installationDateGregorian = \App\Helpers\DateHelper::toGregorian($installationDateJalali);
            } else {
                $normalized = str_replace('/', '-', $installationDateJalali);
                $parts = explode('-', $normalized);
                if (count($parts) === 3 && function_exists('jalali_to_gregorian')) {
                    $g = jalali_to_gregorian((int) $parts[0], (int) $parts[1], (int) $parts[2]);
                    if (is_array($g) && count($g) === 3) {
                        $installationDateGregorian = sprintf('%04d-%02d-%02d', $g[0], $g[1], $g[2]);
                    }
                }
            }
        }

        return [
            'location_id'       => !empty($_POST['location_id']) ? (int) $_POST['location_id'] : null,
            'category_id'       => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'parent_asset_id'   => !empty($_POST['parent_asset_id']) ? (int) $_POST['parent_asset_id'] : null,
            'asset_code'        => trim($_POST['asset_code'] ?? ''),
            'name'              => trim($_POST['name'] ?? ''),
            'manufacturer'      => trim($_POST['manufacturer'] ?? '') ?: null,
            'model'             => trim($_POST['model'] ?? '') ?: null,
            'serial_number'     => trim($_POST['serial_number'] ?? '') ?: null,
            'installation_date' => $installationDateGregorian,
            'criticality'       => $_POST['criticality'] ?? 'medium',
            'status'            => $_POST['status'] ?? 'active',
            'description'       => trim($_POST['description'] ?? '') ?: null,
        ];
    }
}