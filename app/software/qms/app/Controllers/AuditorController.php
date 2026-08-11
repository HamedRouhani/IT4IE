<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class AuditorController extends Controller
{
    public function index()
    {
        $p = $this->prefix;
        
        $auditors = $this->db->query("
            SELECT a.*, u.name as user_name,
                   (SELECT COUNT(*) FROM {$p}audit_plans WHERE lead_auditor_id = a.id) as lead_count,
                   (SELECT COUNT(*) FROM {$p}audit_plan_items WHERE assigned_auditor_id = a.id) as item_count
            FROM {$p}auditors a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.is_active = 1
            ORDER BY a.lead_auditor DESC, a.full_name
        ")->fetchAll();
        
        $this->view('auditors/index', [
            'pageTitle' => 'ممیزان',
            'currentPage' => 'auditors',
            'auditors' => $auditors,
        ]);
    }
}