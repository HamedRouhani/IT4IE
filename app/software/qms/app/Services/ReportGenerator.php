<?php

namespace App\Software\Qms\Services;

/**
 * سرویس تولید گزارش نهایی ممیزی
 */
class ReportGenerator
{
    private $db;
    private $prefix = 'qms_';

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * تولید گزارش نهایی از یک برنامه ممیزی
     */
    public function generateFinalReport($auditPlanId, $userId)
    {
        // دریافت اطلاعات برنامه ممیزی
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$auditPlanId]);
        $plan = $stmt->fetch();

        if (!$plan) {
            return ['success' => false, 'message' => 'برنامه ممیزی یافت نشد.'];
        }

        // جمع‌آوری آمار
        $stats = $this->collectAuditStats($auditPlanId);

        // تولید شماره گزارش
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT report_number FROM {$this->prefix}audit_reports 
            WHERE report_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(["RPT-{$year}-%"]);
        $last = $stmt->fetch();
        $newNum = $last ? (int)substr($last['report_number'], -3) + 1 : 1;
        $reportNumber = sprintf('RPT-%s-%03d', $year, $newNum);

        // تولید خلاصه مدیریتی خودکار
        $executiveSummary = $this->generateExecutiveSummary($plan, $stats);

        // تولید نتیجه‌گیری کلی
        $overallConclusion = $this->generateOverallConclusion($stats);

        // تعیین سطح بلوغ
        $maturityLevel = $this->assessMaturityLevel($stats);

        // ایجاد گزارش
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_reports 
            (audit_plan_id, report_number, title, executive_summary, audit_scope, 
             audit_criteria, audit_team, audited_departments, audit_period_start, 
             audit_period_end, total_evidences, conformities_count, observations_count, 
             minor_nc_count, major_nc_count, opportunities_count, strengths, weaknesses, 
             recommendations, overall_conclusion, maturity_level, status, prepared_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW())
        ");

        $stmt->execute([
            $auditPlanId,
            $reportNumber,
            'گزارش نهایی ممیزی: ' . $plan['title'],
            $executiveSummary,
            $plan['scope'],
            $plan['criteria'],
            $plan['lead_auditor_id'],
            $plan['departments'],
            $plan['start_date'],
            $plan['end_date'],
            $stats['total_evidences'],
            $stats['conformities'],
            $stats['observations'],
            $stats['minor_nc'],
            $stats['major_nc'],
            $stats['opportunities'],
            $this->generateStrengths($stats),
            $this->generateWeaknesses($stats),
            $this->generateRecommendations($stats),
            $overallConclusion,
            $maturityLevel,
            $userId
        ]);

        $reportId = $this->db->lastInsertId();

        // به‌روزرسانی وضعیت برنامه ممیزی
        $this->db->prepare("
            UPDATE {$this->prefix}audit_plans 
            SET status = 'completed', updated_at = NOW()
            WHERE id = ?
        ")->execute([$auditPlanId]);

        return [
            'success' => true,
            'report_id' => $reportId,
            'report_number' => $reportNumber,
            'message' => 'گزارش نهایی با موفقیت ایجاد شد.'
        ];
    }

    /**
     * جمع‌آوری آمار ممیزی
     */
    private function collectAuditStats($auditPlanId)
    {
        $stats = [
            'total_evidences' => 0,
            'conformities' => 0,
            'observations' => 0,
            'minor_nc' => 0,
            'major_nc' => 0,
            'opportunities' => 0,
            'total_sessions' => 0,
            'completed_sessions' => 0
        ];

        // شمارش جلسات
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN overall_status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
        ");
        $stmt->execute([$auditPlanId]);
        $sessions = $stmt->fetch();
        $stats['total_sessions'] = $sessions['total'] ?? 0;
        $stats['completed_sessions'] = $sessions['completed'] ?? 0;

        // شمارش شواهد
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN finding_type = 'conformity' THEN 1 ELSE 0 END) as conformities,
                SUM(CASE WHEN finding_type = 'observation' THEN 1 ELSE 0 END) as observations,
                SUM(CASE WHEN finding_type = 'minor_nc' THEN 1 ELSE 0 END) as minor_nc,
                SUM(CASE WHEN finding_type = 'major_nc' THEN 1 ELSE 0 END) as major_nc,
                SUM(CASE WHEN finding_type = 'ofI' THEN 1 ELSE 0 END) as opportunities
            FROM {$this->prefix}audit_evidences e
            JOIN {$this->prefix}audit_sessions s ON e.session_id = s.id
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
        ");
        $stmt->execute([$auditPlanId]);
        $evidences = $stmt->fetch();

        $stats['total_evidences'] = $evidences['total'] ?? 0;
        $stats['conformities'] = $evidences['conformities'] ?? 0;
        $stats['observations'] = $evidences['observations'] ?? 0;
        $stats['minor_nc'] = $evidences['minor_nc'] ?? 0;
        $stats['major_nc'] = $evidences['major_nc'] ?? 0;
        $stats['opportunities'] = $evidences['opportunities'] ?? 0;

        return $stats;
    }

    /**
     * تولید خلاصه مدیریتی
     */
    private function generateExecutiveSummary($plan, $stats)
    {
        $summary = "ممیزی {$plan['title']} در بازه زمانی " . 
                   qms_date_fa($plan['start_date']) . " تا " . 
                   qms_date_fa($plan['end_date']) . " انجام شد. ";
        
        $summary .= "در این ممیزی {$stats['total_evidences']} شاهد بررسی شد که شامل ";
        $summary .= "{$stats['conformities']} مورد انطباق، ";
        $summary .= "{$stats['observations']} مشاهده، ";
        $summary .= "{$stats['minor_nc']} عدم انطباق جزئی و ";
        $summary .= "{$stats['major_nc']} عدم انطباق عمده بود. ";
        
        if ($stats['major_nc'] > 0) {
            $summary .= "وجود عدم انطباق‌های عمده نیاز به اقدام فوری دارد. ";
        }
        
        $summary .= "توصیه می‌شود گزارش کامل مطالعه و اقدامات لازم انجام شود.";
        
        return $summary;
    }

    /**
     * تولید نتیجه‌گیری کلی
     */
    private function generateOverallConclusion($stats)
    {
        if ($stats['major_nc'] > 0) {
            return "سیستم مدیریت کیفیت نیاز به بهبود جدی دارد. عدم انطباق‌های عمده شناسایی شده باید در اسرع وقت رفع شوند.";
        } elseif ($stats['minor_nc'] > 3) {
            return "سیستم مدیریت کیفیت در سطح قابل قبولی قرار دارد اما نیاز به بهبود در برخی حوزه‌ها دارد.";
        } elseif ($stats['minor_nc'] > 0) {
            return "سیستم مدیریت کیفیت به‌طور کلی مؤثر است با چند مورد عدم انطباق جزئی که باید رفع شوند.";
        } else {
            return "سیستم مدیریت کیفیت به‌طور مؤثر پیاده‌سازی و نگهداری شده است. هیچ عدم انطباقی شناسایی نشد.";
        }
    }

    /**
     * ارزیابی سطح بلوغ
     */
    private function assessMaturityLevel($stats)
    {
        if ($stats['major_nc'] > 0) return 'initial';
        if ($stats['minor_nc'] > 3) return 'managed';
        if ($stats['minor_nc'] > 0) return 'defined';
        if ($stats['observations'] > 5) return 'quantitatively_managed';
        return 'optimizing';
    }

    private function generateStrengths($stats) { return "نقاط قوت بر اساس شواهد مثبت شناسایی شده."; }
    private function generateWeaknesses($stats) { return "نقاط ضعف بر اساس عدم انطباق‌های شناسایی شده."; }
    private function generateRecommendations($stats) { return "توصیه‌ها بر اساس یافته‌های ممیزی."; }
}