<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Software\Hr\Models\JobGrade;

/**
 * ============================================================
 * JobGradeController - مدیریت طبقه‌بندی شغلی
 * ============================================================
 */
class JobGradeController extends Controller
{
    /**
     * لیست طبقات
     */
    public function index()
    {
        $this->requireAuth();

        $model = new JobGrade();
        $grades = $model->getAllWithDetails();
        $stats = $model->getStats();

        $this->renderSoftware('job_grade/index', [
            'pageTitle'    => 'طبقه‌بندی شغلی',
            'softwareName' => 'HR Analyzer',
            'grades'       => $grades,
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

        $this->renderSoftware('job_grade/create', [
            'pageTitle'    => 'افزودن طبقه شغلی',
            'softwareName' => 'HR Analyzer',
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('job_grade'));
            return;
        }

        $this->verifyCsrf();

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $level = (int) ($_POST['level'] ?? 1);
        $minSalary = !empty($_POST['min_salary']) ? (float) str_replace(',', '', $_POST['min_salary']) : null;
        $maxSalary = !empty($_POST['max_salary']) ? (float) str_replace(',', '', $_POST['max_salary']) : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            hr_flash_set('danger', 'کد و نام طبقه الزامی است.');
            $this->redirect(hr_url('job_grade', 'create'));
            return;
        }

        if ($level < 1) {
            hr_flash_set('danger', 'سطح باید عددی بزرگتر از صفر باشد.');
            $this->redirect(hr_url('job_grade', 'create'));
            return;
        }

        $model = new JobGrade();

        if ($model->codeExists($code)) {
            hr_flash_set('danger', 'کد طبقه تکراری است.');
            $this->redirect(hr_url('job_grade', 'create'));
            return;
        }

        if ($model->levelExists($level)) {
            hr_flash_set('danger', 'سطح ' . $level . ' قبلاً استفاده شده است.');
            $this->redirect(hr_url('job_grade', 'create'));
            return;
        }

        $model->create([
            'code'        => $code,
            'name'        => $name,
            'level'       => $level,
            'min_salary'  => $minSalary,
            'max_salary'  => $maxSalary,
            'description' => $description ?: null,
        ]);

        hr_flash_set('success', 'طبقه شغلی با موفقیت ثبت شد.');
        $this->redirect(hr_url('job_grade'));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new JobGrade();
        $grade = $model->find($id);

        if (!$grade) {
            hr_flash_set('danger', 'طبقه شغلی یافت نشد.');
            $this->redirect(hr_url('job_grade'));
            return;
        }

        $this->renderSoftware('job_grade/edit', [
            'pageTitle'    => 'ویرایش طبقه شغلی',
            'softwareName' => 'HR Analyzer',
            'grade'        => $grade,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('job_grade'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new JobGrade();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'طبقه شغلی یافت نشد.');
            $this->redirect(hr_url('job_grade'));
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $level = (int) ($_POST['level'] ?? 1);
        $minSalary = !empty($_POST['min_salary']) ? (float) str_replace(',', '', $_POST['min_salary']) : null;
        $maxSalary = !empty($_POST['max_salary']) ? (float) str_replace(',', '', $_POST['max_salary']) : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($code) || empty($name)) {
            hr_flash_set('danger', 'کد و نام طبقه الزامی است.');
            $this->redirect(hr_url('job_grade', 'edit', ['id' => $id]));
            return;
        }

        if ($model->codeExists($code, $id)) {
            hr_flash_set('danger', 'کد طبقه تکراری است.');
            $this->redirect(hr_url('job_grade', 'edit', ['id' => $id]));
            return;
        }

        if ($model->levelExists($level, $id)) {
            hr_flash_set('danger', 'سطح ' . $level . ' قبلاً استفاده شده است.');
            $this->redirect(hr_url('job_grade', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'code'        => $code,
            'name'        => $name,
            'level'       => $level,
            'min_salary'  => $minSalary,
            'max_salary'  => $maxSalary,
            'description' => $description ?: null,
        ]);

        hr_flash_set('success', 'طبقه شغلی با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('job_grade'));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new JobGrade();

        $grade = $model->find($id);
        if (!$grade) {
            hr_flash_set('danger', 'طبقه شغلی یافت نشد.');
            $this->redirect(hr_url('job_grade'));
            return;
        }

        if ($model->isUsedInPositions($id)) {
            hr_flash_set('danger', 'این طبقه در پست‌ها استفاده شده است.');
            $this->redirect(hr_url('job_grade'));
            return;
        }

        if ($model->isUsedInEmployees($id)) {
            hr_flash_set('danger', 'این طبقه در کارکنان استفاده شده است.');
            $this->redirect(hr_url('job_grade'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'طبقه شغلی با موفقیت حذف شد.');
        $this->redirect(hr_url('job_grade'));
    }
}