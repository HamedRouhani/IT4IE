<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\SparePart;

/**
 * ============================================================
 * SparePartController - مدیریت قطعات یدکی
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/SparePartController.php
 * ============================================================
 */
class SparePartController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست قطعات
     */
    public function index()
    {
        $this->requireAuth();

        $model = new SparePart();

        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'low_stock' => !empty($_GET['low_stock']),
        ];

        $spareParts = $model->search($filters);
        $stats = $model->getStockStats();

        $this->renderSoftware('spare_part/index', [
            'pageTitle'    => 'قطعات یدکی',
            'softwareName' => 'PdM Analyzer',
            'spareParts'   => $spareParts,
            'filters'      => $filters,
            'stats'        => $stats,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد قطعه
     */
    public function create()
    {
        $this->requireAuth();

        $this->renderSoftware('spare_part/create', [
            'pageTitle'    => 'افزودن قطعه یدکی',
            'softwareName' => 'PdM Analyzer',
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره قطعه
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('spare_part'));
            return;
        }

        $this->verifyCsrf();

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $stock = (int) ($_POST['stock_quantity'] ?? 0);
        $min = (int) ($_POST['minimum_stock'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'عدد');
        $desc = trim($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            pdm_flash_set('danger', 'کد و نام قطعه الزامی است.');
            $this->redirect(pdm_url('spare_part', 'create'));
            return;
        }

        $model = new SparePart();

        if ($model->codeExists($code)) {
            pdm_flash_set('danger', 'کد قطعه تکراری است.');
            $this->redirect(pdm_url('spare_part', 'create'));
            return;
        }

        $model->create([
            'code'            => $code,
            'name'            => $name,
            'manufacturer'    => $manufacturer ?: null,
            'stock_quantity'  => $stock,
            'minimum_stock'   => $min,
            'unit'            => $unit ?: 'عدد',
            'description'     => $desc ?: null,
        ]);

        pdm_flash_set('success', 'قطعه یدکی با موفقیت ثبت شد.');
        $this->redirect(pdm_url('spare_part'));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new SparePart();
        $part = $model->find($id);

        if (!$part) {
            pdm_flash_set('danger', 'قطعه یافت نشد.');
            $this->redirect(pdm_url('spare_part'));
            return;
        }

        $this->renderSoftware('spare_part/edit', [
            'pageTitle'    => 'ویرایش قطعه یدکی',
            'softwareName' => 'PdM Analyzer',
            'part'         => $part,
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
            $this->redirect(pdm_url('spare_part'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new SparePart();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'قطعه یافت نشد.');
            $this->redirect(pdm_url('spare_part'));
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $stock = (int) ($_POST['stock_quantity'] ?? 0);
        $min = (int) ($_POST['minimum_stock'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'عدد');
        $desc = trim($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            pdm_flash_set('danger', 'کد و نام قطعه الزامی است.');
            $this->redirect(pdm_url('spare_part', 'edit', ['id' => $id]));
            return;
        }

        if ($model->codeExists($code, $id)) {
            pdm_flash_set('danger', 'کد قطعه تکراری است.');
            $this->redirect(pdm_url('spare_part', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'code'            => $code,
            'name'            => $name,
            'manufacturer'    => $manufacturer ?: null,
            'stock_quantity'  => $stock,
            'minimum_stock'   => $min,
            'unit'            => $unit ?: 'عدد',
            'description'     => $desc ?: null,
        ]);

        pdm_flash_set('success', 'قطعه یدکی به‌روزرسانی شد.');
        $this->redirect(pdm_url('spare_part'));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new SparePart();

        $part = $model->find($id);
        if (!$part) {
            pdm_flash_set('danger', 'قطعه یافت نشد.');
            $this->redirect(pdm_url('spare_part'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'قطعه یدکی حذف شد.');
        $this->redirect(pdm_url('spare_part'));
    }
}