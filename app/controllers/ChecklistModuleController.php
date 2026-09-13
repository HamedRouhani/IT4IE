<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Checklist;
use App\Models\Setting;
use App\Models\User;
use App\Models\Message;

class ChecklistModuleController extends Controller
{
    private $checklistModel;

    public function __construct()
    {
        $this->checklistModel = new Checklist();
    }

    /**
     * لیست همه چک‌لیست‌ها (قابل مشاهده برای همه، حتی مهمان)
     */
    public function index()
    {
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        $checklists = $this->checklistModel->getAllChecklists(false);

        $this->render('checklist/list', [
            'title' => 'چک‌لیست‌های تخصصی - IT4IE',
            'settings' => $settings,
            'checklists' => $checklists,
            'hideSidebar' => true,
            'hideFooter' => true
        ]);
    }

    /**
     * نمایش یک چک‌لیست خاص
     */
    public function view($slug)
    {
        $checklist = $this->checklistModel->getChecklistBySlug($slug);
        
        if (!$checklist) {
            http_response_code(404);
            echo "چک‌لیست یافت نشد";
            exit;
        }

        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        $questions = $this->checklistModel->getQuestionsByCategory($checklist['id']);

        // بررسی وضعیت لاگین کاربر
        $isLoggedIn = isset($_SESSION['user_id']);

        // اگر کاربر لاگین کرده، بررسی کنیم آیا قبلاً این چک‌لیست را پر کرده یا نه
        $userSubmissions = [];
        if ($isLoggedIn) {
            $userSubmissions = $this->checklistModel->getUserSubmissions($_SESSION['user_id']);
            $userSubmissions = array_filter($userSubmissions, fn($s) => $s['checklist_id'] == $checklist['id']);
        }

        $this->render('checklist/view', [
            'title' => $checklist['title'] . ' - IT4IE',
            'settings' => $settings,
            'checklist' => $checklist,
            'questions' => $questions,
            'isLoggedIn' => $isLoggedIn,
            'userSubmissions' => $userSubmissions,
            'hideSidebar' => true,
            'hideFooter' => true
        ]);
    }

    /**
     * ذخیره پاسخ‌های چک‌لیست (فقط کاربران لاگین‌شده)
     */
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checklist');
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'برای پر کردن چک‌لیست، لطفاً ابتدا وارد حساب کاربری خود شوید.';
            $this->redirect('/login');
            return;
        }

        $checklistId = (int)($_POST['checklist_id'] ?? 0);
        $checklist = $this->checklistModel->getChecklist($checklistId);
        
        if (!$checklist) {
            $_SESSION['error'] = 'چک‌لیست یافت نشد.';
            $this->redirect('/checklist');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            $_SESSION['error'] = 'کاربر یافت نشد.';
            $this->redirect('/login');
            return;
        }

        $company = trim($_POST['company'] ?? 'تعریف نشده');

        $answers = [];
        $questions = $this->checklistModel->getQuestionsForChecklist($checklistId);
        $totalScore = 0;
        $maxScore = 0;

        foreach ($questions as $q) {
            $answer = (int)($_POST['q_' . $q['id']] ?? 0);
            if ($answer < 0) $answer = 0;
            if ($answer > 3) $answer = 3;
            
            $answers[$q['id']] = $answer;
            $totalScore += $answer * $q['weight'];
            $maxScore += 3 * $q['weight'];
        }

        $recommendations = $this->checklistModel->analyzeAnswers($answers, $questions, $checklistId);
        $riskLevel = $this->checklistModel->calculateRiskLevel($totalScore, $maxScore);

        $data = [
            'user_id' => (int)$_SESSION['user_id'],
            'checklist_id' => $checklistId,
            'name' => $_SESSION['user_name'] ?? $user['name'] ?? 'کاربر',
            'email' => $_SESSION['user_email'] ?? $user['email'] ?? 'no-email@it4ie.ir',
            'phone' => $user['phone'] ?? 'ندارد',
            'company' => $company,
            'total_score' => (int)$totalScore,
            'max_score' => (int)$maxScore,
            'risk_level' => $riskLevel,
            'answers' => json_encode($answers, JSON_UNESCAPED_UNICODE),
            'recommendations' => json_encode($recommendations, JSON_UNESCAPED_UNICODE),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        try {
            $result = $this->checklistModel->createSubmission($data);

            if ($result) {
                $_SESSION['checklist_result'] = [
                    'checklist_id' => $checklistId,
                    'checklist_slug' => $checklist['slug'],
                    'checklist_title' => $checklist['title'],
                    'risk_level' => $riskLevel,
                    'percentage' => $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0,
                    'total_score' => $totalScore,
                    'max_score' => $maxScore,
                    'recommendations' => $recommendations,
                    'name' => $data['name']
                ];
                $this->redirect('/checklist/result/' . $checklist['slug']);
            } else {
                throw new \Exception("ذخیره ناموفق");
            }
        } catch (\Exception $e) {
            error_log("Checklist Submit Error: " . $e->getMessage());
            $_SESSION['error'] = 'خطا در ذخیره نتایج.';
            $this->redirect('/checklist/view/' . $checklist['slug']);
        }
    }

    /**
     * نمایش نتیجه چک‌لیست
     */
    public function result($slug)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        if (!isset($_SESSION['checklist_result']) || $_SESSION['checklist_result']['checklist_slug'] !== $slug) {
            $this->redirect('/checklist');
            return;
        }

        $result = $_SESSION['checklist_result'];
        unset($_SESSION['checklist_result']);

        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        $overallRec = $this->checklistModel->getOverallRecommendation(
            $result['risk_level'], 
            $result['percentage']
        );

        $this->render('checklist/result', [
            'title' => 'نتایج ' . $result['checklist_title'] . ' - IT4IE',
            'settings' => $settings,
            'result' => $result,
            'overallRec' => $overallRec,
            'hideSidebar' => true,
            'hideFooter' => true
        ]);
    }

    /**
     * نمایش تاریخچه چک‌لیست‌های کاربر
     */
    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'ابتدا وارد شوید.';
            $this->redirect('/login');
            return;
        }

        $submissions = $this->checklistModel->getUserSubmissions($_SESSION['user_id']);

        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        $this->render('checklist/history', [
            'title' => 'تاریخچه چک‌لیست‌های من - IT4IE',
            'settings' => $settings,
            'submissions' => $submissions,
            'hideSidebar' => true,
            'hideFooter' => true
        ]);
    }

    /**
     * 🗑 حذف یک ارزیابی از تاریخچه (فقط مالک رکورد)
     */
    public function deleteSubmission($id)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checklist/history');
            return;
        }

        $sub = $this->checklistModel->getSubmission($id);

        // فقط مالک رکورد اجازه حذف دارد
        if (!$sub || (int)$sub['user_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = 'دسترسی غیرمجاز یا رکورد یافت نشد.';
            $this->redirect('/checklist/history');
            return;
        }

        $this->checklistModel->deleteSubmission($id);
        $_SESSION['message'] = 'ارزیابی از تاریخچه شما حذف شد.';
        $this->redirect('/checklist/history');
    }
}