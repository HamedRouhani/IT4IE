<?php
$pageTitle = $pageTitle ?? 'تحلیل پیشرفته پروژه';
$currentPage = $currentPage ?? 'dashboard';

// تابع فرمت ارز
function formatCurrency($amount, $currency = 'IRR') {
    if ($amount >= 1000000000) return number_format($amount / 1000000000, 1) . ' میلیارد';
    if ($amount >= 1000000) return number_format($amount / 1000000, 0) . ' میلیون';
    return number_format($amount, 0);
}

// رنگ شاخص‌ها
function getPerformanceColor($value) {
    if ($value >= 1.0) return '#10B981'; // سبز
    if ($value >= 0.9) return '#3B82F6'; // آبی
    if ($value >= 0.8) return '#F59E0B'; // نارنجی
    return '#EF4444'; // قرمز
}
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h2><i class="fas fa-chart-line"></i> تحلیل پیشرفته پروژه</h2>
        <p class="text-muted"><?= htmlspecialchars($project['name']) ?></p>
    </div>
    <div>
        <a href="?controller=dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

<!-- شاخص سلامت پروژه -->
<div class="card" style="background: linear-gradient(135deg, <?= $health['status_color'] ?> 0%, <?= $health['status_color'] ?>dd 100%); color: white; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px;">
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 1.1rem; opacity: 0.9;">شاخص سلامت پروژه</h3>
            <div style="font-size: 3rem; font-weight: 800;"><?= $health['health_index'] ?></div>
            <div style="font-size: 1rem; opacity: 0.95;">
                <?php
                $statusLabels = [
                    'excellent' => '🏆 عالی',
                    'good' => '✅ خوب',
                    'fair' => '⚠️ متوسط',
                    'at_risk' => '⚠️ در معرض خطر',
                    'critical' => '🚨 بحرانی'
                ];
                echo $statusLabels[$health['status']] ?? 'نامشخص';
                ?>
            </div>
        </div>
        <div style="text-align: left; flex: 1; padding-right: 30px;">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <div>
                    <div style="font-size: 0.85rem; opacity: 0.85;">زمان‌بندی</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $health['spi_score'] ?>%</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; opacity: 0.85;">هزینه</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $health['cpi_score'] ?>%</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; opacity: 0.85;">ریسک</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $health['risk_score'] ?>%</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; opacity: 0.85;">کیفیت</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?= $health['quality_score'] ?>%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- شاخص‌های کلیدی EVM -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 0.85rem; color: var(--gray);">ارزش برنامه‌ریزی‌شده (PV)</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #3B82F6;"><?= formatCurrency($evm['pv']) ?></div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 0.85rem; color: var(--gray);">ارزش کسب‌شده (EV)</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #10B981;"><?= formatCurrency($evm['ev']) ?></div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 0.85rem; color: var(--gray);">هزینه واقعی (AC)</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #F59E0B;"><?= formatCurrency($evm['ac']) ?></div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 0.85rem; color: var(--gray);">بودجه کل (BAC)</div>
        <div style="font-size: 1.5rem; font-weight: 700;"><?= formatCurrency($evm['bac']) ?></div>
    </div>
</div>

<!-- شاخص‌های عملکرد -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="card">
        <h4 style="margin: 0 0 15px 0; color: var(--soft-primary);">
            <i class="fas fa-tachometer-alt"></i> شاخص‌های عملکرد
        </h4>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>شاخص عملکرد زمان‌بندی (SPI)</span>
                    <strong style="color: <?= getPerformanceColor($evm['spi']) ?>;"><?= $evm['spi'] ?></strong>
                </div>
                <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                    <div style="width: <?= min(100, $evm['spi'] * 100) ?>%; background: <?= getPerformanceColor($evm['spi']) ?>; height: 100%;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>شاخص عملکرد هزینه (CPI)</span>
                    <strong style="color: <?= getPerformanceColor($evm['cpi']) ?>;"><?= $evm['cpi'] ?></strong>
                </div>
                <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                    <div style="width: <?= min(100, $evm['cpi'] * 100) ?>%; background: <?= getPerformanceColor($evm['cpi']) ?>; height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h4 style="margin: 0 0 15px 0; color: var(--soft-primary);">
            <i class="fas fa-bullseye"></i> پیش‌بینی‌ها
        </h4>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; justify-content: space-between;">
                <span>تخمین هزینه در تکمیل (EAC)</span>
                <strong><?= formatCurrency($evm['eac']) ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>هزینه باقی‌مانده (ETC)</span>
                <strong><?= formatCurrency($evm['etc']) ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>انحراف در تکمیل (VAC)</span>
                <strong style="color: <?= $evm['vac'] >= 0 ? '#10B981' : '#EF4444' ?>;">
                    <?= formatCurrency(abs($evm['vac'])) ?> <?= $evm['vac'] >= 0 ? '(صرفه‌جویی)' : '(مازاد)' ?>
                </strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>تاریخ تکمیل پیش‌بینی‌شده</span>
                <strong><?= $evm['estimated_completion_date'] ?? 'نامشخص' ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- نمودارها -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- نمودار S-Curve -->
    <div class="card">
        <h4 style="margin: 0 0 15px 0; color: var(--soft-primary);">
            <i class="fas fa-chart-area"></i> منحنی S (S-Curve)
        </h4>
        <canvas id="sCurveChart" height="250"></canvas>
    </div>

    <!-- نمودار توزیع حوزه‌های دانشی -->
    <div class="card">
        <h4 style="margin: 0 0 15px 0; color: var(--soft-primary);">
            <i class="fas fa-chart-pie"></i> توزیع وظایف بر اساس حوزه دانشی
        </h4>
        <canvas id="kaChart" height="250"></canvas>
    </div>
</div>

<!-- وضعیت وظایف -->
<div class="card">
    <h4 style="margin: 0 0 15px 0; color: var(--soft-primary);">
        <i class="fas fa-tasks"></i> وضعیت وظایف
    </h4>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        <div style="text-align: center; padding: 15px; background: #f3f4f6; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #6B7280;"><?= $evm['total_tasks'] ?></div>
            <div style="color: var(--gray);">کل وظایف</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #d1fae5; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #10B981;"><?= $evm['completed_tasks'] ?></div>
            <div style="color: #065F46;">تکمیل‌شده</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #fef3c7; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #F59E0B;"><?= $evm['in_progress_tasks'] ?></div>
            <div style="color: #92400E;">در حال انجام</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #dbeafe; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #3B82F6;"><?= $evm['overall_progress'] ?>%</div>
            <div style="color: #1E40AF;">پیشرفت کلی</div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// منحنی S-Curve
const sCurveCtx = document.getElementById('sCurveChart').getContext('2d');
new Chart(sCurveCtx, {
    type: 'line',
    data: {
        labels: ['PV (برنامه‌ریزی)', 'EV (کسب‌شده)', 'AC (واقعی)'],
        datasets: [{
            label: 'ارزش (میلیون)',
            data: [
                <?= round($evm['pv'] / 1000000, 1) ?>,
                <?= round($evm['ev'] / 1000000, 1) ?>,
                <?= round($evm['ac'] / 1000000, 1) ?>
            ],
            backgroundColor: ['rgba(59, 130, 246, 0.2)', 'rgba(16, 185, 129, 0.2)', 'rgba(245, 158, 11, 0.2)'],
            borderColor: ['#3B82F6', '#10B981', '#F59E0B'],
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// نمودار حوزه‌های دانشی
const kaCtx = document.getElementById('kaChart').getContext('2d');
new Chart(kaCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php 
            $labels = array_map(fn($ka) => "'" . addslashes($ka['knowledge_area']) . "'", $kaDistribution);
            echo implode(',', $labels);
        ?>],
        datasets: [{
            data: [<?php 
                $data = array_map(fn($ka) => $ka['total'], $kaDistribution);
                echo implode(',', $data);
            ?>],
            backgroundColor: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                position: 'right',
                labels: { font: { size: 11 } }
            }
        }
    }
});
</script>