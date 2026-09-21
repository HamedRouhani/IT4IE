<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Software\Pdm\Models\AssetCategory;

/**
 * ============================================================
 * AssetCategoryController - مدیریت دسته‌بندی دارایی‌ها
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/AssetCategoryController.php
 * ============================================================
 */
class AssetCategoryController extends Controller
{
    /**
     * لیست دسته‌بندی‌ها به صورت درختی
     */
    public function index()
    {
        $this->requireAuth();

        $model = new AssetCategory();
        $tree = $model->getTree();
        $totalCount = $model->count();

        $this->renderSoftware('category/index', [
            'pageTitle'    => 'دسته‌بندی دارایی‌ها',
            'softwareName' => 'PdM Analyzer',
            'tree'         => $tree,
            'totalCount'   => $totalCount,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * فرم ایجاد دسته
     */
    public function create()
    {
        $this->requireAuth();
        $model = new AssetCategory();

        $this->renderSoftware('category/create', [
            'pageTitle'         => 'افزودن دسته‌بندی جدید',
            'softwareName'      => 'PdM Analyzer',
            'pdm_parentOptions' => $model->getFlatList(),
            'flash'             => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره دسته جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        $this->verifyCsrf();

        $name        = trim($_POST['name'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            pdm_flash_set('danger', 'نام دسته الزامی است.');
            $this->redirect(pdm_url('asset_category', 'create'));
            return;
        }

        $model = new AssetCategory();

        if ($parentId !== null && !$model->exists($parentId)) {
            pdm_flash_set('danger', 'دسته والد انتخاب‌شده معتبر نیست.');
            $this->redirect(pdm_url('asset_category', 'create'));
            return;
        }

        $model->create([
            'parent_id'   => $parentId,
            'name'        => $name,
            'description' => $description,
        ]);

        pdm_flash_set('success', 'دسته‌بندی با موفقیت ثبت شد.');
        $this->redirect(pdm_url('asset_category'));
    }

    /**
     * فرم ویرایش دسته
     */
    public function edit($id)
    {
        $this->requireAuth();

        $model = new AssetCategory();
        $category = $model->find((int) $id);

        if (!$category) {
            pdm_flash_set('danger', 'دسته‌بندی یافت نشد.');
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        $parentOptions = $model->getFlatList();

        $this->renderSoftware('category/edit', [
            'pageTitle'         => 'ویرایش دسته‌بندی',
            'softwareName'      => 'PdM Analyzer',
            'category'          => $category,
            'pdm_parentOptions' => $model->getFlatList(),
            'flash'             => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی دسته
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        $this->verifyCsrf();

        $id          = (int) $id;
        $name        = trim($_POST['name'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            pdm_flash_set('danger', 'نام دسته الزامی است.');
            $this->redirect(pdm_url('asset_category', 'edit', ['id' => $id]));
            return;
        }

        $model = new AssetCategory();

        if (!$model->exists($id)) {
            pdm_flash_set('danger', 'دسته‌بندی یافت نشد.');
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        if ($parentId === $id) {
            pdm_flash_set('danger', 'یک دسته نمی‌تواند والد خودش باشد.');
            $this->redirect(pdm_url('asset_category', 'edit', ['id' => $id]));
            return;
        }

        if ($parentId !== null && !$model->exists($parentId)) {
            pdm_flash_set('danger', 'دسته والد انتخاب‌شده معتبر نیست.');
            $this->redirect(pdm_url('asset_category', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'parent_id'   => $parentId,
            'name'        => $name,
            'description' => $description,
        ]);

        pdm_flash_set('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
        $this->redirect(pdm_url('asset_category'));
    }

    /**
     * حذف دسته
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new AssetCategory();

        $category = $model->find($id);
        if (!$category) {
            pdm_flash_set('danger', 'دسته‌بندی یافت نشد.');
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        if ($model->hasChildren($id)) {
            pdm_flash_set('danger', 'این دسته دارای زیرشاخه است. ابتدا زیرشاخه‌ها را حذف کنید.');
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        if ($model->isUsedInAssets($id)) {
            pdm_flash_set('danger', 'این دسته در دارایی‌ها استفاده شده و قابل حذف نیست.');
            $this->redirect(pdm_url('asset_category'));
            return;
        }

        $model->delete($id);

        pdm_flash_set('success', 'دسته‌بندی با موفقیت حذف شد.');
        $this->redirect(pdm_url('asset_category'));
    }
}