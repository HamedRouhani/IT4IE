<?php
namespace App\Models;

use App\Core\Model;

class Checklist extends Model
{
    protected $table = 'checklist_submissions';
    protected $checklistsTable = 'checklists';
    protected $questionsTable = 'checklist_questions';

    // ============================================
    // ۱. مدیریت چک‌لیست‌ها (Checklists)
    // ============================================

    public function getAllChecklists($includeInactive = false)
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM {$this->questionsTable} WHERE checklist_id = c.id) as questions_count,
                       (SELECT COUNT(*) FROM {$this->table} WHERE checklist_id = c.id) as submissions_count
                FROM {$this->checklistsTable} c";
        
        if (!$includeInactive) {
            $sql .= " WHERE c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.sort_order ASC, c.created_at DESC";
        
        $result = $this->query($sql);
        return is_array($result) ? $result : [];
    }

    public function getChecklist($id)
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM {$this->questionsTable} WHERE checklist_id = c.id) as questions_count
                FROM {$this->checklistsTable} c 
                WHERE c.id = :id";
        
        $result = $this->query($sql, [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function getChecklistBySlug($slug)
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM {$this->questionsTable} WHERE checklist_id = c.id) as questions_count
                FROM {$this->checklistsTable} c 
                WHERE c.slug = :slug AND c.is_active = 1";
        
        $result = $this->query($sql, [':slug' => $slug]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function createChecklist($data)
    {
        $sql = "INSERT INTO {$this->checklistsTable} 
                (title, slug, description, category, icon, estimated_time, is_active, is_featured, sort_order) 
                VALUES 
                (:title, :slug, :description, :category, :icon, :estimated_time, :is_active, :is_featured, :sort_order)";
        
        try {
            $this->query($sql, [
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? '',
                ':category' => $data['category'] ?? 'general',
                ':icon' => $data['icon'] ?? 'fa-clipboard-list',
                ':estimated_time' => (int)($data['estimated_time'] ?? 5),
                ':is_active' => (int)($data['is_active'] ?? 1),
                ':is_featured' => (int)($data['is_featured'] ?? 0),
                ':sort_order' => (int)($data['sort_order'] ?? 0)
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::createChecklist ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function updateChecklist($id, $data)
    {
        $sql = "UPDATE {$this->checklistsTable} 
                SET title = :title, slug = :slug, description = :description, 
                    category = :category, icon = :icon, estimated_time = :estimated_time,
                    is_active = :is_active, is_featured = :is_featured, sort_order = :sort_order
                WHERE id = :id";
        
        try {
            $this->query($sql, [
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? '',
                ':category' => $data['category'] ?? 'general',
                ':icon' => $data['icon'] ?? 'fa-clipboard-list',
                ':estimated_time' => (int)($data['estimated_time'] ?? 5),
                ':is_active' => (int)($data['is_active'] ?? 1),
                ':is_featured' => (int)($data['is_featured'] ?? 0),
                ':sort_order' => (int)($data['sort_order'] ?? 0),
                ':id' => (int)$id
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::updateChecklist ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteChecklist($id)
    {
        try {
            $this->query("DELETE FROM {$this->questionsTable} WHERE checklist_id = :id", [':id' => (int)$id]);
            $this->query("DELETE FROM {$this->table} WHERE checklist_id = :id", [':id' => (int)$id]);
            $this->query("DELETE FROM {$this->checklistsTable} WHERE id = :id", [':id' => (int)$id]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::deleteChecklist ERROR: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================
    // ۲. مدیریت سوالات (Questions)
    // ============================================

    public function getQuestionsForChecklist($checklistId)
    {
        $sql = "SELECT * FROM {$this->questionsTable} 
                WHERE checklist_id = :checklist_id AND is_active = 1 
                ORDER BY sort_order ASC, id ASC";
        
        $result = $this->query($sql, [':checklist_id' => (int)$checklistId]);
        return is_array($result) ? $result : [];
    }

    public function getQuestionsByCategory($checklistId)
    {
        $questions = $this->getQuestionsForChecklist($checklistId);
        $grouped = [];
        
        foreach ($questions as $q) {
            $grouped[$q['category']][] = $q;
        }
        return $grouped;
    }

    public function getQuestion($id)
    {
        $sql = "SELECT * FROM {$this->questionsTable} WHERE id = :id";
        $result = $this->query($sql, [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function getOrderedQuestions($checklistId)
    {
        $sql = "SELECT * FROM {$this->questionsTable} 
                WHERE checklist_id = :cid 
                ORDER BY sort_order ASC, id ASC";
        $result = $this->query($sql, [':cid' => (int)$checklistId]);
        return is_array($result) ? $result : [];
    }

    public function createQuestion($data)
    {
        $sql = "INSERT INTO {$this->questionsTable} 
                (checklist_id, category, question_text, weight, sort_order, is_active) 
                VALUES 
                (:checklist_id, :category, :question_text, :weight, :sort_order, :is_active)";
        
        try {
            $this->query($sql, [
                ':checklist_id' => (int)$data['checklist_id'],
                ':category' => $data['category'],
                ':question_text' => $data['question_text'],
                ':weight' => (int)($data['weight'] ?? 1),
                ':sort_order' => (int)($data['sort_order'] ?? 0),
                ':is_active' => (int)($data['is_active'] ?? 1)
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::createQuestion ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function updateQuestion($id, $data)
    {
        $sql = "UPDATE {$this->questionsTable} 
                SET category = :category, question_text = :question_text, 
                    weight = :weight, sort_order = :sort_order, is_active = :is_active
                WHERE id = :id";
        
        try {
            $this->query($sql, [
                ':category' => $data['category'],
                ':question_text' => $data['question_text'],
                ':weight' => (int)($data['weight'] ?? 1),
                ':sort_order' => (int)($data['sort_order'] ?? 0),
                ':is_active' => (int)($data['is_active'] ?? 1),
                ':id' => (int)$id
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::updateQuestion ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteQuestion($id)
    {
        try {
            $this->query("DELETE FROM {$this->questionsTable} WHERE id = :id", [':id' => (int)$id]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::deleteQuestion ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function setSortOrder($id, $order)
    {
        try {
            $this->query(
                "UPDATE {$this->questionsTable} SET sort_order = :o WHERE id = :id",
                [':o' => (int)$order, ':id' => (int)$id]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::setSortOrder ERROR: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================
    // ۳. مدیریت ارسال‌ها و نتایج (Submissions)
    // ============================================

    public function createSubmission($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, checklist_id, name, email, phone, company, total_score, max_score, 
                 risk_level, answers, recommendations, ip_address, user_agent) 
                VALUES 
                (:user_id, :checklist_id, :name, :email, :phone, :company, :total_score, :max_score, 
                 :risk_level, :answers, :recommendations, :ip_address, :user_agent)";
        
        try {
            $this->query($sql, [
                ':user_id' => (int)$data['user_id'],
                ':checklist_id' => (int)$data['checklist_id'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':company' => $data['company'],
                ':total_score' => (int)$data['total_score'],
                ':max_score' => (int)$data['max_score'],
                ':risk_level' => $data['risk_level'],
                ':answers' => $data['answers'],
                ':recommendations' => $data['recommendations'],
                ':ip_address' => $data['ip_address'],
                ':user_agent' => $data['user_agent']
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::createSubmission ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllSubmissions($limit = 50, $offset = 0, $checklistId = null)
    {
        $sql = "SELECT s.*, c.title as checklist_title, u.name as user_name, u.email as user_email 
                FROM {$this->table} s 
                LEFT JOIN {$this->checklistsTable} c ON s.checklist_id = c.id
                LEFT JOIN users u ON s.user_id = u.id ";
        
        $params = [];
        if ($checklistId !== null) {
            $sql .= " WHERE s.checklist_id = :checklist_id";
            $params[':checklist_id'] = (int)$checklistId;
        }
        
        $sql .= " ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;
        
        $result = $this->query($sql, $params);
        return is_array($result) ? $result : [];
    }

    public function getUserSubmissions($userId)
    {
        $sql = "SELECT s.*, c.title as checklist_title, c.slug as checklist_slug
                FROM {$this->table} s 
                LEFT JOIN {$this->checklistsTable} c ON s.checklist_id = c.id
                WHERE s.user_id = :user_id 
                ORDER BY s.created_at DESC";
        
        $result = $this->query($sql, [':user_id' => (int)$userId]);
        return is_array($result) ? $result : [];
    }

    public function getSubmission($id)
    {
        $sql = "SELECT s.*, c.title as checklist_title, u.name as user_name, u.email as user_email 
                FROM {$this->table} s 
                LEFT JOIN {$this->checklistsTable} c ON s.checklist_id = c.id
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE s.id = :id";
        
        $result = $this->query($sql, [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function updateStatus($id, $status, $notes = null)
    {
        $sql = "UPDATE {$this->table} 
                SET status = :status, admin_notes = :notes 
                WHERE id = :id";
        
        try {
            $this->query($sql, [
                ':status' => $status,
                ':notes' => $notes,
                ':id' => (int)$id
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::updateStatus ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteSubmission($id)
    {
        try {
            $this->query("DELETE FROM {$this->table} WHERE id = :id", [':id' => (int)$id]);
            return true;
        } catch (\Throwable $e) {
            error_log('Checklist::deleteSubmission ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function getStats($checklistId = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN risk_level = 'critical' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN risk_level = 'high' THEN 1 ELSE 0 END) as high,
                    SUM(CASE WHEN risk_level = 'medium' THEN 1 ELSE 0 END) as medium,
                    SUM(CASE WHEN risk_level = 'low' THEN 1 ELSE 0 END) as low,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count
                FROM {$this->table}";
        
        $params = [];
        if ($checklistId !== null) {
            $sql .= " WHERE checklist_id = :checklist_id";
            $params[':checklist_id'] = (int)$checklistId;
        }
        
        $result = $this->query($sql, $params);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    // ============================================
    // ۴. منطق تحلیل و محاسبات (Analysis Logic)
    // ============================================

    public function analyzeAnswers($answers, $questions)
    {
        $categoryScores = [];
        $categoryMaxScores = [];
        $recommendations = [];
        
        $categoryInfo = [
            'sign1' => [
                'title' => 'مدیریت بودجه و زمان پروژه',
                'icon' => 'fa-clock',
                'recommendations' => [
                    'استفاده از تکنیک‌های Agile برای مدیریت تغییرات',
                    'تعریف فازهای کوتاه‌مدت با خروجی‌های مشخص',
                    'بازنگری ماهانه بودجه و زمان‌بندی با ذی‌نفعان'
                ]
            ],
            'sign2' => [
                'title' => 'تحلیل نیازمندی‌ها و ارتباط تیم‌ها',
                'icon' => 'fa-comments',
                'recommendations' => [
                    'استفاده از استاندارد BABOK برای مستندسازی نیازمندی‌ها',
                    'برگزاری جلسات منظم بین تیم فنی و کسب‌وکار',
                    'استفاده از User Stories و Acceptance Criteria'
                ]
            ],
            'sign3' => [
                'title' => 'مستندسازی و بهینه‌سازی فرآیندها',
                'icon' => 'fa-project-diagram',
                'recommendations' => [
                    'ترسیم نقشه فرآیند وضع موجود (As-Is) با BPMN',
                    'استفاده از Process Mining برای شناسایی گلوگاه‌ها',
                    'بازطراحی فرآیند (To-Be) قبل از اتوماسیون'
                ]
            ],
            'sign4' => [
                'title' => 'مشارکت ذی‌نفعان و مدیریت تغییر',
                'icon' => 'fa-users',
                'recommendations' => [
                    'شناسایی و درگیر کردن Champions در هر دپارتمان',
                    'برنامه‌ریزی آموزش تدریجی کاربران',
                    'ایجاد تیم راهنمای تغییر (Change Agents)'
                ]
            ],
            'sign5' => [
                'title' => 'تعریف معیارهای موفقیت',
                'icon' => 'fa-chart-line',
                'recommendations' => [
                    'تعریف KPIهای SMART برای پروژه',
                    'محاسبه ROI قبل و بعد از استقرار',
                    'ایجاد داشبورد مدیریتی برای پایش مستمر'
                ]
            ]
        ];

        if (!is_array($questions)) return $recommendations;

        foreach ($questions as $q) {
            $cat = $q['category'];
            if (!isset($categoryScores[$cat])) {
                $categoryScores[$cat] = 0;
                $categoryMaxScores[$cat] = 0;
            }
            
            $answerValue = $answers[$q['id']] ?? 0;
            $categoryScores[$cat] += $answerValue * $q['weight'];
            $categoryMaxScores[$cat] += 3 * $q['weight'];
        }

        foreach ($categoryScores as $cat => $score) {
            $max = $categoryMaxScores[$cat];
            $percentage = $max > 0 ? ($score / $max) * 100 : 0;
            
            $level = 'low';
            if ($percentage >= 70) $level = 'critical';
            elseif ($percentage >= 50) $level = 'high';
            elseif ($percentage >= 30) $level = 'medium';
            
            $recommendations[$cat] = [
                'title' => $categoryInfo[$cat]['title'] ?? $cat,
                'icon' => $categoryInfo[$cat]['icon'] ?? 'fa-circle',
                'score' => $score,
                'max' => $max,
                'percentage' => round($percentage),
                'level' => $level,
                'items' => $categoryInfo[$cat]['recommendations'] ?? []
            ];
        }

        return $recommendations;
    }

    public function calculateRiskLevel($totalScore, $maxScore)
    {
        if ($maxScore == 0) return 'low';
        
        $percentage = ($totalScore / $maxScore) * 100;
        
        if ($percentage >= 70) return 'critical';
        if ($percentage >= 50) return 'high';
        if ($percentage >= 30) return 'medium';
        return 'low';
    }

    public function getOverallRecommendation($riskLevel, $percentage)
    {
        $texts = [
            'critical' => [
                'title' => '🚨 وضعیت بحرانی - نیاز به اقدام فوری',
                'message' => 'پروژه شما در معرض خطر جدی شکست است. توصیه اکید ما برگزاری یک جلسه مشاوره تخصصی برای عیب‌یابی و بازطراحی فرآیندها است.',
                'cta' => 'درخواست مشاوره فوری',
                'color' => '#dc3545'
            ],
            'high' => [
                'title' => '⚠️ ریسک بالا - نیاز به بهبود جدی',
                'message' => 'نشانه‌های نگران‌کننده‌ای در پروژه شما دیده می‌شود. با اقدام به‌موقع می‌توان از شکست پروژه جلوگیری کرد.',
                'cta' => 'برنامه‌ریزی برای بهبود',
                'color' => '#fd7e14'
            ],
            'medium' => [
                'title' => '⚡ ریسک متوسط - جای بهبود دارد',
                'message' => 'پروژه شما در مسیر درستی است اما نقاط ضعفی وجود دارد که با بهینه‌سازی می‌توانند برطرف شوند.',
                'cta' => 'مشاهده راهکارهای بهبود',
                'color' => '#ffc107'
            ],
            'low' => [
                'title' => '✅ وضعیت مطلوب - حفظ و تقویت',
                'message' => 'تبریک! پروژه شما از نظر تحلیل فرآیند در وضعیت خوبی قرار دارد. توصیه ما حفظ این روند و به‌روزرسانی مستمر است.',
                'cta' => 'دریافت راهنمای حفظ کیفیت',
                'color' => '#28a745'
            ]
        ];
        
        return $texts[$riskLevel] ?? $texts['medium'];
    }
}