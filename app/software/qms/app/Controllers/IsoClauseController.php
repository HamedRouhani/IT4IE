<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class IsoClauseController extends Controller
{
    /**
     * نمایش سلسله‌مراتبی تمام بندهای استاندارد در یک صفحه
     */
    public function index()
    {
        $p = $this->prefix;
        
        // ۱. دریافت بندهای اصلی (سطح ۱)
        $stmt = $this->db->query("
            SELECT id, clause_number, title_fa, description, level, clause_type, sort_order 
            FROM {$p}iso_clauses 
            WHERE level = 1 AND is_active = 1 
            ORDER BY sort_order ASC
        ");
        $mainClauses = $stmt->fetchAll();
        
        // ۲. دریافت زیربندها (سطح ۲) و زیرزیربندها (سطح ۳) به صورت تو در تو
        foreach ($mainClauses as &$main) {
            $stmtSub = $this->db->prepare("
                SELECT id, clause_number, title_fa, description, level, clause_type, sort_order 
                FROM {$p}iso_clauses 
                WHERE parent_id = ? AND is_active = 1 
                ORDER BY sort_order ASC
            ");
            $stmtSub->execute([$main['id']]);
            $main['children'] = $stmtSub->fetchAll();
            
            // ۳. دریافت زیرزیربندها (سطح ۳) برای هر زیربند
            foreach ($main['children'] as &$sub) {
                $stmtSubSub = $this->db->prepare("
                    SELECT id, clause_number, title_fa, description, level, clause_type, sort_order 
                    FROM {$p}iso_clauses 
                    WHERE parent_id = ? AND is_active = 1 
                    ORDER BY sort_order ASC
                ");
                $stmtSubSub->execute([$sub['id']]);
                $sub['children'] = $stmtSubSub->fetchAll();
            }
        }
        unset($main, $sub); // آزاد کردن ارجاع‌ها
        
        // آمار کلی
        $stats = [
            'total' => $this->db->query("SELECT COUNT(*) FROM {$p}iso_clauses WHERE is_active = 1")->fetchColumn(),
            'main' => count($mainClauses),
        ];
        
        $this->view('iso-clauses/index', [
            'pageTitle' => 'بندهای استاندارد ISO 9001:2015',
            'currentPage' => 'isoclauses',
            'mainClauses' => $mainClauses,
            'stats' => $stats,
        ]);
    }
}