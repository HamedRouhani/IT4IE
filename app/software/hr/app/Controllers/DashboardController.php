<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Hr\Models\System;

/**
 * ============================================================
 * DashboardController - داشبورد ماژول HR
 * ============================================================
 */
class DashboardController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $activeSystem = $this->getOrCreateSystem($userId);

        $stats                  = $this->getDashboardStats($activeSystem['id']);
        $kpiCards               = $this->getKPICards($activeSystem['id']);
        $departmentDistribution = $this->getDepartmentDistribution($activeSystem['id']);
        $employmentDistribution = $this->getEmploymentDistribution($activeSystem['id']);
        $recentEmployees        = $this->getRecentEmployees($activeSystem['id'], 5);
        $alerts                 = $this->getAlerts($activeSystem['id']);

        $this->renderSoftware('dashboard/index', [
            'pageTitle'              => 'داشبورد منابع انسانی',
            'softwareName'           => 'HR Analyzer',
            'activeSystem'           => $activeSystem,
            'stats'                  => $stats,
            'kpiCards'               => $kpiCards,
            'departmentDistribution' => $departmentDistribution,
            'employmentDistribution' => $employmentDistribution,
            'recentEmployees'        => $recentEmployees,
            'alerts'                 => $alerts,
            'flash'                  => hr_flash_get(),
        ], 'hr');
    }

    private function getOrCreateSystem(int $userId): array
    {
        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if ($system) {
            $_SESSION['hr_active_system'] = (int) $system['id'];
            return $system;
        }

        $newId = $systemModel->createForUser($userId, [
            'company_name' => 'شرکت من',
            'industry'     => 'general',
            'company_size' => 'medium',
            'description'  => 'سیستم منابع انسانی',
            'status'       => 'active',
        ]);

        $_SESSION['hr_active_system'] = $newId;
        return $systemModel->findById($newId);
    }

    private function getDashboardStats(int $systemId): array
    {
        $stats = [
            'total_employees'   => 0,
            'active_employees'  => 0,
            'on_leave'          => 0,
            'terminated'        => 0,
            'total_departments' => 0,
            'total_positions'   => 0,
            'open_positions'    => 0,
            'total_candidates'  => 0,
            'active_trainings'  => 0,
            'pending_reviews'   => 0,
        ];

        // کارکنان
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN employment_status = 'on_leave' THEN 1 ELSE 0 END) AS on_leave_count,
                SUM(CASE WHEN employment_status = 'terminated' THEN 1 ELSE 0 END) AS terminated_count
             FROM hr_employees WHERE system_id = ?"
        );
        $stmt->execute([$systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $stats['total_employees']  = (int) $row['total'];
            $stats['active_employees'] = (int) $row['active_count'];
            $stats['on_leave']         = (int) $row['on_leave_count'];
            $stats['terminated']       = (int) $row['terminated_count'];
        }

        // دپارتمان‌ها
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_departments WHERE system_id = ? AND status = 'active'"
        );
        $stmt->execute([$systemId]);
        $stats['total_departments'] = (int) $stmt->fetchColumn();

        // پست‌ها
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_positions WHERE system_id = ? AND is_active = 1"
        );
        $stmt->execute([$systemId]);
        $stats['total_positions'] = (int) $stmt->fetchColumn();

        // پست‌های خالی
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_positions 
             WHERE system_id = ? AND is_active = 1 
               AND (headcount - filled_count) > 0"
        );
        $stmt->execute([$systemId]);
        $stats['open_positions'] = (int) $stmt->fetchColumn();

        // متقاضیان فعال
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_candidates 
             WHERE system_id = ? AND status IN ('new', 'screening', 'interview', 'technical_test', 'offer')"
        );
        $stmt->execute([$systemId]);
        $stats['total_candidates'] = (int) $stmt->fetchColumn();

        // دوره‌های آموزشی فعال
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_trainings 
             WHERE system_id = ? AND status IN ('planned', 'open', 'in_progress')"
        );
        $stmt->execute([$systemId]);
        $stats['active_trainings'] = (int) $stmt->fetchColumn();

        // ✅ اصلاح: ارزیابی‌های در انتظار (draft + in_progress + pending_*)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_performance_reviews 
             WHERE system_id = ? 
               AND status IN ('draft', 'in_progress', 'pending_employee', 'pending_manager')"
        );
        $stmt->execute([$systemId]);
        $stats['pending_reviews'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    private function getKPICards(int $systemId): array
    {
        $cards = [];

        // ۱. نرخ ترک خدمت
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'terminated' 
               AND YEAR(termination_date) = YEAR(CURDATE())"
        );
        $stmt->execute([$systemId]);
        $terminated = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_employees WHERE system_id = ?"
        );
        $stmt->execute([$systemId]);
        $total = (int) $stmt->fetchColumn();

        $turnoverRate = $total > 0 ? round(($terminated / $total) * 100, 1) : 0;

        $cards['turnover'] = [
            'label' => 'نرخ ترک خدمت',
            'value' => $turnoverRate,
            'unit'  => '٪',
            'icon'  => 'fas fa-user-minus',
            'color' => '#dc2626',
        ];

        // ۲. میانگین سابقه
        $stmt = $this->db->prepare(
            "SELECT AVG(DATEDIFF(CURDATE(), hire_date) / 365.25) AS avg_tenure
             FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'active'"
        );
        $stmt->execute([$systemId]);
        $avgTenure = $stmt->fetchColumn();

        $cards['avg_tenure'] = [
            'label' => 'میانگین سابقه',
            'value' => $avgTenure ? round((float) $avgTenure, 1) : 0,
            'unit'  => 'سال',
            'icon'  => 'fas fa-history',
            'color' => '#1E40AF',
        ];

        // ۳. نسبت زنان
        $stmt = $this->db->prepare(
            "SELECT 
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) AS female_count,
                COUNT(*) AS total
             FROM hr_employees WHERE system_id = ? AND employment_status = 'active'"
        );
        $stmt->execute([$systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $femaleRatio = ($row && $row['total'] > 0) 
            ? round(($row['female_count'] / $row['total']) * 100, 1) 
            : 0;

        $cards['female_ratio'] = [
            'label' => 'نسبت زنان',
            'value' => $femaleRatio,
            'unit'  => '٪',
            'icon'  => 'fas fa-venus',
            'color' => '#9333EA',
        ];

        // ۴. میانگین سن
        $stmt = $this->db->prepare(
            "SELECT AVG(TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) AS avg_age
             FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'active' 
               AND birth_date IS NOT NULL"
        );
        $stmt->execute([$systemId]);
        $avgAge = $stmt->fetchColumn();

        $cards['avg_age'] = [
            'label' => 'میانگین سن',
            'value' => $avgAge ? round((float) $avgAge, 1) : 0,
            'unit'  => 'سال',
            'icon'  => 'fas fa-birthday-cake',
            'color' => '#0F766E',
        ];

        // ۵. ساعت آموزش سرانه
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(training_hours), 0) AS total_hours
             FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'active'"
        );
        $stmt->execute([$systemId]);
        $totalHours = (float) $stmt->fetchColumn();
        $perCapita = $total > 0 ? round($totalHours / $total, 1) : 0;

        $cards['training_hours'] = [
            'label' => 'ساعت آموزش سرانه',
            'value' => $perCapita,
            'unit'  => 'ساعت',
            'icon'  => 'fas fa-graduation-cap',
            'color' => '#059669',
        ];

        // ✅ اصلاح: ۶. میانگین امتیاز ارزیابی (۱ سال اخیر بر اساس period_end)
        $stmt = $this->db->prepare(
            "SELECT AVG(overall_score) AS avg_score
             FROM hr_performance_reviews 
             WHERE system_id = ? 
               AND status = 'completed'
               AND period_end >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)"
        );
        $stmt->execute([$systemId]);
        $avgScore = $stmt->fetchColumn();

        $cards['avg_performance'] = [
            'label' => 'میانگین امتیاز عملکرد',
            'value' => $avgScore ? round((float) $avgScore, 2) : 0,
            'unit'  => 'از ۵',
            'icon'  => 'fas fa-star',
            'color' => '#f59e0b',
        ];

        return $cards;
    }

    private function getDepartmentDistribution(int $systemId): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.name, COUNT(e.id) AS count
             FROM hr_departments d
             LEFT JOIN hr_employees e ON e.department_id = d.id 
                AND e.employment_status = 'active'
             WHERE d.system_id = ? AND d.status = 'active'
             GROUP BY d.id, d.name
             ORDER BY count DESC
             LIMIT 10"
        );
        $stmt->execute([$systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getEmploymentDistribution(int $systemId): array
    {
        $stmt = $this->db->prepare(
            "SELECT employment_status, COUNT(*) AS count
             FROM hr_employees
             WHERE system_id = ?
             GROUP BY employment_status"
        );
        $stmt->execute([$systemId]);

        $result = [
            'active'     => 0,
            'on_leave'   => 0,
            'suspended'  => 0,
            'terminated' => 0,
            'retired'    => 0,
        ];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['employment_status']] = (int) $row['count'];
        }
        return $result;
    }

    private function getRecentEmployees(int $systemId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.hire_date, 
                    e.employment_status, e.photo_path,
                    d.name AS department_name,
                    p.title AS position_title
             FROM hr_employees e
             LEFT JOIN hr_departments d ON e.department_id = d.id
             LEFT JOIN hr_positions p ON e.position_id = p.id
             WHERE e.system_id = ?
             ORDER BY e.created_at DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAlerts(int $systemId): array
    {
        $alerts = [];

        // ۱. قراردادهای نزدیک به پایان
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'active'
               AND contract_end_date IS NOT NULL
               AND contract_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
        );
        $stmt->execute([$systemId]);
        $expiringContracts = (int) $stmt->fetchColumn();

        if ($expiringContracts > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fas fa-file-signature',
                'message' => hr_num($expiringContracts) . ' قرارداد تا ۳۰ روز آینده منقضی می‌شود.',
                'url'     => hr_url('employee', 'index', ['filter' => 'expiring_contract']),
            ];
        }

        // ۲. پایان دوره آزمایشی
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_employees 
             WHERE system_id = ? AND employment_status = 'active'
               AND probation_end_date IS NOT NULL
               AND probation_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)"
        );
        $stmt->execute([$systemId]);
        $expiringProbation = (int) $stmt->fetchColumn();

        if ($expiringProbation > 0) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'fas fa-hourglass-half',
                'message' => hr_num($expiringProbation) . ' دوره آزمایشی تا ۱۴ روز آینده به پایان می‌رسد.',
                'url'     => hr_url('employee', 'index', ['filter' => 'expiring_probation']),
            ];
        }

        // ۳. مستندات نزدیک به انقضا
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_employee_documents 
             WHERE system_id = ? 
               AND expiry_date IS NOT NULL
               AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)"
        );
        $stmt->execute([$systemId]);
        $expiringDocs = (int) $stmt->fetchColumn();

        if ($expiringDocs > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fas fa-file-alt',
                'message' => hr_num($expiringDocs) . ' سند پرسنلی تا ۶۰ روز آینده منقضی می‌شود.',
                'url'     => hr_url('employee_document', 'index'),
            ];
        }

        // ۴. دوره‌های آموزشی نزدیک به شروع
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_trainings 
             WHERE system_id = ? AND status IN ('planned', 'open')
               AND start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        $stmt->execute([$systemId]);
        $upcomingTrainings = (int) $stmt->fetchColumn();

        if ($upcomingTrainings > 0) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'fas fa-graduation-cap',
                'message' => hr_num($upcomingTrainings) . ' دوره آموزشی تا ۷ روز آینده شروع می‌شود.',
                'url'     => hr_url('training', 'index'),
            ];
        }

        return $alerts;
    }
}