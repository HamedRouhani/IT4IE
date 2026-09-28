<?php

namespace App\Software\Pmbok\Services;

/**
 * سرویس تحلیل پیشرفته پروژه بر اساس استاندارد PMBOK
 * شامل محاسبات EVM (Earned Value Management) و شاخص‌های سلامت
 */
class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * محاسبه کامل شاخص‌های EVM برای یک پروژه
     */
    public function calculateEVM($projectId)
    {
        // دریافت اطلاعات پروژه
        $project = $this->getProject($projectId);
        if (!$project) return null;

        // دریافت وظایف پروژه
        $tasks = $this->getProjectTasks($projectId);
        if (empty($tasks)) return null;

        $budgetAtCompletion = (float)$project['budget'];
        $totalPlannedHours = array_sum(array_column($tasks, 'planned_hours'));
        $totalActualHours = array_sum(array_column($tasks, 'actual_hours'));
        $taskCount = count($tasks);

        // محاسبه BAC (Budget at Completion) بر اساس ساعات
        $bac = $budgetAtCompletion;

        // محاسبه PV (Planned Value) - ارزش برنامه‌ریزی‌شده تا امروز
        $pv = 0;
        $today = date('Y-m-d');
        foreach ($tasks as $task) {
            if ($task['planned_start'] && $task['planned_end']) {
                $start = strtotime($task['planned_start']);
                $end = strtotime($task['planned_end']);
                $now = strtotime($today);
                
                if ($now >= $end) {
                    // وظیفه باید کامل می‌شد
                    $pv += $this->calculateTaskBudget($task, $totalPlannedHours, $bac, $taskCount);
                } elseif ($now >= $start) {
                    // وظیفه در حال انجام است (نسبت زمانی)
                    $totalDays = ($end - $start) / 86400;
                    $elapsedDays = ($now - $start) / 86400;
                    $progress = $totalDays > 0 ? $elapsedDays / $totalDays : 0;
                    $pv += $this->calculateTaskBudget($task, $totalPlannedHours, $bac, $taskCount) * $progress;
                }
            } else {
                // اگر تاریخ برنامه‌ریزی نشده، بر اساس درصد تکمیل محاسبه کن
                $pv += $this->calculateTaskBudget($task, $totalPlannedHours, $bac, $taskCount);
            }
        }

        // محاسبه EV (Earned Value) - ارزش کسب‌شده
        $ev = 0;
        foreach ($tasks as $task) {
            $percentComplete = (float)$task['percent_complete'];
            $percentComplete = max(0.0, min(100.0, $percentComplete));
            $ev += $this->calculateTaskBudget($task, $totalPlannedHours, $bac, $taskCount) * ($percentComplete / 100);
        }

        // محاسبه AC (Actual Cost) - هزینه واقعی
        // بر اساس ساعات واقعی × نرخ ساعتی
        $hourlyRate = $totalPlannedHours > 0 ? $bac / $totalPlannedHours : 0;
        $actualCostAvailable = $totalActualHours > 0 && $hourlyRate > 0;
        $ac = $actualCostAvailable ? $totalActualHours * $hourlyRate : null;

        // محاسبات مشتق‌شده EVM
        $sv = $ev - $pv;                    // Schedule Variance
        $cv = $ac !== null ? $ev - $ac : null; // Cost Variance
        $spi = $pv > 0 ? $ev / $pv : null;     // Schedule Performance Index
        $cpi = $ac !== null && $ac > 0 ? $ev / $ac : null; // Cost Performance Index
        
        $eac = $cpi !== null && $cpi > 0 ? $bac / $cpi : null; // Estimate at Completion
        $etc = $eac !== null && $ac !== null ? $eac - $ac : null; // Estimate to Complete
        $vac = $eac !== null ? $bac - $eac : null; // Variance at Completion
        $tcpi = $ac !== null && ($bac - $ev) > 0 && ($bac - $ac) > 0
            ? ($bac - $ev) / ($bac - $ac) 
            : null;                                  // To-Complete Performance Index

        // درصد پیشرفت کلی
        $overallProgress = $bac > 0 ? ($ev / $bac) * 100 : 0;

        // زمان تکمیل پیش‌بینی‌شده
        $estimatedCompletionDate = null;
        if ($project['planned_start'] && $project['planned_end'] && $spi !== null && $spi > 0) {
            $plannedDays = (strtotime($project['planned_end']) - strtotime($project['planned_start'])) / 86400;
            $estimatedDays = $plannedDays / $spi;
            $estimatedCompletionDate = date('Y-m-d', strtotime($project['planned_start'] . ' + ' . round($estimatedDays) . ' days'));
        }

        return [
            'bac' => $bac,
            'pv' => $pv,
            'ev' => $ev,
            'ac' => $ac,
            'actual_cost_available' => $actualCostAvailable,
            'sv' => $sv,
            'cv' => $cv,
            'spi' => $spi !== null ? round($spi, 3) : null,
            'cpi' => $cpi !== null ? round($cpi, 3) : null,
            'eac' => $eac,
            'etc' => $etc,
            'vac' => $vac,
            'tcpi' => $tcpi !== null ? round($tcpi, 3) : null,
            'overall_progress' => round($overallProgress, 1),
            'estimated_completion_date' => $estimatedCompletionDate,
            'total_tasks' => count($tasks),
            'completed_tasks' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed')),
            'in_progress_tasks' => count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')),
            'total_planned_hours' => $totalPlannedHours,
            'total_actual_hours' => $totalActualHours,
        ];
    }

    /**
     * محاسبه شاخص سلامت پروژه (Health Index)
     * ترکیبی از SPI, CPI, کیفیت و ریسک
     */
    public function calculateHealthIndex($projectId, $evmData)
    {
        if (!$evmData) return 0;

        // امتیاز زمان‌بندی (بر اساس SPI)
        $spiScore = $evmData['spi'] !== null ? $this->performanceScore((float)$evmData['spi']) : null;

        // امتیاز هزینه (بر اساس CPI)
        $cpiScore = $evmData['cpi'] !== null ? $this->performanceScore((float)$evmData['cpi']) : null;

        // امتیاز ریسک (بر اساس تعداد و شدت ریسک‌ها)
        $riskScore = $this->calculateRiskScore($projectId);

        // امتیاز کیفیت (بر اساس وضعیت وظایف)
        $qualityScore = $this->calculateQualityScore($projectId);

        // شاخص سلامت نهایی (میانگین وزنی)
        $weightedScore = ($riskScore * 0.20) + ($qualityScore * 0.20);
        $availableWeight = 0.40;
        if ($spiScore !== null) { $weightedScore += $spiScore * 0.30; $availableWeight += 0.30; }
        if ($cpiScore !== null) { $weightedScore += $cpiScore * 0.30; $availableWeight += 0.30; }
        $healthIndex = $availableWeight > 0 ? $weightedScore / $availableWeight : 50.0;

        return [
            'health_index' => round($healthIndex, 1),
            'spi_score' => $spiScore,
            'cpi_score' => $cpiScore,
            'risk_score' => $riskScore,
            'quality_score' => $qualityScore,
            'status' => $this->getHealthStatus($healthIndex),
            'status_color' => $this->getHealthColor($healthIndex),
        ];
    }

    /**
     * محاسبه امتیاز ریسک
     */
    private function calculateRiskScore($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_risks,
                SUM(CASE WHEN status = 'identified' THEN 1 ELSE 0 END) as open_risks,
                AVG(risk_score) as avg_risk_score
            FROM pmbok_risks 
            WHERE project_id = ?
        ");
        $stmt->execute([$projectId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data || $data['total_risks'] == 0) return 100; // بدون ریسک = عالی

        // هرچه ریسک باز و شدیدتر، امتیاز کمتر
        $openRatio = $data['open_risks'] / $data['total_risks'];
        $avgScore = (float)$data['avg_risk_score'];
        
        // امتیاز از ۱۰۰ تا ۰
        $score = 100 - ($openRatio * 40) - min(60, $avgScore * 2);
        return max(0, min(100, round($score)));
    }

    /**
     * محاسبه امتیاز کیفیت
     */
    private function calculateQualityScore($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
            FROM pmbok_project_tasks 
            WHERE project_id = ?
        ");
        $stmt->execute([$projectId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data || $data['total'] == 0) return 50;

        $completionRatio = $data['completed'] / $data['total'];
        $activeRatio = $data['in_progress'] / $data['total'];
        
        // ترکیب نسبت تکمیل و فعالیت
        $score = ($completionRatio * 70) + ($activeRatio * 30);
        return max(0, min(100, round($score)));
    }

    /**
     * تعیین وضعیت سلامت
     */
    private function getHealthStatus($score)
    {
        if ($score >= 85) return 'excellent';
        if ($score >= 70) return 'good';
        if ($score >= 55) return 'fair';
        if ($score >= 40) return 'at_risk';
        return 'critical';
    }

    /**
     * رنگ وضعیت سلامت
     */
    private function getHealthColor($score)
    {
        if ($score >= 85) return '#10B981'; // سبز
        if ($score >= 70) return '#3B82F6'; // آبی
        if ($score >= 55) return '#F59E0B'; // نارنجی
        if ($score >= 40) return '#EF4444'; // قرمز
        return '#991B1B'; // قرمز تیره
    }

    private function performanceScore(float $index): int
    {
        if ($index >= 1.0) return 100;
        if ($index >= 0.9) return 80;
        if ($index >= 0.8) return 60;
        if ($index >= 0.7) return 40;
        return 20;
    }

    /**
     * محاسبه بودجه تخصیص‌یافته به یک وظیفه
     */
    private function calculateTaskBudget($task, $totalPlannedHours, $totalBudget, int $taskCount)
    {
        if ($totalPlannedHours <= 0) return $taskCount > 0 ? $totalBudget / $taskCount : 0.0;
        $ratio = (float)$task['planned_hours'] / $totalPlannedHours;
        return $totalBudget * $ratio;
    }

    /**
     * دریافت اطلاعات پروژه
     */
    private function getProject($projectId)
    {
        $stmt = $this->db->prepare("SELECT * FROM pmbok_projects WHERE id = ?");
        $stmt->execute([$projectId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت وظایف پروژه
     */
    private function getProjectTasks($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT pt.*, t.name as task_name, t.code as task_code
            FROM pmbok_project_tasks pt
            JOIN pmbok_tasks t ON pt.task_id = t.id
            WHERE pt.project_id = ?
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت داده‌های نمودار پیشرفت (برای Chart.js)
     */
    public function getProgressChartData($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(COALESCE(planned_start, created_at), '%Y-%m') as month,
                COUNT(*) as planned,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM pmbok_project_tasks 
            WHERE project_id = ?
            GROUP BY month
            ORDER BY month ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت توزیع وظایف بر اساس حوزه دانشی
     */
    public function getTasksByKnowledgeArea($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                ka.name as knowledge_area,
                ka.code as ka_code,
                COUNT(*) as total,
                SUM(CASE WHEN pt.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN pt.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
            FROM pmbok_project_tasks pt
            JOIN pmbok_tasks t ON pt.task_id = t.id
            JOIN pmbok_knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE pt.project_id = ?
            GROUP BY ka.id
            ORDER BY ka.code
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
