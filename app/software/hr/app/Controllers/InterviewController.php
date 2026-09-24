<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Interview;
use App\Software\Hr\Models\Candidate;
use App\Software\Hr\Models\Recruitment;
use App\Software\Hr\Models\Employee;

/**
 * ============================================================
 * InterviewController - مدیریت مصاحبه‌ها
 * ============================================================
 */
class InterviewController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست مصاحبه‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Interview();

        $filters = [
            'candidate_id'    => !empty($_GET['candidate_id']) ? (int) $_GET['candidate_id'] : null,
            'recruitment_id'  => !empty($_GET['recruitment_id']) ? (int) $_GET['recruitment_id'] : null,
            'status'          => trim($_GET['status'] ?? ''),
            'interview_type'  => trim($_GET['interview_type'] ?? ''),
            'date_from'       => trim($_GET['date_from'] ?? ''),
            'date_to'         => trim($_GET['date_to'] ?? ''),
        ];

        $interviews = $model->search($filters);
        $stats = $model->getStats();
        $todayInterviews = $model->getToday();

        $recModel = new Recruitment();

        $this->renderSoftware('interview/index', [
            'pageTitle'       => 'مصاحبه‌ها',
            'softwareName'    => 'HR Analyzer',
            'interviews'      => $interviews,
            'stats'           => $stats,
            'todayInterviews' => $todayInterviews,
            'filters'         => $filters,
            'hr_recruitments' => $recModel->getSelectList(),
            'typeOptions'     => Interview::getTypeOptions(),
            'statusOptions'   => Interview::getStatusOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $candModel = new Candidate();
        $recModel = new Recruitment();
        $empModel = new Employee();

        $preselectedCandidateId = !empty($_GET['candidate_id']) ? (int) $_GET['candidate_id'] : null;

        // دریافت لیست متقاضیان
        $candidates = $candModel->search([]);
        $candidateList = array_map(function ($c) {
            return [
                'id' => $c['id'],
                'full_name' => $c['full_name'],
                'candidate_code' => $c['candidate_code'],
            ];
        }, $candidates);

        $this->renderSoftware('interview/create', [
            'pageTitle'              => 'برنامه‌ریزی مصاحبه',
            'softwareName'           => 'HR Analyzer',
            'hr_candidates'          => $candidateList,
            'hr_recruitments'        => $recModel->getSelectList(),
            'hr_interviewers'        => $empModel->getSelectList(),
            'preselectedCandidateId' => $preselectedCandidateId,
            'typeOptions'            => Interview::getTypeOptions(),
            'statusOptions'          => Interview::getStatusOptions(),
            'recommendationOptions'  => Interview::getRecommendationOptions(),
            'flash'                  => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('interview'));
            return;
        }

        $this->verifyCsrf();

        $candidateId  = (int) ($_POST['candidate_id'] ?? 0);
        $scheduledDay = trim($_POST['scheduled_date'] ?? '');   // 1404/07/15
        $scheduledH   = trim($_POST['scheduled_hour'] ?? '');   // 14
        $scheduledM   = trim($_POST['scheduled_minute'] ?? ''); // 30

        if ($candidateId === 0 || empty($scheduledDay)) {
            hr_flash_set('danger', 'متقاضی و تاریخ مصاحبه الزامی است.');
            $this->redirect(hr_url('interview', 'create'));
            return;
        }

        // ترکیب تاریخ + ساعت و تبدیل به میلادی
        $scheduledDateG = $this->convertJalaliToGregorianWithTime(
            $scheduledDay,
            $scheduledH,
            $scheduledM
        );

        if (!$scheduledDateG) {
            hr_flash_set('danger', 'تاریخ مصاحبه نامعتبر است.');
            $this->redirect(hr_url('interview', 'create'));
            return;
        }

        $model = new Interview();
        $newId = $model->create([
            'candidate_id'     => $candidateId,
            'recruitment_id'   => !empty($_POST['recruitment_id']) ? (int) $_POST['recruitment_id'] : null,
            'interview_type'   => trim($_POST['interview_type'] ?? 'in_person'),
            'round'            => max(1, (int) ($_POST['round'] ?? 1)),
            'interviewer_id'   => !empty($_POST['interviewer_id']) ? (int) $_POST['interviewer_id'] : null,
            'scheduled_date'   => $scheduledDateG,
            'duration_minutes' => max(15, (int) ($_POST['duration_minutes'] ?? 60)),
            'location'         => trim($_POST['location'] ?? '') ?: null,
            'meeting_link'     => trim($_POST['meeting_link'] ?? '') ?: null,
            'status'           => trim($_POST['status'] ?? 'scheduled'),
            'notes'            => trim($_POST['notes'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'مصاحبه با موفقیت برنامه‌ریزی شد.');
        $this->redirect(hr_url('interview', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Interview();
        $interview = $model->findWithDetails($id);

        if (!$interview) {
            hr_flash_set('danger', 'مصاحبه یافت نشد.');
            $this->redirect(hr_url('interview'));
            return;
        }

        $this->renderSoftware('interview/show', [
            'pageTitle'    => 'جزئیات مصاحبه',
            'softwareName' => 'HR Analyzer',
            'interview'    => $interview,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ویرایش
     */
    public function edit($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Interview();
        $interview = $model->find($id);

        if (!$interview) {
            hr_flash_set('danger', 'مصاحبه یافت نشد.');
            $this->redirect(hr_url('interview'));
            return;
        }

        $candModel = new Candidate();
        $recModel = new Recruitment();
        $empModel = new Employee();

        $candidates = $candModel->search([]);
        $candidateList = array_map(function ($c) {
            return [
                'id' => $c['id'],
                'full_name' => $c['full_name'],
                'candidate_code' => $c['candidate_code'],
            ];
        }, $candidates);

        $this->renderSoftware('interview/edit', [
            'pageTitle'             => 'ویرایش مصاحبه',
            'softwareName'          => 'HR Analyzer',
            'interview'             => $interview,
            'hr_candidates'         => $candidateList,
            'hr_recruitments'       => $recModel->getSelectList(),
            'hr_interviewers'       => $empModel->getSelectList(),
            'typeOptions'           => Interview::getTypeOptions(),
            'statusOptions'         => Interview::getStatusOptions(),
            'recommendationOptions' => Interview::getRecommendationOptions(),
            'flash'                 => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('interview'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Interview();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'مصاحبه یافت نشد.');
            $this->redirect(hr_url('interview'));
            return;
        }

        $candidateId  = (int) ($_POST['candidate_id'] ?? 0);
        $scheduledDay = trim($_POST['scheduled_date'] ?? '');
        $scheduledH   = trim($_POST['scheduled_hour'] ?? '');
        $scheduledM   = trim($_POST['scheduled_minute'] ?? '');

        if ($candidateId === 0 || empty($scheduledDay)) {
            hr_flash_set('danger', 'متقاضی و تاریخ مصاحبه الزامی است.');
            $this->redirect(hr_url('interview', 'edit', ['id' => $id]));
            return;
        }

        $scheduledDateG = $this->convertJalaliToGregorianWithTime(
            $scheduledDay,
            $scheduledH,
            $scheduledM
        );

        if (!$scheduledDateG) {
            hr_flash_set('danger', 'تاریخ مصاحبه نامعتبر است.');
            $this->redirect(hr_url('interview', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, [
            'candidate_id'        => $candidateId,
            'recruitment_id'      => !empty($_POST['recruitment_id']) ? (int) $_POST['recruitment_id'] : null,
            'interview_type'      => trim($_POST['interview_type'] ?? 'in_person'),
            'round'               => max(1, (int) ($_POST['round'] ?? 1)),
            'interviewer_id'      => !empty($_POST['interviewer_id']) ? (int) $_POST['interviewer_id'] : null,
            'scheduled_date'      => $scheduledDateG,
            'duration_minutes'    => max(15, (int) ($_POST['duration_minutes'] ?? 60)),
            'location'            => trim($_POST['location'] ?? '') ?: null,
            'meeting_link'        => trim($_POST['meeting_link'] ?? '') ?: null,
            'status'              => trim($_POST['status'] ?? 'scheduled'),
            'technical_score'     => !empty($_POST['technical_score']) ? (float) $_POST['technical_score'] : null,
            'communication_score' => !empty($_POST['communication_score']) ? (float) $_POST['communication_score'] : null,
            'culture_fit_score'   => !empty($_POST['culture_fit_score']) ? (float) $_POST['culture_fit_score'] : null,
            'overall_score'       => !empty($_POST['overall_score']) ? (float) $_POST['overall_score'] : null,
            'strengths'           => trim($_POST['strengths'] ?? '') ?: null,
            'weaknesses'          => trim($_POST['weaknesses'] ?? '') ?: null,
            'notes'               => trim($_POST['notes'] ?? '') ?: null,
            'recommendation'      => trim($_POST['recommendation'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'مصاحبه به‌روزرسانی شد.');
        $this->redirect(hr_url('interview', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Interview();

        $interview = $model->find($id);
        if (!$interview) {
            hr_flash_set('danger', 'مصاحبه یافت نشد.');
            $this->redirect(hr_url('interview'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'مصاحبه حذف شد.');
        $this->redirect(hr_url('interview'));
    }

    /**
     * تبدیل datetime شمسی به میلادی
     * فرمت ورودی: 1404/07/01 14:30
     */
    private function convertJalaliDateTime(string $input): ?string
    {
        $input = trim($input);
        if (empty($input)) return null;

        // جدا کردن تاریخ و زمان
        $parts = preg_split('/\s+/', $input);
        $datePart = $parts[0] ?? '';
        $timePart = $parts[1] ?? '00:00';

        $gDate = \App\Helpers\DateHelper::toGregorian($datePart);
        if (!$gDate) return null;

        return $gDate . ' ' . $timePart . ':00';
    }

    /**
     * تبدیل تاریخ شمسی + ساعت به datetime میلادی
     * فرمت ورودی: 
     *   $jalaliDate  = "1404/07/15"
     *   $hour        = "14"
     *   $minute      = "30"
     * خروجی: "2025-10-07 14:30:00"
     */
    private function convertJalaliToGregorianWithTime(string $jalaliDate, string $hour, string $minute): ?string
    {
        $jalaliDate = trim($jalaliDate);
        if ($jalaliDate === '') {
            return null;
        }

        // نرمال‌سازی اعداد فارسی/عربی به انگلیسی
        $jalaliDate = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $jalaliDate
        );

        // جداکننده‌ها: / یا -
        $jalaliDate = str_replace('-', '/', $jalaliDate);
        $parts = explode('/', $jalaliDate);

        if (count($parts) !== 3) {
            return null;
        }

        $jy = (int) $parts[0];
        $jm = (int) $parts[1];
        $jd = (int) $parts[2];

        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        // تبدیل شمسی → میلادی
        list($gy, $gm, $gd) = hr_jalali_to_gregorian($jy, $jm, $jd);

        // اعتبارسنجی ساعت و دقیقه
        $hour   = str_pad(preg_replace('/\D/', '', $hour ?: '00'), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(preg_replace('/\D/', '', $minute ?: '00'), 2, '0', STR_PAD_LEFT);

        $h = (int) $hour;
        $m = (int) $minute;

        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
            return null;
        }

        return sprintf('%04d-%02d-%02d %02d:%02d:00', $gy, $gm, $gd, $h, $m);
    }
}