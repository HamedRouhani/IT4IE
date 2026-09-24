<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\KPI;
use App\Software\Hr\Models\KPIValue;

/**
 * ============================================================
 * KPIController - مدیریت شاخص‌های کلیدی عملکرد
 * ============================================================
 */
class KPIController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================================
    // بخش ۱: تعریف شاخص‌ها (hr_kpis)
    // ============================================================

    /**
     * لیست شاخص‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new KPI();
        $filters = [
            'q'          => trim($_GET['q'] ?? ''),
            'category'   => trim($_GET['category'] ?? ''),
            'kpi_type'   => trim($_GET['kpi_type'] ?? ''),
            'is_active'  => $_GET['is_active'] ?? '',
            'is_builtin' => $_GET['is_builtin'] ?? '',
        ];

        $this->renderSoftware('kpi/index', [
            'pageTitle'       => 'شاخص‌های کلیدی عملکرد',
            'softwareName'    => 'HR Analyzer',
            'kpis'            => $model->search($filters),
            'stats'           => $model->getStats(),
            'filters'         => $filters,
            'categoryOptions' => KPI::getCategoryOptions(),
            'categoryColors'  => KPI::getCategoryColors(),
            'typeOptions'     => KPI::getTypeOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد شاخص
     */
    public function create()
    {
        $this->requireAuth();

        $this->renderSoftware('kpi/create', [
            'pageTitle'       => 'ایجاد شاخص جدید',
            'softwareName'    => 'HR Analyzer',
            'categoryOptions' => KPI::getCategoryOptions(),
            'typeOptions'     => KPI::getTypeOptions(),
            'categoryColors'  => KPI::getCategoryColors(),
            'categoryIcons'   => KPI::getCategoryIcons(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره شاخص
     */
    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('kpi'));
            return;
        }
        $this->verifyCsrf();

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if ($code === '' || $name === '') {
            hr_flash_set('danger', 'کد و نام شاخص الزامی است.');
            $this->redirect(hr_url('kpi', 'create'));
            return;
        }

        $model = new KPI();
        if ($model->isCodeDuplicate($code)) {
            hr_flash_set('warning', 'این کد قبلاً استفاده شده است.');
            $this->redirect(hr_url('kpi', 'create'));
            return;
        }

        $category = trim($_POST['category'] ?? 'other');
        $colors = KPI::getCategoryColors();
        $icons = KPI::getCategoryIcons();

        $newId = $model->create([
            'code'        => $code,
            'name'        => $name,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'formula'     => trim($_POST['formula'] ?? '') ?: null,
            'unit'        => trim($_POST['unit'] ?? '') ?: null,
            'category'    => $category,
            'kpi_type'    => trim($_POST['kpi_type'] ?? 'manual'),
            'is_builtin'  => 0,
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'color'       => trim($_POST['color'] ?? '') ?: ($colors[$category] ?? '#1E40AF'),
            'icon'        => trim($_POST['icon'] ?? '') ?: ($icons[$category] ?? 'fas fa-chart-bar'),
        ]);

        hr_flash_set('success', 'شاخص با موفقیت ایجاد شد.');
        $this->redirect(hr_url('kpi', 'show', ['id' => $newId]));
    }

    /**
     * نمایش شاخص + مقادیر
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            hr_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(hr_url('kpi'));
            return;
        }

        $valueModel = new KPIValue();
        $values = $model->getValues($id);

        $this->renderSoftware('kpi/show', [
            'pageTitle'       => 'جزئیات شاخص',
            'softwareName'    => 'HR Analyzer',
            'kpi'             => $kpi,
            'values'          => $values,
            'categoryOptions' => KPI::getCategoryOptions(),
            'typeOptions'     => KPI::getTypeOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            hr_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(hr_url('kpi'));
            return;
        }

        // KPI سیستمی قابل ویرایش نیست
        if (!empty($kpi['is_builtin'])) {
            hr_flash_set('warning', 'شاخص‌های سیستمی قابل ویرایش نیستند.');
            $this->redirect(hr_url('kpi', 'show', ['id' => $id]));
            return;
        }

        $this->renderSoftware('kpi/edit', [
            'pageTitle'       => 'ویرایش شاخص',
            'softwareName'    => 'HR Analyzer',
            'kpi'             => $kpi,
            'categoryOptions' => KPI::getCategoryOptions(),
            'typeOptions'     => KPI::getTypeOptions(),
            'categoryColors'  => KPI::getCategoryColors(),
            'categoryIcons'   => KPI::getCategoryIcons(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی شاخص
     */
    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('kpi'));
            return;
        }
        $this->verifyCsrf();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            hr_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(hr_url('kpi'));
            return;
        }
        if (!empty($kpi['is_builtin'])) {
            hr_flash_set('warning', 'شاخص‌های سیستمی قابل ویرایش نیستند.');
            $this->redirect(hr_url('kpi', 'show', ['id' => $id]));
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if ($code === '' || $name === '') {
            hr_flash_set('danger', 'کد و نام الزامی است.');
            $this->redirect(hr_url('kpi', 'edit', ['id' => $id]));
            return;
        }

        if ($model->isCodeDuplicate($code, $id)) {
            hr_flash_set('warning', 'این کد قبلاً استفاده شده است.');
            $this->redirect(hr_url('kpi', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'code'        => $code,
            'name'        => $name,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'formula'     => trim($_POST['formula'] ?? '') ?: null,
            'unit'        => trim($_POST['unit'] ?? '') ?: null,
            'category'    => trim($_POST['category'] ?? 'other'),
            'kpi_type'    => trim($_POST['kpi_type'] ?? 'manual'),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'color'       => trim($_POST['color'] ?? '') ?: '#1E40AF',
            'icon'        => trim($_POST['icon'] ?? '') ?: 'fas fa-chart-bar',
        ]);

        hr_flash_set('success', 'شاخص به‌روزرسانی شد.');
        $this->redirect(hr_url('kpi', 'show', ['id' => $id]));
    }

    /**
     * حذف شاخص
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPI();
        $kpi = $model->find($id);

        if (!$kpi) {
            hr_flash_set('danger', 'شاخص یافت نشد.');
            $this->redirect(hr_url('kpi'));
            return;
        }
        if (!empty($kpi['is_builtin'])) {
            hr_flash_set('warning', 'شاخص‌های سیستمی قابل حذف نیستند.');
            $this->redirect(hr_url('kpi', 'show', ['id' => $id]));
            return;
        }

        $model->delete($id);
        hr_flash_set('success', 'شاخص و مقادیر مرتبط حذف شدند.');
        $this->redirect(hr_url('kpi'));
    }

    // ============================================================
    // بخش ۲: مقادیر شاخص‌ها (hr_kpi_values)
    // ============================================================

    /**
     * لیست مقادیر
     */
    public function values()
    {
        $this->requireAuth();

        $model = new KPIValue();
        $kpiModel = new KPI();

        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'kpi_id'    => !empty($_GET['kpi_id']) ? (int) $_GET['kpi_id'] : null,
            'category'  => trim($_GET['category'] ?? ''),
            'period'    => trim($_GET['period'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to'] ?? ''),
        ];

        $this->renderSoftware('kpi/values', [
            'pageTitle'       => 'مقادیر شاخص‌ها',
            'softwareName'    => 'HR Analyzer',
            'values'          => $model->search($filters),
            'stats'           => $model->getStats(),
            'filters'         => $filters,
            'hr_kpis'         => $kpiModel->getSelectList(),
            'categoryOptions' => KPI::getCategoryOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد مقدار
     */
    public function createValue()
    {
        $this->requireAuth();

        $kpiModel = new KPI();
        $preselectedKpiId = !empty($_GET['kpi_id']) ? (int) $_GET['kpi_id'] : null;

        $this->renderSoftware('kpi/value_create', [
            'pageTitle'          => 'ثبت مقدار جدید',
            'softwareName'       => 'HR Analyzer',
            'hr_kpis'            => $kpiModel->getSelectList(),
            'preselectedKpiId'   => $preselectedKpiId,
            'flash'              => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره مقدار
     */
    public function storeValue()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }
        $this->verifyCsrf();

        $kpiId      = (int) ($_POST['kpi_id'] ?? 0);
        $period     = trim($_POST['period'] ?? '');
        $periodDate = trim($_POST['period_date'] ?? '');

        if ($kpiId === 0 || $period === '' || $periodDate === '') {
            hr_flash_set('danger', 'شاخص، دوره و تاریخ الزامی است.');
            $this->redirect(hr_url('kpi', 'createValue'));
            return;
        }

        $periodDateG = $this->convertJalaliDate($periodDate);
        if (!$periodDateG) {
            hr_flash_set('danger', 'تاریخ نامعتبر است.');
            $this->redirect(hr_url('kpi', 'createValue'));
            return;
        }

        $model = new KPIValue();

        if ($model->isDuplicate($kpiId, $period)) {
            hr_flash_set('warning', 'برای این شاخص در این دوره قبلاً مقداری ثبت شده است.');
            $this->redirect(hr_url('kpi', 'createValue', ['kpi_id' => $kpiId]));
            return;
        }

        $newId = $model->create([
            'kpi_id'       => $kpiId,
            'value'        => $this->toDecimal($_POST['value'] ?? null),
            'target_value' => $this->toDecimal($_POST['target_value'] ?? null),
            'period'       => $period,
            'period_date'  => $periodDateG,
            'notes'        => trim($_POST['notes'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'مقدار شاخص ثبت شد.');
        $this->redirect(hr_url('kpi', 'showValue', ['id' => $newId]));
    }

    /**
     * نمایش مقدار
     */
    public function showValue($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPIValue();
        $value = $model->findWithDetails($id);

        if (!$value) {
            hr_flash_set('danger', 'مقدار یافت نشد.');
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }

        $this->renderSoftware('kpi/value_show', [
            'pageTitle'    => 'جزئیات مقدار شاخص',
            'softwareName' => 'HR Analyzer',
            'value'        => $value,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش مقدار
     */
    public function editValue($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPIValue();
        $value = $model->find($id);

        if (!$value) {
            hr_flash_set('danger', 'مقدار یافت نشد.');
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }

        $kpiModel = new KPI();

        $this->renderSoftware('kpi/value_edit', [
            'pageTitle'    => 'ویرایش مقدار',
            'softwareName' => 'HR Analyzer',
            'value'        => $value,
            'hr_kpis'      => $kpiModel->getSelectList(),
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی مقدار
     */
    public function updateValue($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }
        $this->verifyCsrf();

        $id = (int) $id;
        $model = new KPIValue();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'مقدار یافت نشد.');
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }

        $kpiId      = (int) ($_POST['kpi_id'] ?? 0);
        $period     = trim($_POST['period'] ?? '');
        $periodDate = trim($_POST['period_date'] ?? '');

        if ($kpiId === 0 || $period === '' || $periodDate === '') {
            hr_flash_set('danger', 'شاخص، دوره و تاریخ الزامی است.');
            $this->redirect(hr_url('kpi', 'editValue', ['id' => $id]));
            return;
        }

        $periodDateG = $this->convertJalaliDate($periodDate);

        if ($model->isDuplicate($kpiId, $period, $id)) {
            hr_flash_set('warning', 'برای این شاخص در این دوره قبلاً مقداری ثبت شده است.');
            $this->redirect(hr_url('kpi', 'editValue', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'kpi_id'       => $kpiId,
            'value'        => $this->toDecimal($_POST['value'] ?? null),
            'target_value' => $this->toDecimal($_POST['target_value'] ?? null),
            'period'       => $period,
            'period_date'  => $periodDateG,
            'notes'        => trim($_POST['notes'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'مقدار به‌روزرسانی شد.');
        $this->redirect(hr_url('kpi', 'showValue', ['id' => $id]));
    }

    /**
     * حذف مقدار
     */
    public function deleteValue($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new KPIValue();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'مقدار یافت نشد.');
            $this->redirect(hr_url('kpi', 'values'));
            return;
        }

        $model->delete($id);
        hr_flash_set('success', 'مقدار حذف شد.');
        $this->redirect(hr_url('kpi', 'values'));
    }

    // ============================================================
    // متدهای کمکی
    // ============================================================

    private function toDecimal($value): ?float
    {
        if ($value === null || $value === '') return null;
        $value = str_replace([',', ' ', '٬'], '', (string) $value);
        return (float) $value;
    }

    private function convertJalaliDate(string $jalali): ?string
    {
        $jalali = trim($jalali);
        if ($jalali === '') return null;

        $jalali = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $jalali
        );
        $jalali = str_replace('-', '/', $jalali);
        $parts = explode('/', $jalali);
        if (count($parts) !== 3) return null;

        list($jy, $jm, $jd) = array_map('intval', $parts);
        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        list($gy, $gm, $gd) = hr_jalali_to_gregorian($jy, $jm, $jd);
        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }
}