<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\Candidate;
use App\Software\Hr\Models\Recruitment;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * CandidateController - مدیریت متقاضیان استخدام
 * ============================================================
 */
class CandidateController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * لیست متقاضیان
     */
    public function index()
    {
        $this->requireAuth();

        $model = new Candidate();

        $filters = [
            'q'              => trim($_GET['q'] ?? ''),
            'recruitment_id' => !empty($_GET['recruitment_id']) ? (int) $_GET['recruitment_id'] : null,
            'status'         => trim($_GET['status'] ?? ''),
            'source'         => trim($_GET['source'] ?? ''),
            'gender'         => trim($_GET['gender'] ?? ''),
        ];

        $candidates = $model->search($filters);
        $stats = $model->getStats();

        $recModel = new Recruitment();

        $this->renderSoftware('candidate/index', [
            'pageTitle'       => 'متقاضیان استخدام',
            'softwareName'    => 'HR Analyzer',
            'candidates'      => $candidates,
            'filters'         => $filters,
            'stats'           => $stats,
            'hr_recruitments' => $recModel->getSelectList(),
            'statusOptions'   => Candidate::getStatusOptions(),
            'sourceOptions'   => Candidate::getSourceOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();

        $recModel = new Recruitment();
        $model = new Candidate();

        $preselectedRecruitmentId = !empty($_GET['recruitment_id']) ? (int) $_GET['recruitment_id'] : null;

        $this->renderSoftware('candidate/create', [
            'pageTitle'                => 'افزودن متقاضی',
            'softwareName'             => 'HR Analyzer',
            'hr_recruitments'          => $recModel->getSelectList(),
            'preselectedRecruitmentId' => $preselectedRecruitmentId,
            'suggested_code'           => $model->generateCandidateCode(),
            'statusOptions'            => Candidate::getStatusOptions(),
            'sourceOptions'            => Candidate::getSourceOptions(),
            'flash'                    => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('candidate'));
            return;
        }

        $this->verifyCsrf();

        $data = $this->collectPostData();

        if (empty($data['first_name']) || empty($data['last_name'])) {
            hr_flash_set('danger', 'نام و نام خانوادگی الزامی است.');
            $this->redirect(hr_url('candidate', 'create'));
            return;
        }

        $model = new Candidate();

        if (!empty($data['national_id']) && $model->nationalIdExists($data['national_id'])) {
            hr_flash_set('danger', 'کد ملی تکراری است.');
            $this->redirect(hr_url('candidate', 'create'));
            return;
        }

        $newId = $model->create($data);

        hr_flash_set('success', 'متقاضی با موفقیت ثبت شد.');
        $this->redirect(hr_url('candidate', 'show', ['id' => $newId]));
    }

    /**
     * نمایش جزئیات
     */
    public function show($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Candidate();
        $candidate = $model->findWithDetails($id);

        if (!$candidate) {
            hr_flash_set('danger', 'متقاضی یافت نشد.');
            $this->redirect(hr_url('candidate'));
            return;
        }

        // مصاحبه‌ها
        $interviewModel = new \App\Software\Hr\Models\Interview();
        $interviews = $interviewModel->getByCandidate($id);

        $this->renderSoftware('candidate/show', [
            'pageTitle'    => 'جزئیات متقاضی',
            'softwareName' => 'HR Analyzer',
            'candidate'    => $candidate,
            'interviews'   => $interviews,
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
        $model = new Candidate();
        $candidate = $model->find($id);

        if (!$candidate) {
            hr_flash_set('danger', 'متقاضی یافت نشد.');
            $this->redirect(hr_url('candidate'));
            return;
        }

        $recModel = new Recruitment();

        $this->renderSoftware('candidate/edit', [
            'pageTitle'       => 'ویرایش متقاضی',
            'softwareName'    => 'HR Analyzer',
            'candidate'       => $candidate,
            'hr_recruitments' => $recModel->getSelectList(),
            'statusOptions'   => Candidate::getStatusOptions(),
            'sourceOptions'   => Candidate::getSourceOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('candidate'));
            return;
        }

        $this->verifyCsrf();

        $id = (int) $id;
        $model = new Candidate();

        if (!$model->exists($id)) {
            hr_flash_set('danger', 'متقاضی یافت نشد.');
            $this->redirect(hr_url('candidate'));
            return;
        }

        $data = $this->collectPostData();

        if (empty($data['first_name']) || empty($data['last_name'])) {
            hr_flash_set('danger', 'نام و نام خانوادگی الزامی است.');
            $this->redirect(hr_url('candidate', 'edit', ['id' => $id]));
            return;
        }

        if (!empty($data['national_id']) && $model->nationalIdExists($data['national_id'], $id)) {
            hr_flash_set('danger', 'کد ملی تکراری است.');
            $this->redirect(hr_url('candidate', 'edit', ['id' => $id]));
            return;
        }

        $model->update($id, $data);

        // به‌روزرسانی تعداد استخدام‌شده در نیاز استخدامی
        if ($data['status'] === 'hired' && !empty($data['recruitment_id'])) {
            $recModel = new Recruitment();
            $recModel->updateFilledCount((int) $data['recruitment_id']);
        }

        hr_flash_set('success', 'متقاضی به‌روزرسانی شد.');
        $this->redirect(hr_url('candidate', 'show', ['id' => $id]));
    }

    /**
     * حذف
     */
    public function delete($id)
    {
        $this->requireAuth();

        $id = (int) $id;
        $model = new Candidate();

        $candidate = $model->find($id);
        if (!$candidate) {
            hr_flash_set('danger', 'متقاضی یافت نشد.');
            $this->redirect(hr_url('candidate'));
            return;
        }

        $model->delete($id);

        hr_flash_set('success', 'متقاضی حذف شد.');
        $this->redirect(hr_url('candidate'));
    }

    /**
     * جمع‌آوری داده‌ها
     */
    private function collectPostData(): array
    {
        $birthDate = trim($_POST['birth_date'] ?? '');
        $appliedDate = trim($_POST['applied_date'] ?? '');

        return [
            'recruitment_id'   => !empty($_POST['recruitment_id']) ? (int) $_POST['recruitment_id'] : null,
            'candidate_code'   => trim($_POST['candidate_code'] ?? ''),
            'first_name'       => trim($_POST['first_name'] ?? ''),
            'last_name'        => trim($_POST['last_name'] ?? ''),
            'national_id'      => trim($_POST['national_id'] ?? '') ?: null,
            'gender'           => trim($_POST['gender'] ?? 'male'),
            'birth_date'       => $birthDate ? DateHelper::toGregorian($birthDate) : null,
            'mobile'           => trim($_POST['mobile'] ?? '') ?: null,
            'email'            => trim($_POST['email'] ?? '') ?: null,
            'address'          => trim($_POST['address'] ?? '') ?: null,
            'education_level'  => trim($_POST['education_level'] ?? '') ?: null,
            'field_of_study'   => trim($_POST['field_of_study'] ?? '') ?: null,
            'university'       => trim($_POST['university'] ?? '') ?: null,
            'experience_years' => max(0, (int) ($_POST['experience_years'] ?? 0)),
            'current_company'  => trim($_POST['current_company'] ?? '') ?: null,
            'current_position' => trim($_POST['current_position'] ?? '') ?: null,
            'expected_salary'  => !empty($_POST['expected_salary']) ? (float) str_replace(',', '', $_POST['expected_salary']) : null,
            'portfolio_url'    => trim($_POST['portfolio_url'] ?? '') ?: null,
            'source'           => trim($_POST['source'] ?? 'website'),
            'status'           => trim($_POST['status'] ?? 'new'),
            'rating'           => !empty($_POST['rating']) ? (float) $_POST['rating'] : null,
            'notes'            => trim($_POST['notes'] ?? '') ?: null,
            'applied_date'     => $appliedDate ? DateHelper::toGregorian($appliedDate) : date('Y-m-d'),
        ];
    }
}