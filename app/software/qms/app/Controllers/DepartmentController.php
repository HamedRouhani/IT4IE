<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class DepartmentController extends Controller
{
    public function index()
    {
        $p = $this->prefix;
        
        $departments = $this->db->query("
            SELECT d.*, u.name as manager_name, 
                   (SELECT COUNT(*) FROM {$p}audit_plan_items pi 
                    JOIN {$p}audit_plans ap ON pi.audit_plan_id = ap.id 
                    WHERE pi.department_id = d.id) as audit_count
            FROM {$p}departments d
            LEFT JOIN users u ON d.manager_name = u.name
            WHERE d.is_active = 1
            ORDER BY d.sort_order, d.name_fa
        ")->fetchAll();
        
        $this->view('departments/index', [
            'pageTitle' => 'واحدهای سازمانی',
            'currentPage' => 'departments',
            'departments' => $departments,
        ]);
    }
}