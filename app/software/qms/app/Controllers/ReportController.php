<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class ReportController extends Controller
{
    /**
     * داشبورد گزارش‌ها (لیست تمام گزارش‌ها)
     */
    public function index()
    {
        $this->requireAuth();
        
        $reports = $this->db->query("
            SELECT r.*, ap.title as plan_title, 
                   u.name as prepared_by_name,
                   (SELECT COUNT(*) FROM {$this->prefix}nonconformities nc 
                    JOIN {$this->prefix}audit_sessions s ON nc.session_id = s.id 
                    JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id 
                    WHERE pi.audit_plan_id = r.audit_plan_id) as total_ncs
            FROM {$this->prefix}audit_reports r
            JOIN {$this->prefix}audit_plans ap ON r.audit_plan_id = ap.id
            LEFT JOIN users u ON r.prepared_by = u.id
            ORDER BY r.created_at DESC
        ")->fetchAll();

        // آمار کلی برای داشبورد
        $stats = $this->getOverallStats();

        $this->view('reports/index', [
            'pageTitle' => 'گزارش‌های ممیزی',
            'currentPage' => 'reports',
            'reports' => $reports,
            'stats' => $stats
        ]);
    }

    /**
     * مشاهده جزئیات گزارش نهایی
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT r.*, ap.title as plan_title, ap.audit_type, ap.scope, ap.criteria,
                   ap.start_date, ap.end_date,
                   u.name as prepared_by_name,
                   la.full_name as lead_auditor_name
            FROM {$this->prefix}audit_reports r
            JOIN {$this->prefix}audit_plans ap ON r.audit_plan_id = ap.id
            LEFT JOIN users u ON r.prepared_by = u.id
            LEFT JOIN {$this->prefix}auditors la ON ap.lead_auditor_id = la.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $report = $stmt->fetch();

        if (!$report) {
            $this->flashError('گزارش یافت نشد.');
            $this->redirect('reports');
            return;
        }

        // دریافت عدم انطباق‌های مرتبط
        $ncs = $this->db->prepare("
            SELECT nc.*, c.clause_number, c.title_fa as clause_title,
                   d.name_fa as dept_name,
                   cf.car_number
            FROM {$this->prefix}nonconformities nc
            JOIN {$this->prefix}audit_sessions s ON nc.session_id = s.id
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            LEFT JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            LEFT JOIN {$this->prefix}departments d ON nc.affected_department_id = d.id
            LEFT JOIN {$this->prefix}car_forms cf ON nc.car_form_id = cf.id
            WHERE pi.audit_plan_id = ?
            ORDER BY nc.severity DESC, nc.created_at DESC
        ");
        $ncs->execute([$report['audit_plan_id']]);
        $ncs = $ncs->fetchAll();

        // دریافت شواهد
        $evidences = $this->db->prepare("
            SELECT e.*, c.clause_number, c.title_fa as clause_title,
                   s.actual_date, d.name_fa as dept_name
            FROM {$this->prefix}audit_evidences e
            JOIN {$this->prefix}audit_sessions s ON e.session_id = s.id
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            LEFT JOIN {$this->prefix}iso_clauses c ON e.clause_id = c.id
            LEFT JOIN {$this->prefix}departments d ON pi.department_id = d.id
            WHERE pi.audit_plan_id = ?
            ORDER BY e.finding_type, e.created_at DESC
        ");
        $evidences->execute([$report['audit_plan_id']]);
        $evidences = $evidences->fetchAll();

        // دریافت CARهای مرتبط
        $cars = $this->db->prepare("
            SELECT cf.*, nc.nc_number, nc.title as nc_title
            FROM {$this->prefix}car_forms cf
            JOIN {$this->prefix}nonconformities nc ON cf.nc_id = nc.id
            JOIN {$this->prefix}audit_sessions s ON nc.session_id = s.id
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
            ORDER BY cf.created_at DESC
        ");
        $cars->execute([$report['audit_plan_id']]);
        $cars = $cars->fetchAll();

        // آمار تفصیلی
        $detailedStats = $this->getDetailedStats($report['audit_plan_id']);

        $this->view('reports/show', [
            'pageTitle' => 'گزارش: ' . $report['report_number'],
            'currentPage' => 'reports',
            'report' => $report,
            'ncs' => $ncs,
            'evidences' => $evidences,
            'cars' => $cars,
            'detailedStats' => $detailedStats
        ]);
    }

    /**
     * تولید گزارش نهایی جدید از یک برنامه ممیزی
     */
    public function generate()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reports');
            return;
        }

        $planId = $_POST['audit_plan_id'] ?? null;
        
        if (!$planId) {
            $this->flashError('شناسه برنامه ممیزی نامعتبر است.');
            $this->redirect('reports');
            return;
        }

        $generator = new \App\Software\Qms\Services\ReportGenerator();
        $result = $generator->generateFinalReport($planId, $this->currentUserId);

        if ($result['success']) {
            $this->logActivity('generate_report', 'audit_report', $result['report_id']);
            $this->flashSuccess('گزارش نهایی با موفقیت ایجاد شد.');
            $this->redirect('reports&action=show&id=' . $result['report_id']);
        } else {
            $this->flashError($result['message']);
            $this->redirect('reports');
        }
    }

    /**
     * به‌روزرسانی وضعیت گزارش
     */
    public function updateStatus()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reports');
            return;
        }

        $reportId = $_POST['report_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$reportId || !$newStatus) {
            $this->flashError('اطلاعات نامعتبر است.');
            $this->redirect('reports');
            return;
        }

        $validStatuses = ['draft', 'review', 'finalized', 'distributed', 'archived'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->flashError('وضعیت نامعتبر است.');
            $this->redirect('reports');
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}audit_reports 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$newStatus, $reportId]);

        if ($result) {
            $this->logActivity('update_report_status', 'audit_report', $reportId);
            $this->flashSuccess('وضعیت گزارش به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی وضعیت.');
        }

        $this->redirect('reports&action=show&id=' . $reportId);
    }

    /**
     * داشبورد مدیریتی (نمای کلی QMS)
     */
    public function dashboard()
    {
        $this->requireAuth();
        
        $stats = $this->getOverallStats();
        
        // آخرین فعالیت‌ها
        $recentActivities = $this->db->query("
            SELECT * FROM software_activity_logs 
            WHERE software_slug = 'qms'
            ORDER BY created_at DESC LIMIT 10
        ")->fetchAll();

        // عدم انطباق‌های باز به تفکیک شدت
        $ncsBySeverity = $this->db->query("
            SELECT severity, COUNT(*) as count 
            FROM {$this->prefix}nonconformities 
            WHERE status NOT IN ('closed', 'rejected')
            GROUP BY severity
        ")->fetchAll();

        // روند عدم انطباق‌ها در  ماه اخیر
        $ncsTrend = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM {$this->prefix}nonconformities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ")->fetchAll();

        // وضعیت CARها
        $carsByStatus = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM {$this->prefix}car_forms 
            GROUP BY status
        ")->fetchAll();

        // بندهای پرتکرار در عدم انطباق‌ها
        $topClauses = $this->db->query("
            SELECT c.clause_number, c.title_fa, COUNT(*) as nc_count
            FROM {$this->prefix}nonconformities nc
            JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            GROUP BY nc.clause_id
            ORDER BY nc_count DESC
            LIMIT 10
        ")->fetchAll();

        $this->view('reports/dashboard', [
            'pageTitle' => 'داشبورد مدیریت QMS',
            'currentPage' => 'reports',
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'ncsBySeverity' => $ncsBySeverity,
            'ncsTrend' => $ncsTrend,
            'carsByStatus' => $carsByStatus,
            'topClauses' => $topClauses
        ]);
    }

    /**
     * گزارش تحلیلی بندهای استاندارد
     */
    public function clausesAnalysis()
    {
        $this->requireAuth();
        
        $clauses = $this->db->query("
            SELECT c.*, 
                   COUNT(DISTINCT nc.id) as nc_count,
                   COUNT(DISTINCT e.id) as evidence_count,
                   SUM(CASE WHEN nc.status = 'closed' THEN 1 ELSE 0 END) as closed_ncs
            FROM {$this->prefix}iso_clauses c
            LEFT JOIN {$this->prefix}nonconformities nc ON c.id = nc.clause_id
            LEFT JOIN {$this->prefix}audit_evidences e ON c.id = e.clause_id
            WHERE c.is_active = 1 AND c.clause_type = 'requirement'
            GROUP BY c.id
            ORDER BY nc_count DESC
        ")->fetchAll();

        $this->view('reports/clauses-analysis', [
            'pageTitle' => 'تحلیل بندهای استاندارد',
            'currentPage' => 'reports',
            'clauses' => $clauses
        ]);
    }

    /**
     * گزارش عملکرد واحدها
     */
    public function departmentsPerformance()
    {
        $this->requireAuth();
        
        $departments = $this->db->query("
            SELECT d.*,
                   COUNT(DISTINCT nc.id) as nc_count,
                   SUM(CASE WHEN nc.severity = 'major' THEN 1 ELSE 0 END) as major_ncs,
                   SUM(CASE WHEN nc.severity = 'critical' THEN 1 ELSE 0 END) as critical_ncs,
                   SUM(CASE WHEN nc.status = 'closed' THEN 1 ELSE 0 END) as closed_ncs,
                   COUNT(DISTINCT cf.id) as car_count
            FROM {$this->prefix}departments d
            LEFT JOIN {$this->prefix}nonconformities nc ON d.id = nc.affected_department_id
            LEFT JOIN {$this->prefix}car_forms cf ON nc.car_form_id = cf.id
            WHERE d.is_active = 1
            GROUP BY d.id
            ORDER BY nc_count DESC
        ")->fetchAll();

        $this->view('reports/departments-performance', [
            'pageTitle' => 'عملکرد واحدها',
            'currentPage' => 'reports',
            'departments' => $departments
        ]);
    }

    // ============================================
    // متدهای کمکی
    // ============================================

    private function getOverallStats()
    {
        $p = $this->prefix;
        
        return [
            'total_plans' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_plans")->fetchColumn(),
            'completed_plans' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_plans WHERE status = 'completed'")->fetchColumn(),
            'total_sessions' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_sessions")->fetchColumn(),
            'completed_sessions' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_sessions WHERE overall_status = 'completed'")->fetchColumn(),
            'total_evidences' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_evidences")->fetchColumn(),
            'total_ncs' => $this->db->query("SELECT COUNT(*) FROM {$p}nonconformities")->fetchColumn(),
            'open_ncs' => $this->db->query("SELECT COUNT(*) FROM {$p}nonconformities WHERE status NOT IN ('closed', 'rejected')")->fetchColumn(),
            'closed_ncs' => $this->db->query("SELECT COUNT(*) FROM {$p}nonconformities WHERE status = 'closed'")->fetchColumn(),
            'total_cars' => $this->db->query("SELECT COUNT(*) FROM {$p}car_forms")->fetchColumn(),
            'open_cars' => $this->db->query("SELECT COUNT(*) FROM {$p}car_forms WHERE status NOT IN ('closed', 'verified')")->fetchColumn(),
            'total_reports' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_reports")->fetchColumn(),
            'total_departments' => $this->db->query("SELECT COUNT(*) FROM {$p}departments WHERE is_active = 1")->fetchColumn(),
            'total_auditors' => $this->db->query("SELECT COUNT(*) FROM {$p}auditors WHERE is_active = 1")->fetchColumn(),
        ];
    }

    private function getDetailedStats($planId)
    {
        $p = $this->prefix;
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT e.id) as total_evidences,
                SUM(CASE WHEN e.finding_type = 'conformity' THEN 1 ELSE 0 END) as conformities,
                SUM(CASE WHEN e.finding_type = 'observation' THEN 1 ELSE 0 END) as observations,
                SUM(CASE WHEN e.finding_type = 'minor_nc' THEN 1 ELSE 0 END) as minor_ncs,
                SUM(CASE WHEN e.finding_type = 'major_nc' THEN 1 ELSE 0 END) as major_ncs,
                SUM(CASE WHEN e.finding_type = 'ofI' THEN 1 ELSE 0 END) as opportunities
            FROM {$p}audit_evidences e
            JOIN {$p}audit_sessions s ON e.session_id = s.id
            JOIN {$p}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
        ");
        $stmt->execute([$planId]);
        return $stmt->fetch();
    }
}