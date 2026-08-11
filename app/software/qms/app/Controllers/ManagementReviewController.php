<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class ManagementReviewController extends Controller
{
    /**
     * لیست جلسات بازنگری مدیریت
     */
    public function index()
    {
        $this->requireAuth();
        
        $reviews = $this->db->query("
            SELECT mr.*, u.name as created_by_name
            FROM {$this->prefix}management_reviews mr
            LEFT JOIN users u ON mr.user_id = u.id
            ORDER BY mr.review_date DESC
        ")->fetchAll();

        $this->view('management-reviews/index', [
            'pageTitle' => 'بازنگری مدیریت',
            'currentPage' => 'managementreviews',
            'reviews' => $reviews
        ]);
    }

    /**
     * فرم ایجاد بازنگری مدیریت جدید
     */
    public function create()
    {
        $this->requireAuth();
        
        $this->view('management-reviews/create', [
            'pageTitle' => 'ایجاد بازنگری مدیریت جدید',
            'currentPage' => 'managementreviews'
        ]);
    }

    /**
     * ذخیره بازنگری مدیریت
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('managementreviews');
            return;
        }

        $reviewNumber = 'MR-' . date('Y') . '-' . str_pad($this->db->query("SELECT COUNT(*) FROM {$this->prefix}management_reviews")->fetchColumn() + 1, 3, '0', STR_PAD_LEFT);

        $data = [
            'user_id' => $this->currentUserId,
            'review_number' => $reviewNumber,
            'title' => trim($_POST['title'] ?? 'بازنگری مدیریت ' . date('Y')),
            'review_date' => $_POST['review_date'] ?? date('Y-m-d'),
            'meeting_location' => trim($_POST['meeting_location'] ?? ''),
            'attendees' => json_encode($_POST['attendees'] ?? []),
            
            // ورودی‌ها (Inputs) طبق بند 9.3.2
            'previous_actions_status' => trim($_POST['previous_actions_status'] ?? ''),
            'changes_in_context' => trim($_POST['changes_in_context'] ?? ''),
            'performance_effectiveness' => trim($_POST['performance_effectiveness'] ?? ''),
            'resource_adequacy' => trim($_POST['resource_adequacy'] ?? ''),
            'risk_effectiveness' => trim($_POST['risk_effectiveness'] ?? ''),
            'improvement_opportunities_input' => trim($_POST['improvement_opportunities_input'] ?? ''),
            
            // خروجی‌ها (Outputs) طبق بند 9.3.3
            'improvement_actions' => trim($_POST['improvement_actions'] ?? ''),
            'resource_needs' => trim($_POST['resource_needs'] ?? ''),
            'qms_changes' => trim($_POST['qms_changes'] ?? ''),
            'decisions_made' => trim($_POST['decisions_made'] ?? ''),
            
            'status' => $_POST['status'] ?? 'draft'
        ];

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}management_reviews 
            (user_id, review_number, title, review_date, meeting_location, attendees, 
             previous_actions_status, changes_in_context, performance_effectiveness, 
             resource_adequacy, risk_effectiveness, improvement_opportunities_input,
             improvement_actions, resource_needs, qms_changes, decisions_made, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $data['user_id'], $data['review_number'], $data['title'], $data['review_date'], 
            $data['meeting_location'], $data['attendees'], $data['previous_actions_status'], 
            $data['changes_in_context'], $data['performance_effectiveness'], $data['resource_adequacy'], 
            $data['risk_effectiveness'], $data['improvement_opportunities_input'], $data['improvement_actions'], 
            $data['resource_needs'], $data['qms_changes'], $data['decisions_made'], $data['status']
        ]);

        if ($result) {
            $this->logActivity('create_management_review', 'management_review', $this->db->lastInsertId());
            $this->flashSuccess('بازنگری مدیریت با موفقیت ثبت شد.');
            $this->redirect('managementreviews');
        } else {
            $this->flashError('خطا در ثبت بازنگری مدیریت.');
            $this->redirect('managementreviews&action=create');
        }
    }

    /**
     * مشاهده جزئیات بازنگری مدیریت
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT mr.*, u.name as created_by_name
            FROM {$this->prefix}management_reviews mr
            LEFT JOIN users u ON mr.user_id = u.id
            WHERE mr.id = ?
        ");
        $stmt->execute([$id]);
        $review = $stmt->fetch();

        if (!$review) {
            $this->flashError('سابقه بازنگری مدیریت یافت نشد.');
            $this->redirect('managementreviews');
            return;
        }

        $this->view('management-reviews/show', [
            'pageTitle' => 'جزئیات بازنگری مدیریت: ' . $review['review_number'],
            'currentPage' => 'managementreviews',
            'review' => $review
        ]);
    }
}