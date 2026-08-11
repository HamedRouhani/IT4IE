<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class NonconformityController extends Controller
{
    /**
     * لیست عدم انطباق‌ها
     */
    public function index()
    {
        $this->requireAuth();
        
        $ncs = $this->db->query("
            SELECT nc.*, c.clause_number, c.title_fa as clause_title, 
                   d.name_fa as dept_name, u.name as reporter_name
            FROM {$this->prefix}nonconformities nc
            LEFT JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            LEFT JOIN {$this->prefix}departments d ON nc.affected_department_id = d.id
            LEFT JOIN users u ON nc.user_id = u.id
            ORDER BY nc.created_at DESC
        ")->fetchAll();

        $this->view('nonconformities/index', [
            'pageTitle' => 'عدم انطباق‌ها',
            'currentPage' => 'nonconformities',
            'ncs' => $ncs
        ]);
    }

    /**
     * فرم ثبت عدم انطباق جدید با پیشنهاد هوشمند
     */
    public function create()
    {
        $this->requireAuth();
        
        $departments = $this->db->query("SELECT id, name_fa FROM {$this->prefix}departments WHERE is_active = 1 ORDER BY name_fa")->fetchAll();
        
        // دریافت تمام بندهای استاندارد برای پیشنهاد
        $clauses = $this->db->query("
            SELECT id, clause_number, title_fa, description, requirements, level
            FROM {$this->prefix}iso_clauses 
            WHERE is_active = 1 AND clause_type = 'requirement'
            ORDER BY sort_order
        ")->fetchAll();

        $this->view('nonconformities/create', [
            'pageTitle' => 'ثبت عدم انطباق جدید',
            'currentPage' => 'nonconformities',
            'departments' => $departments,
            'clauses' => $clauses
        ]);
    }

    /**
     * 🔥 API پیشنهاد هوشمند بندهای مرتبط (AJAX)
     * بر اساس توضیحات عدم انطباق، بندهای مرتبط را با درصد تطابق پیشنهاد می‌دهد
     */
    public function suggestClauses()
    {
        $this->requireAuth();
        
        $description = trim($_POST['description'] ?? '');
        $title = trim($_POST['title'] ?? '');
        
        if (empty($description) && empty($title)) {
            $this->json(['success' => false, 'suggestions' => []]);
            return;
        }

        // دریافت تمام بندهای استاندارد
        $clauses = $this->db->query("
            SELECT id, clause_number, title_fa, description, requirements, guidance
            FROM {$this->prefix}iso_clauses 
            WHERE is_active = 1 AND clause_type = 'requirement'
        ")->fetchAll();

        $suggestions = [];
        $searchText = mb_strtolower($title . ' ' . $description, 'UTF-8');
        
        // کلمات کلیدی مرتبط با هر بند (بر اساس تحلیل ISO 9001)
        $keywords = [
            '4.1' => ['بافت', 'محیط', 'خارجی', 'داخلی', 'استراتژیک', 'context'],
            '4.2' => ['ذینفع', 'نیاز', 'انتظار', 'stakeholder'],
            '4.3' => ['دامنه', 'scope', 'مرز', 'کاربرد'],
            '4.4' => ['فرآیند', 'process', 'تعامل', 'ورودی', 'خروجی'],
            '5.1' => ['رهبری', 'تعهد', 'مدیریت ارشد', 'leadership'],
            '5.1.2' => ['مشتری', 'رضایت', 'customer', 'تمرکز'],
            '5.2' => ['خط‌مشی', 'policy', 'کیفیت'],
            '5.3' => ['نقش', 'مسئولیت', 'اختیار', 'role', 'responsibility'],
            '6.1' => ['ریسک', 'فرصت', 'risk', 'opportunity'],
            '6.2' => ['هدف', 'objective', 'اندازه‌گیری'],
            '6.3' => ['تغییر', 'change', 'برنامه‌ریزی'],
            '7.1' => ['منبع', 'resource', 'زیرساخت', 'محیط'],
            '7.1.5' => ['کالیبراسیون', 'اندازه‌گیری', 'تجهیز'],
            '7.2' => ['صلاحیت', 'competence', 'آموزش'],
            '7.3' => ['آگاهی', 'awareness', 'آگاه'],
            '7.4' => ['ارتباط', 'communication', 'ارتباطات'],
            '7.5' => ['مستند', 'سند', 'document', 'اطلاعات مستند'],
            '8.1' => ['عملیات', 'کنترل', 'operation'],
            '8.2' => ['الزامات', 'مشتری', 'سفارش', 'contract'],
            '8.3' => ['طراحی', 'توسعه', 'design', 'development'],
            '8.4' => ['تأمین', 'برون‌سپاری', 'supplier', 'external'],
            '8.5' => ['تولید', 'ارائه خدمات', 'production', 'service'],
            '8.5.2' => ['شناسایی', 'ردیابی', 'identification', 'traceability'],
            '8.6' => ['آزادسازی', 'release', 'انتشار'],
            '8.7' => ['نامنطبق', 'خروجی نامنطبق', 'nonconforming'],
            '9.1' => ['پایش', 'اندازه‌گیری', 'monitoring', 'measurement'],
            '9.1.2' => ['رضایت مشتری', 'customer satisfaction'],
            '9.2' => ['ممیزی', 'audit', 'داخلی'],
            '9.3' => ['بازنگری', 'management review', 'مدیریت'],
            '10.1' => ['بهبود', 'improvement'],
            '10.2' => ['عدم انطباق', 'اقدام اصلاحی', 'corrective', 'ریشه'],
            '10.3' => ['بهبود مستمر', 'continual improvement']
        ];

        foreach ($clauses as $clause) {
            $score = 0;
            $matchedKeywords = [];
            
            // ۱. بررسی تطابق با کلمات کلیدی از پیش تعریف شده
            if (isset($keywords[$clause['clause_number']])) {
                foreach ($keywords[$clause['clause_number']] as $keyword) {
                    if (mb_strpos($searchText, mb_strtolower($keyword)) !== false) {
                        $score += 15;
                        $matchedKeywords[] = $keyword;
                    }
                }
            }
            
            // ۲. بررسی تطابق با عنوان بند
            $titleLower = mb_strtolower($clause['title_fa'], 'UTF-8');
            $titleWords = preg_split('/\s+/', $titleLower);
            foreach ($titleWords as $word) {
                if (mb_strlen($word) > 3 && mb_strpos($searchText, $word) !== false) {
                    $score += 10;
                }
            }
            
            // ۳. بررسی تطابق با توضیحات بند
            $descLower = mb_strtolower($clause['description'] ?? '', 'UTF-8');
            $descWords = preg_split('/\s+/', $descLower);
            foreach ($descWords as $word) {
                if (mb_strlen($word) > 4 && mb_strpos($searchText, $word) !== false) {
                    $score += 3;
                }
            }
            
            // ۴. بررسی تطابق با الزامات بند
            $reqLower = mb_strtolower($clause['requirements'] ?? '', 'UTF-8');
            $reqWords = preg_split('/\s+/', $reqLower);
            foreach ($reqWords as $word) {
                if (mb_strlen($word) > 4 && mb_strpos($searchText, $word) !== false) {
                    $score += 2;
                }
            }
            
            // نرمال‌سازی امتیاز به درصد (حداکثر ۱۰۰)
            $percentage = min(100, $score);
            
            if ($percentage > 10) { // فقط پیشنهادات با تطابق بیش از ۱۰٪
                $suggestions[] = [
                    'id' => $clause['id'],
                    'clause_number' => $clause['clause_number'],
                    'title_fa' => $clause['title_fa'],
                    'description' => mb_substr($clause['description'] ?? '', 0, 200) . '...',
                    'percentage' => $percentage,
                    'matched_keywords' => $matchedKeywords
                ];
            }
        }

        // مرتب‌سازی بر اساس درصد تطابق (نزولی)
        usort($suggestions, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        // فقط ۱۰ پیشنهاد برتر
        $suggestions = array_slice($suggestions, 0, 10);

        $this->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * ذخیره عدم انطباق جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('nonconformities');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $clauseId = $_POST['clause_id'] ?? null;
        $severity = $_POST['severity'] ?? 'minor';

        if (empty($title) || empty($description) || empty($clauseId)) {
            $this->flashError('عنوان، توضیحات و انتخاب بند اصلی الزامی است.');
            $this->redirect('nonconformities&action=create');
            return;
        }

        // تولید شماره NC
        $ncNumber = $this->generateNcNumber();

        // دریافت اطلاعات بند اصلی
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}iso_clauses WHERE id = ?");
        $stmt->execute([$clauseId]);
        $mainClause = $stmt->fetch();

        $this->db->beginTransaction();

        try {
            // ثبت NC اصلی
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}nonconformities 
                (user_id, nc_number, title, description, clause_id, requirement_text, 
                 current_situation, severity, status, detected_date, detection_source, 
                 affected_department_id, affected_process, risk_level, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', CURDATE(), ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $this->currentUserId,
                $ncNumber,
                $title,
                $description,
                $clauseId,
                $mainClause['requirements'] ?? '',
                $_POST['current_situation'] ?? '',
                $severity,
                $_POST['detection_source'] ?? 'internal_audit',
                $_POST['affected_department_id'] ?? null,
                $_POST['affected_process'] ?? null,
                $_POST['risk_level'] ?? 'medium'
            ]);

            $ncId = $this->db->lastInsertId();

            // ثبت بندهای مرتبط با درصد تطابق
            $selectedClauses = $_POST['related_clauses'] ?? [];
            
            // ابتدا بند اصلی را با ۱۰۰٪ تطابق اضافه کن
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}nc_clause_mapping 
                (nc_id, clause_id, match_percentage, is_primary, relevance_note, created_at)
                VALUES (?, ?, 100.00, 1, 'بند اصلی', NOW())
            ");
            $stmt->execute([$ncId, $clauseId]);

            // سپس بندهای مرتبط دیگر
            if (!empty($selectedClauses) && is_array($selectedClauses)) {
                foreach ($selectedClauses as $mapping) {
                    $relatedClauseId = $mapping['clause_id'] ?? null;
                    $percentage = $mapping['percentage'] ?? 0;
                    
                    if ($relatedClauseId && $relatedClauseId != $clauseId) {
                        $stmt = $this->db->prepare("
                            INSERT INTO {$this->prefix}nc_clause_mapping 
                            (nc_id, clause_id, match_percentage, is_primary, relevance_note, created_at)
                            VALUES (?, ?, ?, 0, ?, NOW())
                        ");
                        $stmt->execute([
                            $ncId,
                            $relatedClauseId,
                            $percentage,
                            $mapping['note'] ?? ''
                        ]);
                    }
                }
            }

            $this->db->commit();
            $this->logActivity('create_nonconformity', 'nonconformity', $ncId);
            $this->flashSuccess("عدم انطباق {$ncNumber} با موفقیت ثبت شد.");
            $this->redirect('nonconformities&action=show&id=' . $ncId);

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->flashError('خطا در ثبت عدم انطباق: ' . $e->getMessage());
            $this->redirect('nonconformities&action=create');
        }
    }

    /**
     * مشاهده جزئیات عدم انطباق
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT nc.*, c.clause_number, c.title_fa as clause_title, 
                   d.name_fa as dept_name, u.name as reporter_name,
                   cf.car_number, cf.id as car_form_id
            FROM {$this->prefix}nonconformities nc
            LEFT JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            LEFT JOIN {$this->prefix}departments d ON nc.affected_department_id = d.id
            LEFT JOIN users u ON nc.user_id = u.id
            LEFT JOIN {$this->prefix}car_forms cf ON nc.car_form_id = cf.id
            WHERE nc.id = ?
        ");
        $stmt->execute([$id]);
        $nc = $stmt->fetch();

        if (!$nc) {
            $this->flashError('عدم انطباق یافت نشد.');
            $this->redirect('nonconformities');
            return;
        }

        // دریافت بندهای مرتبط با درصد تطابق
        $relatedClauses = $this->db->prepare("
            SELECT m.*, c.clause_number, c.title_fa, c.description
            FROM {$this->prefix}nc_clause_mapping m
            JOIN {$this->prefix}iso_clauses c ON m.clause_id = c.id
            WHERE m.nc_id = ?
            ORDER BY m.match_percentage DESC
        ");
        $relatedClauses->execute([$id]);
        $relatedClauses = $relatedClauses->fetchAll();

        $this->view('nonconformities/show', [
            'pageTitle' => 'عدم انطباق ' . $nc['nc_number'],
            'currentPage' => 'nonconformities',
            'nc' => $nc,
            'relatedClauses' => $relatedClauses
        ]);
    }

    /**
     * صدور CAR برای عدم انطباق
     */
    public function generateCar($id)
    {
        $this->requireAuth();
        
        $generator = new \App\Software\Qms\Services\CarGenerator();
        $result = $generator->generateFromNc($id, $this->currentUserId);

        if ($result['success']) {
            $this->logActivity('generate_car', 'car_form', $result['car_id']);
            $this->flashSuccess($result['message']);
            $this->redirect('car&action=show&id=' . $result['car_id']);
        } else {
            $this->flashError($result['message']);
            $this->redirect('nonconformities&action=show&id=' . $id);
        }
    }

    /**
     * بستن عدم انطباق توسط ممیز
     */
    public function close()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('nonconformities');
            return;
        }

        $ncId = $_POST['nc_id'] ?? null;
        $notes = trim($_POST['closure_notes'] ?? '');

        if (!$ncId) {
            $this->flashError('شناسه نامعتبر است.');
            $this->redirect('nonconformities');
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}nonconformities 
            SET status = 'closed', closed_by = ?, closed_at = NOW(), closure_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$this->currentUserId, $notes, $ncId]);

        if ($result) {
            $this->logActivity('close_nonconformity', 'nonconformity', $ncId);
            $this->flashSuccess('عدم انطباق با موفقیت بسته شد.');
        } else {
            $this->flashError('خطا در بستن عدم انطباق.');
        }

        $this->redirect('nonconformities&action=show&id=' . $ncId);
    }

    /**
     * رد کردن عدم انطباق (درخواست اقدام مجدد)
     */
    public function reject()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('nonconformities');
            return;
        }

        $ncId = $_POST['nc_id'] ?? null;
        $reason = trim($_POST['rejection_reason'] ?? '');

        if (!$ncId || empty($reason)) {
            $this->flashError('دلیل رد الزامی است.');
            $this->redirect('nonconformities');
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}nonconformities 
            SET status = 'rejected', rejection_reason = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$reason, $ncId]);

        if ($result) {
            $this->logActivity('reject_nonconformity', 'nonconformity', $ncId);
            $this->flashSuccess('عدم انطباق رد شد و برای اقدام مجدد ارسال گردید.');
        } else {
            $this->flashError('خطا در رد عدم انطباق.');
        }

        $this->redirect('nonconformities&action=show&id=' . $ncId);
    }
}