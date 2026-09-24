<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Training;

/**
 * ============================================================
 * TrainingController - مدیریت دوره‌های آموزشی
 * ============================================================
 */
class TrainingController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requireAuth();

        $model = new Training();
        $filters = [
            'q'             => trim($_GET['q'] ?? ''),
            'training_type' => trim($_GET['training_type'] ?? ''),
            'status'        => trim($_GET['status'] ?? ''),
            'category'      => trim($_GET['category'] ?? ''),
        ];

        $this->renderSoftware('training/index', [
            'pageTitle'     => 'دوره‌های آموزشی',
            'softwareName'  => 'HR Analyzer',
            'trainings'     => $model->search($filters),
            'stats'         => $model->getStats(),
            'filters'       => $filters,
            'typeOptions'   => Training::getTypeOptions(),
            'statusOptions' => Training::getStatusOptions(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    public function create()
    {
        $this->requireAuth();

        $this->renderSoftware('training/create', [
            'pageTitle'           => 'ایجاد دوره آموزشی',
            'softwareName'        => 'HR Analyzer',
            'typeOptions'         => Training::getTypeOptions(),
            'deliveryModeOptions' => Training::getDeliveryModeOptions(),
            'statusOptions'       => Training::getStatusOptions(),
            'flash'               => hr_flash_get(),
        ], 'hr');
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('training'));
            return;
        }
        $this->verifyCsrf();

        $title = trim($_POST['title'] ?? '');
        $start = trim($_POST['start_date'] ?? '');
        $end   = trim($_POST['end_date'] ?? '');

        if ($title === '' || $start === '' || $end === '') {
            hr_flash_set('danger', 'عنوان، تاریخ شروع و پایان الزامی است.');
            $this->redirect(hr_url('training', 'create'));
            return;
        }

        $startG = $this->convertJalaliDate($start);
        $endG   = $this->convertJalaliDate($end);
        if (!$startG || !$endG) {
            hr_flash_set('danger', 'تاریخ نامعتبر است.');
            $this->redirect(hr_url('training', 'create'));
            return;
        }

        $model = new Training();
        $newId = $model->create([
            'title'           => $title,
            'code'            => trim($_POST['code'] ?? '') ?: null,
            'description'     => trim($_POST['description'] ?? '') ?: null,
            'category'        => trim($_POST['category'] ?? '') ?: null,
            'training_type'   => trim($_POST['training_type'] ?? 'internal'),
            'provider'        => trim($_POST['provider'] ?? '') ?: null,
            'instructor'      => trim($_POST['instructor'] ?? '') ?: null,
            'location'        => trim($_POST['location'] ?? '') ?: null,
            'meeting_link'    => trim($_POST['meeting_link'] ?? '') ?: null,
            'start_date'      => $startG,
            'end_date'        => $endG,
            'duration_hours'  => $_POST['duration_hours'] !== '' ? (float) $_POST['duration_hours'] : null,
            'capacity'        => !empty($_POST['capacity']) ? (int) $_POST['capacity'] : null,
            'enrolled_count'  => 0,
            'cost_per_person' => $_POST['cost_per_person'] !== '' ? (float) $_POST['cost_per_person'] : null,
            'total_cost'      => $_POST['total_cost'] !== '' ? (float) $_POST['total_cost'] : null,
            'priority'        => trim($_POST['priority'] ?? 'normal'),
            'status'          => trim($_POST['status'] ?? 'draft'),
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
            'created_by'      => (int) ($_SESSION['user_id'] ?? 0) ?: null,
        ]);

        hr_flash_set('success', 'دوره آموزشی با موفقیت ایجاد شد.');
        $this->redirect(hr_url('training', 'show', ['id' => $newId]));
    }

    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Training();
        $training = $model->findWithDetails($id);

        if (!$training) {
            hr_flash_set('danger', 'دوره یافت نشد.');
            $this->redirect(hr_url('training'));
            return;
        }

        // دریافت ثبت‌نام‌های این دوره
        $enrollModel = new \App\Software\Hr\Models\Enrollment();
        $enrollments = $enrollModel->search(['training_id' => $id]);

        $this->renderSoftware('training/show', [
            'pageTitle'     => 'جزئیات دوره',
            'softwareName'  => 'HR Analyzer',
            'training'      => $training,
            'enrollments'   => $enrollments,
            'typeOptions'   => Training::getTypeOptions(),
            'statusOptions' => Training::getStatusOptions(),
            'enrollStatusOptions' => \App\Software\Hr\Models\Enrollment::getStatusOptions(),
            'flash'         => hr_flash_get(),
        ], 'hr');
    }

    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Training();
        $training = $model->find($id);

        if (!$training) {
            hr_flash_set('danger', 'دوره یافت نشد.');
            $this->redirect(hr_url('training'));
            return;
        }

        $this->renderSoftware('training/edit', [
            'pageTitle'           => 'ویرایش دوره',
            'softwareName'        => 'HR Analyzer',
            'training'            => $training,
            'typeOptions'         => Training::getTypeOptions(),
            'deliveryModeOptions' => Training::getDeliveryModeOptions(),
            'statusOptions'       => Training::getStatusOptions(),
            'flash'               => hr_flash_get(),
        ], 'hr');
    }

    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('training'));
            return;
        }
        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Training();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'دوره یافت نشد.');
            $this->redirect(hr_url('training'));
            return;
        }

        $startG = $this->convertJalaliDate(trim($_POST['start_date'] ?? ''));
        $endG   = $this->convertJalaliDate(trim($_POST['end_date'] ?? ''));

        $model->update($id, [
            'title'           => trim($_POST['title'] ?? ''),
            'code'            => trim($_POST['code'] ?? '') ?: null,
            'description'     => trim($_POST['description'] ?? '') ?: null,
            'category'        => trim($_POST['category'] ?? '') ?: null,
            'training_type'   => trim($_POST['training_type'] ?? 'internal'),
            'provider'        => trim($_POST['provider'] ?? '') ?: null,
            'instructor'      => trim($_POST['instructor'] ?? '') ?: null,
            'location'        => trim($_POST['location'] ?? '') ?: null,
            'meeting_link'    => trim($_POST['meeting_link'] ?? '') ?: null,
            'start_date'      => $startG,
            'end_date'        => $endG,
            'duration_hours'  => $_POST['duration_hours'] !== '' ? (float) $_POST['duration_hours'] : null,
            'capacity'        => !empty($_POST['capacity']) ? (int) $_POST['capacity'] : null,
            'cost_per_person' => $_POST['cost_per_person'] !== '' ? (float) $_POST['cost_per_person'] : null,
            'total_cost'      => $_POST['total_cost'] !== '' ? (float) $_POST['total_cost'] : null,
            'priority'        => trim($_POST['priority'] ?? 'normal'),
            'status'          => trim($_POST['status'] ?? 'draft'),
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'دوره به‌روزرسانی شد.');
        $this->redirect(hr_url('training', 'show', ['id' => $id]));
    }

    public function delete($id)
    {
        $this->requireAuth();
        $id = (int) $id;
        $model = new Training();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'دوره یافت نشد.');
            $this->redirect(hr_url('training'));
            return;
        }
        $model->delete($id);
        hr_flash_set('success', 'دوره حذف شد.');
        $this->redirect(hr_url('training'));
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