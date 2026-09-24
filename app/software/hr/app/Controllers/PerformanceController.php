<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\PerformanceReview;
use App\Software\Hr\Models\Employee;

/**
 * ============================================================
 * PerformanceController - ارزیابی عملکرد
 * ============================================================
 */
class PerformanceController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requireAuth();

        $model = new PerformanceReview();
        $filters = [
            'q'            => trim($_GET['q'] ?? ''),
            'employee_id'  => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'reviewer_id'  => !empty($_GET['reviewer_id']) ? (int) $_GET['reviewer_id'] : null,
            'review_type'  => trim($_GET['review_type'] ?? ''),
            'status'       => trim($_GET['status'] ?? ''),
            'rating'       => trim($_GET['rating'] ?? ''),
        ];

        $empModel = new Employee();

        $this->renderSoftware('performance/index', [
            'pageTitle'       => 'ارزیابی عملکرد',
            'softwareName'    => 'HR Analyzer',
            'reviews'         => $model->search($filters),
            'stats'           => $model->getStats(),
            'filters'         => $filters,
            'hr_employees'    => $empModel->getSelectList(),
            'typeOptions'     => PerformanceReview::getTypeOptions(),
            'statusOptions'   => PerformanceReview::getStatusOptions(),
            'ratingOptions'   => PerformanceReview::getRatingOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    public function create()
    {
        $this->requireAuth();

        $empModel = new Employee();

        $this->renderSoftware('performance/create', [
            'pageTitle'             => 'ایجاد ارزیابی عملکرد',
            'softwareName'          => 'HR Analyzer',
            'hr_employees'          => $empModel->getSelectList(),
            'hr_reviewers'          => $empModel->getSelectList(),
            'typeOptions'           => PerformanceReview::getTypeOptions(),
            'statusOptions'         => PerformanceReview::getStatusOptions(),
            'ratingOptions'         => PerformanceReview::getRatingOptions(),
            'recommendationOptions' => PerformanceReview::getRecommendationOptions(),
            'scoreFields'           => PerformanceReview::getScoreFields(),
            'preselectedEmployeeId' => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('performance'));
            return;
        }
        $this->verifyCsrf();

        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $periodStart = trim($_POST['period_start'] ?? '');
        $periodEnd   = trim($_POST['period_end'] ?? '');

        if ($employeeId === 0 || $periodStart === '' || $periodEnd === '') {
            hr_flash_set('danger', 'کارمند، ابتدا و انتهای دوره الزامی است.');
            $this->redirect(hr_url('performance', 'create'));
            return;
        }

        $startG = $this->convertJalaliDate($periodStart);
        $endG   = $this->convertJalaliDate($periodEnd);
        if (!$startG || !$endG) {
            hr_flash_set('danger', 'تاریخ نامعتبر است.');
            $this->redirect(hr_url('performance', 'create'));
            return;
        }

        $model = new PerformanceReview();
        $newId = $model->create($this->collectPayload($startG, $endG));

        // محاسبه overall و rating
        $model->recalcOverall($newId);
        $review = $model->find($newId);
        if ($review && $review['overall_score'] !== null) {
            $model->update($newId, [
                'rating' => PerformanceReview::scoreToRating((float) $review['overall_score']),
            ]);
        }

        hr_flash_set('success', 'ارزیابی با موفقیت ایجاد شد.');
        $this->redirect(hr_url('performance', 'show', ['id' => $newId]));
    }

    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new PerformanceReview();
        $review = $model->findWithDetails($id);

        if (!$review) {
            hr_flash_set('danger', 'ارزیابی یافت نشد.');
            $this->redirect(hr_url('performance'));
            return;
        }

        $this->renderSoftware('performance/show', [
            'pageTitle'       => 'جزئیات ارزیابی',
            'softwareName'    => 'HR Analyzer',
            'review'          => $review,
            'scoreFields'     => PerformanceReview::getScoreFields(),
            'ratingOptions'   => PerformanceReview::getRatingOptions(),
            'recommendationOptions' => PerformanceReview::getRecommendationOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new PerformanceReview();
        $review = $model->find($id);

        if (!$review) {
            hr_flash_set('danger', 'ارزیابی یافت نشد.');
            $this->redirect(hr_url('performance'));
            return;
        }

        $empModel = new Employee();

        $this->renderSoftware('performance/edit', [
            'pageTitle'             => 'ویرایش ارزیابی',
            'softwareName'          => 'HR Analyzer',
            'review'                => $review,
            'hr_employees'          => $empModel->getSelectList(),
            'hr_reviewers'          => $empModel->getSelectList(),
            'typeOptions'           => PerformanceReview::getTypeOptions(),
            'statusOptions'         => PerformanceReview::getStatusOptions(),
            'ratingOptions'         => PerformanceReview::getRatingOptions(),
            'recommendationOptions' => PerformanceReview::getRecommendationOptions(),
            'scoreFields'           => PerformanceReview::getScoreFields(),
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    public function update($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('performance'));
            return;
        }
        $this->verifyCsrf();

        $id = (int) $id;
        $model = new PerformanceReview();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'ارزیابی یافت نشد.');
            $this->redirect(hr_url('performance'));
            return;
        }

        $startG = $this->convertJalaliDate(trim($_POST['period_start'] ?? ''));
        $endG   = $this->convertJalaliDate(trim($_POST['period_end'] ?? ''));

        $model->update($id, $this->collectPayload($startG, $endG));

        // محاسبه overall و rating
        $model->recalcOverall($id);
        $review = $model->find($id);
        if ($review && $review['overall_score'] !== null) {
            $model->update($id, [
                'rating' => PerformanceReview::scoreToRating((float) $review['overall_score']),
            ]);
        }

        hr_flash_set('success', 'ارزیابی به‌روزرسانی شد.');
        $this->redirect(hr_url('performance', 'show', ['id' => $id]));
    }

    public function delete($id)
    {
        $this->requireAuth();
        $id = (int) $id;
        $model = new PerformanceReview();
        if (!$model->exists($id)) {
            hr_flash_set('danger', 'ارزیابی یافت نشد.');
            $this->redirect(hr_url('performance'));
            return;
        }
        $model->delete($id);
        hr_flash_set('success', 'ارزیابی حذف شد.');
        $this->redirect(hr_url('performance'));
    }

    /**
     * جمع‌آوری داده‌های POST برای درج/به‌روزرسانی
     */
    private function collectPayload(?string $startG, ?string $endG): array
    {
        $scoreFields = array_keys(PerformanceReview::getScoreFields());
        $scores = [];
        foreach ($scoreFields as $f) {
            $scores[$f] = $this->sanitizeScore($_POST[$f] ?? null);
        }

        return array_merge($scores, [
            'employee_id'        => (int) ($_POST['employee_id'] ?? 0),
            'reviewer_id'        => !empty($_POST['reviewer_id']) ? (int) $_POST['reviewer_id'] : null,
            'review_period'      => trim($_POST['review_period'] ?? ''),
            'period_start'       => $startG,
            'period_end'         => $endG,
            'review_type'        => trim($_POST['review_type'] ?? 'annual'),
            'strengths'          => trim($_POST['strengths'] ?? '') ?: null,
            'weaknesses'         => trim($_POST['weaknesses'] ?? '') ?: null,
            'goals_achievement'  => trim($_POST['goals_achievement'] ?? '') ?: null,
            'training_needs'     => trim($_POST['training_needs'] ?? '') ?: null,
            'manager_comments'   => trim($_POST['manager_comments'] ?? '') ?: null,
            'employee_comments'  => trim($_POST['employee_comments'] ?? '') ?: null,
            'recommendation'     => trim($_POST['recommendation'] ?? '') ?: null,
            'status'             => trim($_POST['status'] ?? 'draft'),
        ]);
    }

    /**
     * اعتبارسنجی امتیاز (۰ تا ۵)
     */
    private function sanitizeScore($value): ?float
    {
        if ($value === null || $value === '') return null;
        $score = (float) $value;
        if ($score < 0 || $score > 5) return null;
        return round($score, 2);
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