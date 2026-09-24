<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * ============================================================
 * ReportController - گزارش‌های تحلیلی منابع انسانی
 * ============================================================
 * مسیر: app/software/hr/app/Controllers/ReportController.php
 * ============================================================
 */
class ReportController extends Controller
{
    private $db;
    private $systemId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->systemId = !empty($_SESSION['hr_active_system'])
            ? (int) $_SESSION['hr_active_system']
            : null;
    }

    /**
     * صفحه اصلی گزارش‌ها (فهرست کارت‌ها)
     */
    public function index()
    {
        $this->requireAuth();

        $this->renderSoftware('report/index', [
            'pageTitle'    => 'گزارش‌ها',
            'softwareName' => 'HR Analyzer',
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۱: خلاصه سازمانی
    // ============================================================
    public function summary()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── آمار کلی ───
        $overview = [
            'total_employees'    => $this->scalar("SELECT COUNT(*) FROM hr_employees WHERE system_id = ?", [$sid]),
            'active_employees'   => $this->scalar("SELECT COUNT(*) FROM hr_employees WHERE system_id = ? AND employment_status='active'", [$sid]),
            'total_departments'  => $this->scalar("SELECT COUNT(*) FROM hr_departments WHERE system_id = ? AND status='active'", [$sid]),
            'total_positions'    => $this->scalar("SELECT COUNT(*) FROM hr_positions WHERE system_id = ? AND is_active=1", [$sid]),
            'total_candidates'   => $this->scalar("SELECT COUNT(*) FROM hr_candidates WHERE system_id = ?", [$sid]),
            'total_trainings'    => $this->scalar("SELECT COUNT(*) FROM hr_trainings WHERE system_id = ?", [$sid]),
            'total_goals'        => $this->scalar("SELECT COUNT(*) FROM hr_goals WHERE system_id = ?", [$sid]),
            'total_reviews'      => $this->scalar("SELECT COUNT(*) FROM hr_performance_reviews WHERE system_id = ?", [$sid]),
            'total_competencies' => $this->scalar("SELECT COUNT(*) FROM hr_competencies WHERE system_id = ? OR system_id IS NULL", [$sid]),
            'total_compensations'=> $this->scalar("SELECT COUNT(*) FROM hr_compensations WHERE system_id = ?", [$sid]),
        ];

        // ─── توزیع دپارتمان‌ها ───
        $departments = $this->rows(
            "SELECT d.name, COUNT(e.id) AS emp_count
             FROM hr_departments d
             LEFT JOIN hr_employees e ON e.department_id = d.id 
                AND e.employment_status='active' AND e.system_id = d.system_id
             WHERE d.system_id = ? AND d.status='active'
             GROUP BY d.id, d.name
             ORDER BY emp_count DESC",
            [$sid]
        );

        // ─── توزیع جنسیت ───
        $genderDist = $this->rows(
            "SELECT gender, COUNT(*) AS count
             FROM hr_employees WHERE system_id=? AND employment_status='active'
             GROUP BY gender",
            [$sid]
        );

        // ─── توزیع نوع قرارداد ───
        $contractDist = $this->rows(
            "SELECT contract_type, COUNT(*) AS count
             FROM hr_employees WHERE system_id=? AND employment_status='active'
             GROUP BY contract_type",
            [$sid]
        );

        // ─── توزیع مدرک تحصیلی ───
        $educationDist = $this->rows(
            "SELECT education_level, COUNT(*) AS count
             FROM hr_employees WHERE system_id=? AND employment_status='active'
             GROUP BY education_level ORDER BY count DESC",
            [$sid]
        );

        // ─── استخدام‌های سال جاری (ماهانه) ───
        $hiresByMonth = $this->rows(
            "SELECT MONTH(hire_date) AS m, COUNT(*) AS count
             FROM hr_employees WHERE system_id=? AND YEAR(hire_date) = YEAR(CURDATE())
             GROUP BY MONTH(hire_date) ORDER BY m",
            [$sid]
        );

        $this->renderSoftware('report/summary', [
            'pageTitle'      => 'خلاصه سازمانی',
            'softwareName'   => 'HR Analyzer',
            'overview'       => $overview,
            'departments'    => $departments,
            'genderDist'     => $genderDist,
            'contractDist'   => $contractDist,
            'educationDist'  => $educationDist,
            'hiresByMonth'   => $hiresByMonth,
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۲: کارکنان
    // ============================================================
    public function employees()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        $filters = [
            'department_id'    => !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null,
            'employment_status'=> trim($_GET['employment_status'] ?? ''),
            'contract_type'    => trim($_GET['contract_type'] ?? ''),
        ];

        $where = "e.system_id = ?";
        $params = [$sid];

        if ($filters['department_id']) {
            $where .= " AND e.department_id = ?";
            $params[] = $filters['department_id'];
        }
        if ($filters['employment_status']) {
            $where .= " AND e.employment_status = ?";
            $params[] = $filters['employment_status'];
        }
        if ($filters['contract_type']) {
            $where .= " AND e.contract_type = ?";
            $params[] = $filters['contract_type'];
        }

        $employees = $this->rows(
            "SELECT e.*, 
                    d.name AS department_name, 
                    p.title AS position_title,
                    g.name AS grade_name
             FROM hr_employees e
             LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = e.system_id
             LEFT JOIN hr_positions p ON p.id = e.position_id AND p.system_id = e.system_id
             LEFT JOIN hr_job_grades g ON g.id = e.grade_id AND g.system_id = e.system_id
             WHERE $where
             ORDER BY e.employee_code",
            $params
        );

        // آمار
        $stats = [
            'total'      => count($employees),
            'active'     => count(array_filter($employees, fn($e) => $e['employment_status'] === 'active')),
            'on_leave'   => count(array_filter($employees, fn($e) => $e['employment_status'] === 'on_leave')),
            'terminated' => count(array_filter($employees, fn($e) => $e['employment_status'] === 'terminated')),
        ];

        // برای فیلترها
        $departments = $this->rows(
            "SELECT id, name FROM hr_departments WHERE system_id=? AND status='active' ORDER BY name",
            [$sid]
        );

        $this->renderSoftware('report/employees', [
            'pageTitle'      => 'گزارش کارکنان',
            'softwareName'   => 'HR Analyzer',
            'employees'      => $employees,
            'stats'          => $stats,
            'filters'        => $filters,
            'hr_departments' => $departments,
            'statusOptions'  => [
                'active' => 'شاغل', 'on_leave' => 'مرخصی',
                'suspended' => 'تعلیق', 'terminated' => 'خاتمه', 'retired' => 'بازنشسته'
            ],
            'contractOptions'=> [
                'permanent' => 'دائمی', 'fixed_term' => 'مدت معین',
                'project' => 'پروژه‌ای', 'intern' => 'کارآموز', 'consultant' => 'مشاور'
            ],
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۳: جذب و استخدام
    // ============================================================
    public function recruitment()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── آمار نیازها ───
        $recruitments = $this->rows(
            "SELECT r.*, 
                    p.title AS position_title, d.name AS department_name,
                    (SELECT COUNT(*) FROM hr_candidates c 
                     WHERE c.recruitment_id = r.id AND c.system_id = r.system_id) AS candidates_count,
                    (SELECT COUNT(*) FROM hr_candidates c 
                     WHERE c.recruitment_id = r.id AND c.system_id = r.system_id 
                       AND c.status = 'hired') AS hired_count
             FROM hr_recruitments r
             LEFT JOIN hr_positions p ON p.id = r.position_id AND p.system_id = r.system_id
             LEFT JOIN hr_departments d ON d.id = r.department_id AND d.system_id = r.system_id
             WHERE r.system_id = ?
             ORDER BY r.opened_date DESC",
            [$sid]
        );

        // ─── قیف متقاضیان ───
        $candidateFunnel = $this->rows(
            "SELECT status, COUNT(*) AS count
             FROM hr_candidates WHERE system_id=?
             GROUP BY status",
            [$sid]
        );

        // ─── منابع جذب ───
        $sources = $this->rows(
            "SELECT source, COUNT(*) AS count
             FROM hr_candidates WHERE system_id=?
             GROUP BY source ORDER BY count DESC",
            [$sid]
        );

        // ─── آمار مصاحبه‌ها ───
        $interviews = $this->rows(
            "SELECT i.*, 
                    c.first_name, c.last_name, c.candidate_code,
                    e.first_name AS interviewer_first, e.last_name AS interviewer_last
             FROM hr_interviews i
             LEFT JOIN hr_candidates c ON c.id = i.candidate_id AND c.system_id = i.system_id
             LEFT JOIN hr_employees e ON e.id = i.interviewer_id AND e.system_id = i.system_id
             WHERE i.system_id = ?
             ORDER BY i.scheduled_date DESC LIMIT 100",
            [$sid]
        );

        // ─── میانگین زمان جذب (روز) ───
        $avgTimeToHire = $this->scalar(
            "SELECT AVG(DATEDIFF(updated_at, created_at))
             FROM hr_candidates 
             WHERE system_id=? AND status='hired'",
            [$sid]
        );

        $this->renderSoftware('report/recruitment', [
            'pageTitle'        => 'گزارش جذب و استخدام',
            'softwareName'     => 'HR Analyzer',
            'recruitments'     => $recruitments,
            'candidateFunnel'  => $candidateFunnel,
            'sources'          => $sources,
            'interviews'       => $interviews,
            'avgTimeToHire'    => round((float) $avgTimeToHire, 1),
            'statusOptions'    => \App\Software\Hr\Models\Recruitment::getStatusOptions(),
            'candidateStatus'  => \App\Software\Hr\Models\Candidate::getStatusOptions(),
            'sourceOptions'    => \App\Software\Hr\Models\Candidate::getSourceOptions(),
            'flash'            => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۴: عملکرد
    // ============================================================
    public function performance()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── اهداف ───
        $goals = $this->rows(
            "SELECT g.*, 
                    e.first_name, e.last_name, e.employee_code
             FROM hr_goals g
             LEFT JOIN hr_employees e ON e.id = g.employee_id AND e.system_id = g.system_id
             WHERE g.system_id = ?
             ORDER BY g.due_date DESC LIMIT 200",
            [$sid]
        );

        // ─── آمار اهداف ───
        $goalStats = [
            'total'     => count($goals),
            'active'    => count(array_filter($goals, fn($g) => $g['status'] === 'active')),
            'completed' => count(array_filter($goals, fn($g) => $g['status'] === 'completed')),
            'overdue'   => count(array_filter($goals, fn($g) => $g['status'] === 'active' && $g['due_date'] < date('Y-m-d'))),
        ];
        $goalStats['avg_progress'] = !empty($goals)
            ? round(array_sum(array_column($goals, 'progress')) / count($goals), 1)
            : 0;

        // ─── ارزیابی‌ها ───
        $reviews = $this->rows(
            "SELECT r.*, 
                    e.first_name, e.last_name, e.employee_code,
                    rev.first_name AS reviewer_first, rev.last_name AS reviewer_last
             FROM hr_performance_reviews r
             LEFT JOIN hr_employees e ON e.id = r.employee_id AND e.system_id = r.system_id
             LEFT JOIN hr_employees rev ON rev.id = r.reviewer_id AND rev.system_id = r.system_id
             WHERE r.system_id = ?
             ORDER BY r.period_end DESC LIMIT 200",
            [$sid]
        );

        // ─── آمار ارزیابی ───
        $reviewStats = [
            'total'     => count($reviews),
            'completed' => count(array_filter($reviews, fn($r) => $r['status'] === 'completed')),
            'avg_score' => 0,
        ];
        $scores = array_filter(array_column($reviews, 'overall_score'), fn($s) => $s !== null);
        if (!empty($scores)) {
            $reviewStats['avg_score'] = round(array_sum($scores) / count($scores), 2);
        }

        // ─── توزیع رتبه‌ها ───
        $ratingDist = $this->rows(
            "SELECT rating, COUNT(*) AS count
             FROM hr_performance_reviews 
             WHERE system_id=? AND rating IS NOT NULL
             GROUP BY rating",
            [$sid]
        );

        $this->renderSoftware('report/performance', [
            'pageTitle'      => 'گزارش عملکرد',
            'softwareName'   => 'HR Analyzer',
            'goals'          => $goals,
            'goalStats'      => $goalStats,
            'reviews'        => $reviews,
            'reviewStats'    => $reviewStats,
            'ratingDist'     => $ratingDist,
            'goalStatuses'   => \App\Software\Hr\Models\Goal::getStatusOptions(),
            'reviewStatuses' => \App\Software\Hr\Models\PerformanceReview::getStatusOptions(),
            'ratingOptions'  => \App\Software\Hr\Models\PerformanceReview::getRatingOptions(),
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۵: آموزش
    // ============================================================
    public function training()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── دوره‌ها ───
        $trainings = $this->rows(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM hr_enrollments e 
                     WHERE e.training_id = t.id AND e.system_id = t.system_id) AS enrollments_count,
                    (SELECT COUNT(*) FROM hr_enrollments e 
                     WHERE e.training_id = t.id AND e.system_id = t.system_id 
                       AND e.status = 'completed') AS completed_count
             FROM hr_trainings t
             WHERE t.system_id = ?
             ORDER BY t.start_date DESC",
            [$sid]
        );

        // ─── آمار ───
        $trainingStats = [
            'total'         => count($trainings),
            'completed'     => count(array_filter($trainings, fn($t) => $t['status'] === 'completed')),
            'in_progress'   => count(array_filter($trainings, fn($t) => $t['status'] === 'in_progress')),
            'total_hours'   => round(array_sum(array_column($trainings, 'duration_hours')), 1),
            'total_cost'    => array_sum(array_column($trainings, 'total_cost')),
        ];

        // ─── ثبت‌نام‌ها ───
        $enrollments = $this->rows(
            "SELECT e.*, 
                    t.title AS training_title, t.code AS training_code,
                    emp.first_name, emp.last_name, emp.employee_code
             FROM hr_enrollments e
             LEFT JOIN hr_trainings t ON t.id = e.training_id AND t.system_id = e.system_id
             LEFT JOIN hr_employees emp ON emp.id = e.employee_id AND emp.system_id = e.system_id
             WHERE e.system_id = ?
             ORDER BY e.enrolled_date DESC LIMIT 200",
            [$sid]
        );

        // ─── توزیع وضعیت ثبت‌نام ───
        $enrollmentDist = $this->rows(
            "SELECT status, COUNT(*) AS count
             FROM hr_enrollments WHERE system_id=?
             GROUP BY status",
            [$sid]
        );

        $this->renderSoftware('report/training', [
            'pageTitle'       => 'گزارش آموزش',
            'softwareName'    => 'HR Analyzer',
            'trainings'       => $trainings,
            'trainingStats'   => $trainingStats,
            'enrollments'     => $enrollments,
            'enrollmentDist'  => $enrollmentDist,
            'trainingStatuses'=> \App\Software\Hr\Models\Training::getStatusOptions(),
            'enrollStatuses'  => \App\Software\Hr\Models\Enrollment::getStatusOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۶: جبران خدمات
    // ============================================================
    public function compensation()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        $compensations = $this->rows(
            "SELECT c.*, 
                    e.first_name, e.last_name, e.employee_code,
                    d.name AS department_name,
                    p.title AS position_title
             FROM hr_compensations c
             LEFT JOIN hr_employees e ON e.id = c.employee_id AND e.system_id = c.system_id
             LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = c.system_id
             LEFT JOIN hr_positions p ON p.id = e.position_id AND p.system_id = c.system_id
             WHERE c.system_id = ?
             ORDER BY c.effective_date DESC",
            [$sid]
        );

        // ─── آمار ───
        $active = array_filter($compensations, fn($c) => empty($c['end_date']) || $c['end_date'] >= date('Y-m-d'));

        $stats = [
            'total'         => count($compensations),
            'active'        => count($active),
            'total_payroll' => array_sum(array_column($active, 'total_fixed')),
            'avg_salary'    => !empty($active) ? round(array_sum(array_column($active, 'total_fixed')) / count($active)) : 0,
            'min_salary'    => !empty($active) ? min(array_column($active, 'total_fixed')) : 0,
            'max_salary'    => !empty($active) ? max(array_column($active, 'total_fixed')) : 0,
        ];

        // ─── حقوق بر اساس دپارتمان ───
        $byDepartment = $this->rows(
            "SELECT d.name AS department_name,
                    COUNT(c.id) AS count,
                    AVG(c.total_fixed) AS avg_salary,
                    SUM(c.total_fixed) AS total_salary
             FROM hr_compensations c
             LEFT JOIN hr_employees e ON e.id = c.employee_id AND e.system_id = c.system_id
             LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = c.system_id
             WHERE c.system_id = ? AND (c.end_date IS NULL OR c.end_date >= CURDATE())
             GROUP BY d.id, d.name
             ORDER BY total_salary DESC",
            [$sid]
        );

        $this->renderSoftware('report/compensation', [
            'pageTitle'      => 'گزارش جبران خدمات',
            'softwareName'   => 'HR Analyzer',
            'compensations'  => $compensations,
            'stats'          => $stats,
            'byDepartment'   => $byDepartment,
            'currencyOptions'=> \App\Software\Hr\Models\Compensation::getCurrencyOptions(),
            'flash'          => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۷: KPI
    // ============================================================
    public function kpi()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── KPIها با آخرین مقدار ───
        $kpis = $this->rows(
            "SELECT k.*,
                    (SELECT value FROM hr_kpi_values 
                     WHERE kpi_id = k.id AND system_id = ? 
                     ORDER BY period_date DESC LIMIT 1) AS latest_value,
                    (SELECT target_value FROM hr_kpi_values 
                     WHERE kpi_id = k.id AND system_id = ? 
                     ORDER BY period_date DESC LIMIT 1) AS latest_target,
                    (SELECT period_date FROM hr_kpi_values 
                     WHERE kpi_id = k.id AND system_id = ? 
                     ORDER BY period_date DESC LIMIT 1) AS latest_date,
                    (SELECT COUNT(*) FROM hr_kpi_values 
                     WHERE kpi_id = k.id AND system_id = ?) AS values_count
             FROM hr_kpis k
             WHERE k.is_active = 1
             ORDER BY k.sort_order, k.id",
            [$sid, $sid, $sid, $sid]
        );

        // ─── توزیع دسته‌بندی ───
        $categoryDist = $this->rows(
            "SELECT category, COUNT(*) AS count
             FROM hr_kpis WHERE is_active=1
             GROUP BY category",
            []
        );

        // ─── مقادیر اخیر ───
        $recentValues = $this->rows(
            "SELECT v.*, k.code AS kpi_code, k.name AS kpi_name, k.unit, k.category
             FROM hr_kpi_values v
             INNER JOIN hr_kpis k ON k.id = v.kpi_id
             WHERE v.system_id = ?
             ORDER BY v.period_date DESC, v.id DESC LIMIT 50",
            [$sid]
        );

        // ─── آمار ───
        $stats = [
            'total_kpis'      => count($kpis),
            'with_values'     => count(array_filter($kpis, fn($k) => (int) $k['values_count'] > 0)),
            'total_values'    => array_sum(array_column($kpis, 'values_count')),
        ];

        $this->renderSoftware('report/kpi', [
            'pageTitle'       => 'گزارش KPI',
            'softwareName'    => 'HR Analyzer',
            'kpis'            => $kpis,
            'categoryDist'    => $categoryDist,
            'recentValues'    => $recentValues,
            'stats'           => $stats,
            'categoryOptions' => \App\Software\Hr\Models\KPI::getCategoryOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // گزارش ۸: شایستگی‌ها
    // ============================================================
    public function competency()
    {
        $this->requireAuth();
        $sid = $this->systemId;

        // ─── شایستگی‌ها ───
        $competencies = $this->rows(
            "SELECT c.*,
                    p.name AS parent_name,
                    (SELECT COUNT(*) FROM hr_employee_competencies ec 
                     WHERE ec.competency_id = c.id AND ec.system_id = c.system_id) AS assigned_count
             FROM hr_competencies c
             LEFT JOIN hr_competencies p ON p.id = c.parent_id AND p.system_id = c.system_id
             WHERE c.system_id = ?
             ORDER BY c.sort_order, c.name",
            [$sid]
        );

        // ─── آمار ───
        $stats = [
            'total'      => count($competencies),
            'active'     => count(array_filter($competencies, fn($c) => $c['status'] === 'active')),
            'core'       => count(array_filter($competencies, fn($c) => !empty($c['is_core']))),
            'assigned'   => array_sum(array_column($competencies, 'assigned_count')),
        ];

        // ─── توزیع دسته‌بندی ───
        $categoryDist = $this->rows(
            "SELECT category, COUNT(*) AS count
             FROM hr_competencies WHERE system_id=? AND status='active'
             GROUP BY category",
            [$sid]
        );

        // ─── فاصله مهارتی (Skill Gap) - کارمندانی که شایستگی هسته‌ای ندارند ───
        $skillGaps = $this->rows(
            "SELECT ec.*,
                    c.name AS competency_name, c.category,
                    e.first_name, e.last_name, e.employee_code
             FROM hr_employee_competencies ec
             INNER JOIN hr_competencies c ON c.id = ec.competency_id AND c.system_id = ec.system_id
             INNER JOIN hr_employees e ON e.id = ec.employee_id AND e.system_id = ec.system_id
             WHERE ec.system_id = ? 
               AND ec.required_level > ec.current_level
             ORDER BY (ec.required_level - ec.current_level) DESC
             LIMIT 100",
            [$sid]
        );

        $this->renderSoftware('report/competency', [
            'pageTitle'       => 'گزارش شایستگی‌ها',
            'softwareName'    => 'HR Analyzer',
            'competencies'    => $competencies,
            'stats'           => $stats,
            'categoryDist'    => $categoryDist,
            'skillGaps'       => $skillGaps,
            'categoryOptions' => \App\Software\Hr\Models\Competency::getCategoryOptions(),
            'typeOptions'     => \App\Software\Hr\Models\Competency::getTypeOptions(),
            'flash'           => hr_flash_get(),
        ], 'hr');
    }

    // ============================================================
    // متدهای کمکی
    // ============================================================

    /**
     * اجرای کوئری و دریافت یک مقدار اسکالر
     */
    private function scalar(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * اجرای کوئری و دریافت چند ردیف
     */
    private function rows(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}