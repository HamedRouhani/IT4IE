<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $p = $this->prefix;
        
        // آمار کلی
        $stats = [
            'total_clauses' => $this->db->query("SELECT COUNT(*) FROM {$p}iso_clauses WHERE is_active = 1")->fetchColumn(),
            'total_departments' => $this->db->query("SELECT COUNT(*) FROM {$p}departments WHERE is_active = 1")->fetchColumn(),
            'total_auditors' => $this->db->query("SELECT COUNT(*) FROM {$p}auditors WHERE is_active = 1")->fetchColumn(),
            'scheduled_audits' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_plans WHERE status = 'scheduled'")->fetchColumn(),
            'ongoing_audits' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_plans WHERE status = 'in_progress'")->fetchColumn(),
            'completed_audits' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_plans WHERE status = 'completed'")->fetchColumn(),
            'open_ncs' => $this->db->query("SELECT COUNT(*) FROM {$p}nonconformities WHERE status NOT IN ('closed', 'rejected')")->fetchColumn(),
            'open_cars' => $this->db->query("SELECT COUNT(*) FROM {$p}car_forms WHERE status NOT IN ('closed', 'verified')")->fetchColumn(),
            'completed_sessions' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_sessions WHERE overall_status = 'completed'")->fetchColumn(),
            'total_evidences' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_evidences")->fetchColumn(),
            'total_reports' => $this->db->query("SELECT COUNT(*) FROM {$p}audit_reports")->fetchColumn(),
        ];
        
        // آخرین عدم انطباق‌ها
        $recentNcs = $this->db->query("
            SELECT nc.*, c.title_fa as clause_title, d.name_fa as dept_name
            FROM {$p}nonconformities nc
            LEFT JOIN {$p}iso_clauses c ON nc.clause_id = c.id
            LEFT JOIN {$p}departments d ON nc.affected_department_id = d.id
            ORDER BY nc.created_at DESC LIMIT 5
        ")->fetchAll();
        
        // برنامه‌های ممیزی پیش رو
        $upcomingAudits = $this->db->query("
            SELECT ap.*, a.full_name as lead_auditor_name
            FROM {$p}audit_plans ap
            LEFT JOIN {$p}auditors a ON ap.lead_auditor_id = a.id
            WHERE ap.status = 'scheduled' AND ap.start_date >= CURDATE()
            ORDER BY ap.start_date ASC LIMIT 5
        ")->fetchAll();
        
        // CARهای باز
        $openCars = $this->db->query("
            SELECT cf.*, nc.nc_number, nc.title as nc_title
            FROM {$p}car_forms cf
            JOIN {$p}nonconformities nc ON cf.nc_id = nc.id
            WHERE cf.status NOT IN ('closed', 'verified')
            ORDER BY cf.created_at DESC LIMIT 5
        ")->fetchAll();
        
        $this->view('dashboard/index', [
            'pageTitle' => 'داشبورد QMS',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'recentNcs' => $recentNcs,
            'upcomingAudits' => $upcomingAudits,
            'openCars' => $openCars,
        ]);
    }
}