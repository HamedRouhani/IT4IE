<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Software\Pdm\Models\System;
use App\Software\Pdm\Models\Asset;
use App\Software\Pdm\Models\WorkOrder;

/**
 * ============================================================
 * DashboardController - کنترلر داشبورد
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/DashboardController.php
 * ============================================================
 */
class DashboardController extends Controller
{
    /**
     * صفحه اصلی داشبورد
     */
    public function index()
    {
        // بررسی احراز هویت
        $this->requireAuth();
        
        $userId = (int) $_SESSION['user_id'];
        
        // دریافت یا ایجاد سیستم فعال
        $activeSystem = $this->getOrCreateSystem($userId);
        
        // آمار
        $assetModel     = new Asset();
        $workOrderModel = new WorkOrder();
        
        $stats = [
            'total_assets'      => $assetModel->count(),
            'active_assets'     => $assetModel->count("status = 'active'"),
            'critical_assets'   => $assetModel->count("criticality = 'critical'"),
            'total_work_orders' => $workOrderModel->count(),
            'open_work_orders'  => $workOrderModel->count("status = 'open'"),
            'in_progress_wos'   => $workOrderModel->count("status = 'in_progress'"),
            'completed_wos'     => $workOrderModel->count("status = 'completed'"),
        ];
        
        // توزیع بحرانیت دارایی‌ها
        $criticalityDistribution = $assetModel->getCriticalityDistribution();
        
        // توزیع وضعیت دستورکارها
        $workOrderStatusDistribution = $workOrderModel->getStatusDistribution();
        
        // آخرین دستورکارها
        $recentWorkOrders = $workOrderModel->getRecent(5);
        
        // MTTR و MTBF
        $mttr = $workOrderModel->calculateMTTR();
        $mtbf = $workOrderModel->calculateMTBF();
        
        // رندر
        $this->renderSoftware('dashboard/index', [
            'pageTitle'                    => 'داشبورد نگهداری و تعمیرات',
            'activeSystem'                 => $activeSystem,
            'stats'                        => $stats,
            'criticalityDistribution'      => $criticalityDistribution,
            'workOrderStatusDistribution'  => $workOrderStatusDistribution,
            'recentWorkOrders'             => $recentWorkOrders,
            'mttr'                         => $mttr,
            'mtbf'                         => $mtbf,
            'softwareName'                 => 'PdM Analyzer',
        ], 'pdm');
    }
    
    /**
     * دریافت سیستم کاربر. اگر ندارد، یکی می‌سازد.
     * رابطه One-to-One: هر کاربر فقط یک سیستم دارد.
     */
    private function getOrCreateSystem(int $userId): array
    {
        $systemModel = new System();
        $system = $systemModel->findByUser($userId);
        
        if ($system) {
            $_SESSION['pdm_active_system'] = (int) $system['id'];
            return $system;
        }
        
        // ایجاد سیستم پیش‌فرض
        $newId = $systemModel->createForUser($userId, [
            'company_name' => 'شرکت من',
            'industry'     => 'general',
            'description'  => 'سیستم نگهداری و تعمیرات',
            'status'       => 'active',
        ]);
        
        $_SESSION['pdm_active_system'] = $newId;
        
        return $systemModel->findById($newId);
    }
}