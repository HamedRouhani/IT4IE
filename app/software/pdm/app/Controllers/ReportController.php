<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Software\Pdm\Models\Asset;
use App\Software\Pdm\Models\WorkOrder;
use App\Software\Pdm\Models\FailureMode;
use App\Software\Pdm\Models\SparePart;
use App\Software\Pdm\Models\KPI;
use App\Software\Pdm\Models\MaintenancePlan;
use App\Helpers\DateHelper;

/**
 * ============================================================
 * ReportController - گزارش‌های حرفه‌ای
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/ReportController.php
 * 
 * شامل:
 * - داشبورد گزارش‌ها
 * - گزارش دستورکارها (با فیلتر تاریخ و وضعیت)
 * - گزارش خرابی‌ها (Pareto)
 * - گزارش شاخص‌ها (MTTR/MTBF)
 * - گزارش موجودی قطعات
 * - گزارش برنامه‌های نگهداری
 * - خروجی چاپ
 * ============================================================
 */
class ReportController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * صفحه اصلی گزارش‌ها
     */
    public function index()
    {
        $this->requireAuth();

        $assetModel = new Asset();
        $woModel = new WorkOrder();
        $failureModel = new FailureMode();
        $spareModel = new SparePart();

        // آمار کلی برای داشبورد گزارش
        $overview = [
            'total_assets'      => $assetModel->count(),
            'total_work_orders' => $woModel->count(),
            'completed_wos'     => $woModel->count("status = 'completed'"),
            'open_wos'          => $woModel->count("status IN ('open', 'in_progress')"),
            'total_failures'    => $failureModel->count(),
            'low_stock_parts'   => count($spareModel->getLowStock()),
        ];

        // KPIها
        $kpiModel = new KPI();
        $kpiValues = $kpiModel->calculateAll();

        $this->renderSoftware('report/index', [
            'pageTitle'    => 'گزارش‌ها',
            'softwareName' => 'PdM Analyzer',
            'overview'     => $overview,
            'kpiValues'    => $kpiValues,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * گزارش دستورکارها
     */
    public function workOrders()
    {
        $this->requireAuth();

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $assetId = !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null;

        // تبدیل تاریخ شمسی به میلادی
        $dateFromG = $dateFrom ? DateHelper::toGregorian($dateFrom) : null;
        $dateToG = $dateTo ? DateHelper::toGregorian($dateTo) : null;

        $sql = "SELECT wo.*, 
                       a.name AS asset_name, 
                       a.asset_code,
                       mt.name AS maintenance_type_name
                FROM pm_work_orders wo
                LEFT JOIN pm_assets a ON wo.asset_id = a.id
                LEFT JOIN pm_maintenance_types mt ON wo.maintenance_type_id = mt.id
                WHERE wo.system_id = ?";
        $params = [pdm_active_system_id()];

        if ($status) {
            $sql .= " AND wo.status = ?";
            $params[] = $status;
        }

        if ($assetId) {
            $sql .= " AND wo.asset_id = ?";
            $params[] = $assetId;
        }

        if ($dateFromG) {
            $sql .= " AND DATE(wo.created_at) >= ?";
            $params[] = $dateFromG;
        }

        if ($dateToG) {
            $sql .= " AND DATE(wo.created_at) <= ?";
            $params[] = $dateToG;
        }

        $sql .= " ORDER BY wo.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $workOrders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // آمار خلاصه
        $summary = [
            'total'       => count($workOrders),
            'open'        => count(array_filter($workOrders, fn($w) => $w['status'] === 'open')),
            'in_progress' => count(array_filter($workOrders, fn($w) => $w['status'] === 'in_progress')),
            'completed'   => count(array_filter($workOrders, fn($w) => $w['status'] === 'completed')),
            'cancelled'   => count(array_filter($workOrders, fn($w) => $w['status'] === 'cancelled')),
        ];

        $assetModel = new Asset();

        $this->renderSoftware('report/work_orders', [
            'pageTitle'    => 'گزارش دستورکارها',
            'softwareName' => 'PdM Analyzer',
            'workOrders'   => $workOrders,
            'summary'      => $summary,
            'pdm_assets'   => $assetModel->getSelectList(),
            'filters'      => [
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'status'    => $status,
                'asset_id'  => $assetId,
            ],
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * گزارش خرابی‌ها (Pareto)
     */
    public function failures()
    {
        $this->requireAuth();

        $failureModel = new FailureMode();
        $failureModes = $failureModel->getAllWithDetails();

        // Pareto: مرتب‌سازی بر اساس RPN (قبلاً در Model انجام شده)
        // محاسبه درصد تجمعی
        $totalRPN = array_sum(array_column($failureModes, 'rpn'));
        $cumulative = 0;
        foreach ($failureModes as &$fm) {
            $cumulative += (int) $fm['rpn'];
            $fm['cumulative_percent'] = $totalRPN > 0 
                ? round(($cumulative / $totalRPN) * 100, 1) 
                : 0;
        }
        unset($fm);

        // آمار
        $summary = [
            'total'         => count($failureModes),
            'critical'      => count(array_filter($failureModes, fn($f) => $f['rpn'] >= 200)),
            'high'          => count(array_filter($failureModes, fn($f) => $f['rpn'] >= 100 && $f['rpn'] < 200)),
            'medium'        => count(array_filter($failureModes, fn($f) => $f['rpn'] >= 50 && $f['rpn'] < 100)),
            'low'           => count(array_filter($failureModes, fn($f) => $f['rpn'] < 50)),
            'total_rpn'     => $totalRPN,
        ];

        $this->renderSoftware('report/failures', [
            'pageTitle'    => 'گزارش خرابی‌ها (Pareto)',
            'softwareName' => 'PdM Analyzer',
            'failureModes' => $failureModes,
            'summary'      => $summary,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * گزارش KPIها
     */
    public function kpi()
    {
        $this->requireAuth();

        $kpiModel = new KPI();
        $woModel = new WorkOrder();
        $assetModel = new Asset();

        $kpiValues = $kpiModel->calculateAll();
        $kpiDefinitions = $kpiModel->all();

        // برای نمودار روند MTTR: آخرین ۱۰ دستورکار تکمیل‌شده
        $stmt = $this->db->prepare(
            "SELECT wo_number, title, 
                    started_at, completed_at,
                    TIMESTAMPDIFF(SECOND, started_at, completed_at) / 3600 AS repair_hours
             FROM pm_work_orders
             WHERE system_id = ?
               AND status = 'completed'
               AND started_at IS NOT NULL
               AND completed_at IS NOT NULL
             ORDER BY completed_at DESC
             LIMIT 10"
        );
        $stmt->execute([pdm_active_system_id()]);
        $recentRepairs = array_reverse($stmt->fetchAll(\PDO::FETCH_ASSOC));

        // MTTR ماهانه (اختیاری)
        $stmt = $this->db->prepare(
            "SELECT 
                DATE_FORMAT(completed_at, '%Y-%m') AS month,
                AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) / 3600 AS avg_mttr,
                COUNT(*) AS count
             FROM pm_work_orders
             WHERE system_id = ?
               AND status = 'completed'
               AND started_at IS NOT NULL
               AND completed_at IS NOT NULL
               AND completed_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(completed_at, '%Y-%m')
             ORDER BY month ASC"
        );
        $stmt->execute([pdm_active_system_id()]);
        $monthlyMTTR = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->renderSoftware('report/kpi', [
            'pageTitle'       => 'گزارش شاخص‌ها',
            'softwareName'    => 'PdM Analyzer',
            'kpiValues'       => $kpiValues,
            'kpiDefinitions'  => $kpiDefinitions,
            'recentRepairs'   => $recentRepairs,
            'monthlyMTTR'     => $monthlyMTTR,
            'flash'           => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * گزارش موجودی قطعات
     */
    public function inventory()
    {
        $this->requireAuth();

        $spareModel = new SparePart();
        $parts = $spareModel->getAllWithDetails();
        $stats = $spareModel->getStockStats();
        $lowStock = $spareModel->getLowStock();

        $this->renderSoftware('report/inventory', [
            'pageTitle'    => 'گزارش موجودی قطعات',
            'softwareName' => 'PdM Analyzer',
            'parts'        => $parts,
            'stats'        => $stats,
            'lowStock'     => $lowStock,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * گزارش برنامه‌های نگهداری
     */
    public function maintenance()
    {
        $this->requireAuth();

        $planModel = new MaintenancePlan();
        $plans = $planModel->getAllWithDetails();
        $duePlans = $planModel->getDuePlans();
        $upcomingPlans = $planModel->getUpcomingPlans(30);

        $this->renderSoftware('report/maintenance', [
            'pageTitle'     => 'گزارش برنامه‌های نگهداری',
            'softwareName'  => 'PdM Analyzer',
            'plans'         => $plans,
            'duePlans'      => $duePlans,
            'upcomingPlans' => $upcomingPlans,
            'flash'         => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * چاپ گزارش دستورکارها
     */
    public function printWorkOrders()
    {
        $this->requireAuth();

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $assetId = !empty($_GET['asset_id']) ? (int) $_GET['asset_id'] : null;

        $dateFromG = $dateFrom ? DateHelper::toGregorian($dateFrom) : null;
        $dateToG = $dateTo ? DateHelper::toGregorian($dateTo) : null;

        $sql = "SELECT wo.*, 
                       a.name AS asset_name, 
                       a.asset_code,
                       mt.name AS maintenance_type_name
                FROM pm_work_orders wo
                LEFT JOIN pm_assets a ON wo.asset_id = a.id
                LEFT JOIN pm_maintenance_types mt ON wo.maintenance_type_id = mt.id
                WHERE wo.system_id = ?";
        $params = [pdm_active_system_id()];

        if ($status) {
            $sql .= " AND wo.status = ?";
            $params[] = $status;
        }
        if ($assetId) {
            $sql .= " AND wo.asset_id = ?";
            $params[] = $assetId;
        }
        if ($dateFromG) {
            $sql .= " AND DATE(wo.created_at) >= ?";
            $params[] = $dateFromG;
        }
        if ($dateToG) {
            $sql .= " AND DATE(wo.created_at) <= ?";
            $params[] = $dateToG;
        }

        $sql .= " ORDER BY wo.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $workOrders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // ⚠️ مسیر صحیح: MODULAR_APP_PATH . '/views/...'
        $viewPath = MODULAR_APP_PATH . '/views/report/print_work_orders.php';
        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }
        require $viewPath;
        exit;
    }

    /**
     * چاپ گزارش خرابی‌ها
     */
    public function printFailures()
    {
        $this->requireAuth();

        $failureModel = new FailureMode();
        $failureModes = $failureModel->getAllWithDetails();

        $totalRPN = array_sum(array_column($failureModes, 'rpn'));
        $cumulative = 0;
        foreach ($failureModes as &$fm) {
            $cumulative += (int) $fm['rpn'];
            $fm['cumulative_percent'] = $totalRPN > 0 
                ? round(($cumulative / $totalRPN) * 100, 1) 
                : 0;
        }
        unset($fm);

        // ⚠️ مسیر صحیح
        $viewPath = MODULAR_APP_PATH . '/views/report/print_failures.php';
        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }
        require $viewPath;
        exit;
    }

    /**
     * چاپ گزارش KPIها
     */
    public function printKPI()
    {
        $this->requireAuth();

        $kpiModel = new KPI();
        $kpiValues = $kpiModel->calculateAll();
        $kpiDefinitions = $kpiModel->all();

        $woModel = new WorkOrder();
        $assetModel = new Asset();

        $baseStats = [
            'total_assets'      => $assetModel->count(),
            'total_work_orders' => $woModel->count(),
            'completed_wos'     => $woModel->count("status = 'completed'"),
            'open_wos'          => $woModel->count("status IN ('open', 'in_progress')"),
        ];

        // ⚠️ مسیر صحیح
        $viewPath = MODULAR_APP_PATH . '/views/report/print_kpi.php';
        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }
        require $viewPath;
        exit;
    }
}