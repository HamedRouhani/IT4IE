<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Software\Hr\Models\Department;

/**
 * ============================================================
 * DepartmentController - مدیریت دپارتمان‌ها
 * ============================================================
 */
class DepartmentController extends Controller
{
    /**
     * لیست دپارتمان‌ها به صورت درختی
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Department();
        $tree = $model->getTree();
        $stats = $model->getStats();

        $this->renderSoftware('department/index', [
            'pageTitle'    => 'دپارتمان‌ها',
            'softwareName' => 'HR Analyzer',
            'tree'         => $tree,
            'stats'        => $stats,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $model = new Department();

        $this->renderSoftware('department/create', [
            'pageTitle'       => 'افزودن دپارتمان جدید',
            'softwareName'    => 'HR Analyzer',
            'pdm_parents'     => $model->getFlatList(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('department'));
            return;
        }

        $this->verifyCsrf();

        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $costCenter  = trim($_POST['cost_center'] ?? '');
        $budget      = !empty($_POST['budget']) ? (float) str_replace(',', '', $_POST['budget']) : null;
        $location    = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $status      = trim($_POST['status'] ?? 'active');

        if (empty($name)) {
            hr_flash_set('danger', 'نام دپارتمان الزامی است.');
            $this->redirect(hr_url('department', 'create'));
            return;
        }

        $model = new Department();

        if (!empty($code) && $model->codeExists($code)) {
            hr_flash_set('danger', 'کد دپارتمان تکراری است.');
            $this->redirect(hr_url('department', 'create'));
            return;
        }

        if ($parentId !== null && !$model->exists($parentId)) {
            hr_flash_set('danger', 'دپارتمان والد معتبر نیست.');
            $this->redirect(hr_url('department', 'create'));
            return;
        }

        $model->create([
            'parent_id'   => $parentId,
            'code'        => $code ?: null,
            'name'        => $name,
            'cost_center' => $costCenter ?: null,
            'budget'      => $budget,
            'location'    => $location ?: null,
            'description' => $description ?: null,
            'sort_order'  => $sortOrder,
            'status'      => $status,
        ]);

        hr_flash_set('success', 'دپارتمان با موفقیت ثبت شد.');
        $this->redirect(hr_url('department'));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Department();
        $department = $model->find($id);

        if (!$department) {
            hr_flash_set('danger', 'دپارتمان یافت نشد.');
            $this->redirect(hr_url('department'));
            return;
        }

        $parentOptions = $model->getParentOptions($id);

        $this->renderSoftware('department/edit', [
            'pageTitle'      => 'ویرایش دپارتمان',
            'softwareName'   => 'HR Analyzer',
            'department'     => $department,
            'pdm_parents'    => $parentOptions,
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('department'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Department();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'دپارتمان یافت نشد.');
            $this->redirect(hr_url('department'));
            return;
        }

        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $parentId    = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $costCenter  = trim($_POST['cost_center'] ?? '');
        $budget      = !empty($_POST['budget']) ? (float) str_replace(',', '', $_POST['budget']) : null;
        $location    = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $status      = trim($_POST['status'] ?? 'active');

        if (empty($name)) {
            hr_flash_set('danger', 'نام دپارتمان الزامی است.');
            $this->redirect(hr_url('department', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($code) && $model->codeExists($code, $id)) {
            hr_flash_set('danger', 'کد دپارتمان تکراری است.');
            $this->redirect(hr_url('department', 'edit', ['id' => $id]));
            return;
        }

        if ($parentId === $id) {
            hr_flash_set('danger', 'یک دپارتمان نمی‌تواند والد خودش باشد.');
            $this->redirect(hr_url('department', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'parent_id'   => $parentId,
            'code'        => $code ?: null,
            'name'        => $name,
            'cost_center' => $costCenter ?: null,
            'budget'      => $budget,
            'location'    => $location ?: null,
            'description' => $description ?: null,
            'sort_order'  => $sortOrder,
            'status'      => $status,
        ]);

        hr_flash_set('success', 'دپارتمان با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('department'));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Department();

        $department = $model->find($id);
        if (!$department) {
            hr_flash_set('danger', 'دپارتمان یافت نشد.');
            $this->redirect(hr_url('department'));
            return;
        }

        if ($model->hasChildren($id)) {
            hr_flash_set('danger', 'این دپارتمان دارای زیرشاخه است. ابتدا زیرشاخه‌ها را حذف کنید.');
            $this->redirect(hr_url('department'));
            return;
        }

        if ($model->isUsedInEmployees($id)) {
            hr_flash_set('danger', 'این دپارتمان در کارکنان استفاده شده است.');
            $this->redirect(hr_url('department'));
            return;
        }

        if ($model->isUsedInPositions($id)) {
            hr_flash_set('danger', 'این دپارتمان در پست‌ها استفاده شده است.');
            $this->redirect(hr_url('department'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'دپارتمان با موفقیت حذف شد.');
        $this->redirect(hr_url('department'));
    }
}