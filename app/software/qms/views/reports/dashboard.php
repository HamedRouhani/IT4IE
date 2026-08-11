<?php
$pageTitle = 'داشبورد مدیریت QMS';
$currentPage = 'reports';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-tachometer-alt" style="color: #6C3CE1;"></i>
            داشبورد مدیریت QMS
        </h1>
        <a href="?controller=reports" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-list"></i> لیست گزارش‌ها
        </a>
    </div>

    <!-- کارت‌های آمار اصلی -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #6C3CE1; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">برنامه‌های ممیزی</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #6C3CE1;"><?= $stats['total_plans'] ?? 0 ?></div>
                </div>
                <i class="fas fa-clipboard-list" style="font-size: 2rem; color: #6C3CE1; opacity: 0.3;"></i>
            </div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #10B981; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">جلسات تکمیل شده</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #10B981;"><?= $stats['completed_sessions'] ?? 0 ?></div>
                </div>
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #10B981; opacity: 0.3;"></i>
            </div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #EF4444; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">عدم انطباق باز</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #EF4444;"><?= $stats['open_ncs'] ?? 0 ?></div>
                </div>
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #EF4444; opacity: 0.3;"></i>
            </div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #F59E0B; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">CAR باز</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #F59E0B;"><?= $stats['open_cars'] ?? 0 ?></div>
                </div>
                <i class="fas fa-clipboard-check" style="font-size: 2rem; color: #F59E0B; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        
        <!-- نمودار عدم انطباق‌ها به تفکیک شدت -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                <i class="fas fa-chart-pie" style="color: #6C3CE1;"></i>
                عدم انطباق‌ها به تفکیک شدت
            </h3>
            <canvas id="ncsBySeverityChart" height="250"></canvas>
        </div>

        <!-- نمودار روند عدم انطباق‌ها -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                <i class="fas fa-chart-line" style="color: #6C3CE1;"></i>
                روند ۶ ماه اخیر
            </h3>
            <canvas id="ncsTrendChart" height="250"></canvas>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        
        <!-- نمودار وضعیت CARها -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                <i class="fas fa-tasks" style="color: #6C3CE1;"></i>
                وضعیت CARها
            </h3>
            <canvas id="carsByStatusChart" height="250"></canvas>
        </div>

        <!-- بندهای پرتکرار -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                <i class="fas fa-book" style="color: #6C3CE1;"></i>
                بندهای پرتکرار در عدم انطباق‌ها
            </h3>
            <?php if (empty($topClauses)): ?>
                <p style="text-align: center; color: #718096; padding: 30px;">داده‌ای موجود نیست</p>
            <?php else: ?>
                <?php foreach ($topClauses as $i => $clause): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #F0F0F0;">
                        <div>
                            <strong style="color: #2D3748;"><?= qms_e($clause['clause_number']) ?></strong>
                            <div style="font-size: 0.85rem; color: #718096;"><?= qms_e($clause['title_fa']) ?></div>
                        </div>
                        <span style="background: #6C3CE1; color: white; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                            <?= $clause['nc_count'] ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- آخرین فعالیت‌ها -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 20px 0; color: #2D3748;">
            <i class="fas fa-history" style="color: #6C3CE1;"></i>
            آخرین فعالیت‌ها
        </h3>
        <?php if (empty($recentActivities)): ?>
            <p style="text-align: center; color: #718096; padding: 30px;">فعالیتی ثبت نشده است</p>
        <?php else: ?>
            <?php foreach ($recentActivities as $activity): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #F0F0F0;">
                    <div>
                        <strong style="color: #2D3748;"><?= qms_e($activity['action']) ?></strong>
                        <div style="font-size: 0.85rem; color: #718096;">
                            توسط: <?= qms_e($activity['user_name'] ?? 'سیستم') ?>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #718096;">
                        <?= qms_e(date('Y/m/d H:i', strtotime($activity['created_at']))) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// نمودار عدم انطباق‌ها به تفکیک شدت
var ncsBySeverityData = <?= json_encode($ncsBySeverity) ?>;
var severityLabels = {minor: 'جزئی', major: 'عمده', critical: 'بحرانی'};
var severityColors = {minor: '#F59E0B', major: '#F97316', critical: '#EF4444'};

new Chart(document.getElementById('ncsBySeverityChart'), {
    type: 'doughnut',
    data: {
        labels: ncsBySeverityData.map(d => severityLabels[d.severity] || d.severity),
        datasets: [{
            data: ncsBySeverityData.map(d => d.count),
            backgroundColor: ncsBySeverityData.map(d => severityColors[d.severity] || '#6B7280')
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// نمودار روند
var ncsTrendData = <?= json_encode($ncsTrend) ?>;
new Chart(document.getElementById('ncsTrendChart'), {
    type: 'line',
    data: {
        labels: ncsTrendData.map(d => d.month),
        datasets: [{
            label: 'تعداد عدم انطباق',
            data: ncsTrendData.map(d => d.count),
            borderColor: '#6C3CE1',
            backgroundColor: 'rgba(108, 60, 225, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// نمودار وضعیت CARها
var carsByStatusData = <?= json_encode($carsByStatus) ?>;
var statusLabels = {
    draft: 'پیش‌نویس', submitted: 'ارسال شده', approved: 'تأیید شده',
    in_progress: 'در حال انجام', implemented: 'پیاده‌سازی شده',
    verified: 'تأیید شده', closed: 'بسته شده', rejected: 'رد شده'
};

new Chart(document.getElementById('carsByStatusChart'), {
    type: 'bar',
    data: {
        labels: carsByStatusData.map(d => statusLabels[d.status] || d.status),
        datasets: [{
            label: 'تعداد',
            data: carsByStatusData.map(d => d.count),
            backgroundColor: '#6C3CE1'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

<style>
@media (max-width: 992px) {
    .container-fluid > div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>