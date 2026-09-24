<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Competency;

/**
 * ============================================================
 * CompetencyController - مدیریت فهرست شایستگی‌ها
 * ============================================================
 */
class CompetencyController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست شایستگی‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Competency();
        $filters = [
            'q'               => trim($_GET['q'] ?? ''),
            'category'        => trim($_GET['category'] ?? ''),
            'competency_type' => trim($_GET['competency_type'] ?? ''),
            'status'          => trim($_GET['status'] ?? ''),
            'is_core'         => $_GET['is_core'] ?? '',
        ];

        $this->renderSoftware('competency/index', [
            'pageTitle'       => 'شایستگی‌ها',
            'softwareName'    => 'HR Analyzer',
            'competencies'    => $model->search($filters),
            'stats'           => $model->getStats(),
            'filters'         => $filters,
            'categoryOptions' => Competency::getCategoryOptions(),
            'typeOptions'     => Competency::getTypeOptions(),
            'statusOptions'   => Competency::getStatusOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $model = new Competency();

        $this->renderSoftware('competency/create', [
            'pageTitle'           => 'ایجاد شایستگی',
            'softwareName'        => 'HR Analyzer',
            'hr_parent_competencies' => $model->getSelectList(),
            'categoryOptions'     => Competency::getCategoryOptions(),
            'typeOptions'         => Competency::getTypeOptions(),
            'statusOptions'       => Competency::getStatusOptions(),
            'flash'               => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('competency'));
            return;
        }

        $this->verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            hr_flash_set('danger', 'نام شایستگی الزامی است.');
            $this->redirect(hr_url('competency', 'create'));
            return;
        }

        $model = new Competency();

        $code = trim($_POST['code'] ?? '');
        if ($code !== '' && $model->isCodeDuplicate($code)) {
            hr_flash_set('warning', 'این کد قبلاً استفاده شده است.');
            $this->redirect(hr_url('competency', 'create'));
            return;
        }

        $newId = $model->create([
            'parent_id'       => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            'code'            => $code ?: null,
            'name'            => $name,
            'category'        => trim($_POST['category'] ?? 'technical'),
            'competency_type' => trim($_POST['competency_type'] ?? 'skill'),
            'description'     => trim($_POST['description'] ?? '') ?: null,
            'levels'          => trim($_POST['levels'] ?? '') ?: null,
            'is_core'         => !empty($_POST['is_core']) ? 1 : 0,
            'status'          => trim($_POST['status'] ?? 'active'),
            'sort_order'      => (int) ($_POST['sort_order'] ?? 0),
        ]);

        hr_flash_set('success', 'شایستگی با موفقیت ایجاد شد.');
        $this->redirect(hr_url('competency', 'show', ['id' => $newId]));
    }

    /**
     * نمایش
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Competency();
        $competency = $model->findWithDetails($id);

        if (!$competency) {
            hr_flash_set('danger', 'شایستگی یافت نشد.');
            $this->redirect(hr_url('competency'));
            return;
        }

        $this->renderSoftware('competency/show', [
            'pageTitle'       => 'جزئیات شایستگی',
            'softwareName'    => 'HR Analyzer',
            'competency'      => $competency,
            'children'        => $model->getChildren($id),
            'categoryOptions' => Competency::getCategoryOptions(),
            'typeOptions'     => Competency::getTypeOptions(),
            'statusOptions'   => Competency::getStatusOptions(),
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
        $model = new Competency();
        $competency = $model->find($id);

        if (!$competency) {
            hr_flash_set('danger', 'شایستگی یافت نشد.');
            $this->redirect(hr_url('competency'));
            return;
        }

        $this->renderSoftware('competency/edit', [
            'pageTitle'              => 'ویرایش شایستگی',
            'softwareName'           => 'HR Analyzer',
            'competency'             => $competency,
            'hr_parent_competencies' => $model->getSelectList($id),
            'categoryOptions'        => Competency::getCategoryOptions(),
            'typeOptions'            => Competency::getTypeOptions(),
            'statusOptions'          => Competency::getStatusOptions(),
            'flash'                  => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('competency'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Competency();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'شایستگی یافت نشد.');
            $this->redirect(hr_url('competency'));
            return;
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            hr_flash_set('danger', 'نام شایستگی الزامی است.');
            $this->redirect(hr_url('competency', 'edit', ['id' => $id]));
            return;
        }

        $code = trim($_POST['code'] ?? '');
        if ($code !== '' && $model->isCodeDuplicate($code, $id)) {
            hr_flash_set('warning', 'این کد قبلاً استفاده شده است.');
            $this->redirect(hr_url('competency', 'edit', ['id' => $id]));
            return;
        }

        // جلوگیری از parent شدن خودش
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        if ($parentId === $id) {
            $parentId = null;
        }

        $model->update($id, [
            'parent_id'       => $parentId,
            'code'            => $code ?: null,
            'name'            => $name,
            'category'        => trim($_POST['category'] ?? 'technical'),
            'competency_type' => trim($_POST['competency_type'] ?? 'skill'),
            'description'     => trim($_POST['description'] ?? '') ?: null,
            'levels'          => trim($_POST['levels'] ?? '') ?: null,
            'is_core'         => !empty($_POST['is_core']) ? 1 : 0,
            'status'          => trim($_POST['status'] ?? 'active'),
            'sort_order'      => (int) ($_POST['sort_order'] ?? 0),
        ]);

        hr_flash_set('success', 'شایستگی به‌روزرسانی شد.');
        $this->redirect(hr_url('competency', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Competency();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'شایستگی یافت نشد.');
            $this->redirect(hr_url('competency'));
            return;
        }

        // بررسی وجود زیرشاخه
        $children = $model->getChildren($id);
        if (!empty($children)) {
            hr_flash_set('warning', 'ابتدا زیرشاخه‌های این شایستگی را حذف یا منتقل کنید.');
            $this->redirect(hr_url('competency', 'show', ['id' => $id]));
            return;
        }

        // TODO: در فاز ۲، بررسی شود که این شایستگی در هیچ مدل شایستگی استفاده نشده باشد

        $model->delete($id);
        hr_flash_set('success', 'شایستگی حذف شد.');
        $this->redirect(hr_url('competency'));
    }

    /**
     * نمای درختی
     */
    public function tree()
    {
        $this->requireAuth();

        $model = new Competency();

        $this->renderSoftware('competency/tree', [
            'pageTitle'       => 'درخت شایستگی‌ها',
            'softwareName'    => 'HR Analyzer',
            'tree'            => $model->getTree(),
            'stats'           => $model->getStats(),
            'categoryOptions' => Competency::getCategoryOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }
}