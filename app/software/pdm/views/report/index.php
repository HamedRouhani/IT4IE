<?php
/**
 * PdM Analyzer - داشبورد گزارش‌ها
 * مسیر: app/software/pdm/views/report/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-mb-4">
        <h2 style="color: var(--pdm-primary-dark); margin: 0;">
            <i class="fas fa-chart-bar"></i> داشبورد گزارش‌ها
        </h2>
        <p class="pdm-text-muted pdm-mt-2">
            گزارش‌های حرفه‌ای از سیستم نگهداری و تعمیرات
        </p>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های خلاصه -->
    <div class="stats-grid pdm-mb-4">
        <div class="pdm-stat-card">
            <small><i class="fas fa-cogs"></i> کل دارایی‌ها</small>
            <h3><?= pdm_num($overview['total_assets']) ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-check-circle"></i> دستورکارهای تکمیل‌شده</small>
            <h3><?= pdm_num($overview['completed_wos']) ?></h3>
        </div>
        <div class="pdm-stat-card warning">
            <small><i class="fas fa-hourglass-half"></i> دستورکارهای باز</small>
            <h3><?= pdm_num($overview['open_wos']) ?></h3>
        </div>
        <div class="pdm-stat-card critical">
            <small><i class="fas fa-exclamation-triangle"></i> کل خرابی‌ها</small>
            <h3><?= pdm_num($overview['total_failures']) ?></h3>
        </div>
    </div>

    <!-- KPIها -->
    <div class="stats-grid pdm-mb-4">
        <div class="pdm-stat-card">
            <small><i class="fas fa-clock"></i> MTTR</small>
            <h3 style="font-size: 1.3rem;">
                <?= $kpiValues['MTTR'] !== null ? pdm_num(number_format($kpiValues['MTTR'], 2)) . ' ساعت' : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-history"></i> MTBF</small>
            <h3 style="font-size: 1.3rem;">
                <?= $kpiValues['MTBF'] !== null ? pdm_num(number_format($kpiValues['MTBF'], 2)) . ' ساعت' : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card">
            <small><i class="fas fa-percentage"></i> دسترس‌پذیری</small>
            <h3 style="font-size: 1.3rem;">
                <?= $kpiValues['Availability'] !== null ? pdm_num($kpiValues['Availability']) . '٪' : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card critical">
            <small><i class="fas fa-shopping-cart"></i> قطعات کمبود</small>
            <h3><?= pdm_num($overview['low_stock_parts']) ?></h3>
        </div>
    </div>

    <!-- گزارش‌های در دسترس -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt"></i>
                گزارش‌های در دسترس
            </h3>
        </div>
        <div class="card-body">
            <div class="stats-grid">

                <a href="<?= pdm_url('report', 'workOrders') ?>" 
                   class="pdm-stat-card" style="text-decoration: none; display: block;">
                    <small><i class="fas fa-clipboard-list"></i> گزارش دستورکارها</small>
                    <p class="pdm-text-muted pdm-mt-2" style="font-size: 0.85rem;">
                        لیست کامل دستورکارها با فیلتر تاریخ، وضعیت و دارایی
                    </p>
                    <span class="btn-pdm-outline btn-sm pdm-mt-2">مشاهده گزارش</span>
                </a>

                <a href="<?= pdm_url('report', 'failures') ?>" 
                   class="pdm-stat-card critical" style="text-decoration: none; display: block;">
                    <small><i class="fas fa-exclamation-triangle"></i> گزارش خرابی‌ها</small>
                    <p class="pdm-text-muted pdm-mt-2" style="font-size: 0.85rem;">
                        تحلیل Pareto حالات خرابی بر اساس RPN
                    </p>
                    <span class="btn-pdm-outline btn-sm pdm-mt-2">مشاهده گزارش</span>
                </a>

                <a href="<?= pdm_url('report', 'kpi') ?>" 
                   class="pdm-stat-card success" style="text-decoration: none; display: block;">
                    <small><i class="fas fa-chart-line"></i> گزارش شاخص‌ها</small>
                    <p class="pdm-text-muted pdm-mt-2" style="font-size: 0.85rem;">
                        MTTR، MTBF، Availability و سایر KPIها
                    </p>
                    <span class="btn-pdm-outline btn-sm pdm-mt-2">مشاهده گزارش</span>
                </a>

                <a href="<?= pdm_url('report', 'inventory') ?>" 
                   class="pdm-stat-card warning" style="text-decoration: none; display: block;">
                    <small><i class="fas fa-puzzle-piece"></i> گزارش موجودی قطعات</small>
                    <p class="pdm-text-muted pdm-mt-2" style="font-size: 0.85rem;">
                        لیست قطعات یدکی با هشدار کمبود موجودی
                    </p>
                    <span class="btn-pdm-outline btn-sm pdm-mt-2">مشاهده گزارش</span>
                </a>

                <a href="<?= pdm_url('report', 'maintenance') ?>" 
                   class="pdm-stat-card" style="text-decoration: none; display: block;">
                    <small><i class="fas fa-calendar-check"></i> گزارش برنامه‌های نت</small>
                    <p class="pdm-text-muted pdm-mt-2" style="font-size: 0.85rem;">
                        برنامه‌های سررسید شده و نزدیک به سررسید
                    </p>
                    <span class="btn-pdm-outline btn-sm pdm-mt-2">مشاهده گزارش</span>
                </a>

            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>