<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Software\Pdm\Models\Location;

/**
 * ============================================================
 * LocationController - مدیریت مکان‌ها
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/LocationController.php
 * ============================================================
 */
class LocationController extends Controller
{
    /**
     * لیست مکان‌ها به صورت درختی
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Location();
        $tree = $model->getTree();
        $totalCount = $model->count();

        $this->renderSoftware('location/index', [
            'pageTitle'    => 'مکان‌ها',
            'softwareName' => 'PdM Analyzer',
            'tree'         => $tree,
            'totalCount'   => $totalCount,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد مکان
     */
    public function create()
    {
        $this->requireAuth();

        $model = new Location();

        $this->renderSoftware('location/create', [
            'pageTitle'        => 'افزودن مکان جدید',
            'softwareName'     => 'PdM Analyzer',
            'pdm_parentOptions' => $model->getFlatList(),
            'flash'            => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره مکان جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('location'));
            return;
        }

        $this->verifyCsrf();

        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            pdm_flash_set('danger', 'نام مکان الزامی است.');
            $this->redirect(pdm_url('location', 'create'));
            return;
        }

        $model = new Location();

        if (!empty($code) && $model->codeExists($code)) {
            pdm_flash_set('danger', 'کد مکان تکراری است.');
            $this->redirect(pdm_url('location', 'create'));
            return;
        }

        // بررسی معتبر بودن parent
        if ($parentId !== null && !$model->exists($parentId)) {
            pdm_flash_set('danger', 'مکان والد انتخاب‌شده معتبر نیست.');
            $this->redirect(pdm_url('location', 'create'));
            return;
        }

        $model->create([
            'parent_id'   => $parentId,
            'code'        => $code ?: null,
            'name'        => $name,
            'description' => $description,
        ]);

        pdm_flash_set('success', 'مکان با موفقیت ثبت شد.');
        $this->redirect(pdm_url('location'));
    }

    /**
     * فرم ویرایش مکان
     */
    public function edit($id)
    {
        $this->requireAuth();

        $model = new Location();
        $location = $model->find((int) $id);

        if (!$location) {
            pdm_flash_set('danger', 'مکان یافت نشد.');
            $this->redirect(pdm_url('location'));
            return;
        }

        $parentOptions = $model->getParentOptions((int) $id);

        $this->renderSoftware('location/edit', [
            'pageTitle'         => 'ویرایش مکان',
            'softwareName'      => 'PdM Analyzer',
            'location'          => $location,
            'pdm_parentOptions' => $model->getParentOptions((int) $id),
            'flash'             => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی مکان
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('location'));
            return;
        }

        $this->verifyCsrf();

        $id          = (int) $id;
        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            pdm_flash_set('danger', 'نام مکان الزامی است.');
            $this->redirect(pdm_url('location', 'edit', ['id' => $id]));
            return;
        }

        $model = new Location();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'مکان یافت نشد.');
            $this->redirect(pdm_url('location'));
            return;
        }

        if (!empty($code) && $model->codeExists($code, $id)) {
            pdm_flash_set('danger', 'کد مکان تکراری است.');
            $this->redirect(pdm_url('location', 'edit', ['id' => $id]));
            return;
        }

        // جلوگیری از انتخاب خود به عنوان والد
        if ($parentId === $id) {
            pdm_flash_set('danger', 'یک مکان نمی‌تواند والد خودش باشد.');
            $this->redirect(pdm_url('location', 'edit', ['id' => $id]));
            return;
        }

        if ($parentId !== null && !$model->exists($parentId)) {
            pdm_flash_set('danger', 'مکان والد انتخاب‌شده معتبر نیست.');
            $this->redirect(pdm_url('location', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'parent_id'   => $parentId,
            'code'        => $code ?: null,
            'name'        => $name,
            'description' => $description,
        ]);

        pdm_flash_set('success', 'مکان با موفقیت به‌روزرسانی شد.');
        $this->redirect(pdm_url('location'));
    }

    /**
     * حذف مکان
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Location();

        $location = $model->find($id);
        if (!$location) {
            pdm_flash_set('danger', 'مکان یافت نشد.');
            $this->redirect(pdm_url('location'));
            return;
        }

        // بررسی فرزند
        if ($model->hasChildren($id)) {
            pdm_flash_set('danger', 'این مکان دارای زیرشاخه است. ابتدا زیرشاخه‌ها را حذف کنید.');
            $this->redirect(pdm_url('location'));
            return;
        }

        // بررسی استفاده در دارایی‌ها
        if ($model->isUsedInAssets($id)) {
            pdm_flash_set('danger', 'این مکان در دارایی‌ها استفاده شده و قابل حذف نیست.');
            $this->redirect(pdm_url('location'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'مکان با موفقیت حذف شد.');
        $this->redirect(pdm_url('location'));
    }
}