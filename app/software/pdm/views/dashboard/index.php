<?php
/**
 * PdM Analyzer - Dashboard View
 * مسیر: app/software/pdm/views/dashboard/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css">

<div class="software-content pdm-fade-in">

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- هدر صفحه -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-industry"></i> داشبورد نگهداری و تعمیرات
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                سیستم فعال: <strong><?= htmlspecialchars($activeSystem['company_name']) ?></strong>
                <?php if (!empty($activeSystem['industry'])): ?>
                    <span class="pdm-criticality-badge pdm-criticality-medium" style="margin-right: 0.5rem;">
                        <?= htmlspecialchars($activeSystem['industry']) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('asset', 'create') ?>" class="btn-pdm-primary">
                <i class="fas fa-plus"></i> افزودن دارایی
            </a>
            <a href="<?= pdm_url('location') ?>" class="btn-pdm-outline">
                <i class="fas fa-map-marked-alt"></i> مکان‌ها
            </a>
            <a href="<?= pdm_url('asset_category') ?>" class="btn-pdm-outline">
                <i class="fas fa-tags"></i> دسته‌بندی
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- کارت‌های آماری -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="stats-grid pdm-mb-4">

        <div class="pdm-stat-card">
            <small><i class="fas fa-cogs"></i> کل دارایی‌ها</small>
            <h3 class="pdm-count-up" data-target="<?= (int) $stats['total_assets'] ?>">0</h3>
        </div>

        <div class="pdm-stat-card success">
            <small><i class="fas fa-check-circle"></i> دارایی‌های فعال</small>
            <h3 class="pdm-count-up" data-target="<?= (int) $stats['active_assets'] ?>">0</h3>
        </div>

        <div class="pdm-stat-card critical">
            <small><i class="fas fa-exclamation-triangle"></i> دارایی‌های بحرانی</small>
            <h3 class="pdm-count-up" data-target="<?= (int) $stats['critical_assets'] ?>">0</h3>
        </div>

        <div class="pdm-stat-card warning">
            <small><i class="fas fa-clipboard-list"></i> دستورکارهای باز</small>
            <h3 class="pdm-count-up" data-target="<?= (int) $stats['open_work_orders'] ?>">0</h3>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- گرید اصلی -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="main-grid">

        <!-- ستون چپ: آخرین دستورکارها -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i>
                    آخرین دستورکارها
                </h3>
                <a href="?controller=workorder" class="btn-pdm-outline btn-sm">
                    مشاهده همه <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            <?php if (empty($recentWorkOrders)): ?>
                <div class="pdm-empty-state">
                    <i class="fas fa-clipboard"></i>
                    <h4>هنوز دستورکاری ثبت نشده است</h4>
                    <p>اولین دستورکار خود را ایجاد کنید.</p>
                    <a href="?controller=workorder&action=create" class="btn-pdm-primary">
                        <i class="fas fa-plus"></i> ایجاد دستورکار
                    </a>
                </div>
            <?php else: ?>
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>شماره</th>
                            <th>عنوان</th>
                            <th>دارایی</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentWorkOrders as $wo): ?>
                            <tr>
                                <td>
                                    <a href="?controller=workorder&action=show&id=<?= (int) $wo['id'] ?>">
                                        <?= htmlspecialchars($wo['wo_number'] ?? '-') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($wo['title'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($wo['asset_name'] ?? '-') ?></td>
                                <td>
                                    <span class="pdm-status-badge pdm-wo-status-<?= htmlspecialchars($wo['status']) ?>">
                                        <?= htmlspecialchars($wo['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($wo['created_at'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ستون راست: توزیع بحرانیت -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    توزیع بحرانیت دارایی‌ها
                </h3>
            </div>

            <?php
            $totalAssets = array_sum($criticalityDistribution);
            if ($totalAssets > 0):
            ?>
                <div class="pdm-mb-3">
                    <?php
                    $criticalityLabels = [
                        'critical' => ['label' => 'بحرانی', 'class' => 'critical'],
                        'high'     => ['label' => 'بالا',   'class' => 'warning'],
                        'medium'   => ['label' => 'متوسط',  'class' => 'medium'],
                        'low'      => ['label' => 'پایین',  'class' => 'low'],
                    ];
                    foreach ($criticalityLabels as $key => $info):
                        $count = $criticalityDistribution[$key] ?? 0;
                        $percent = $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0;
                    ?>
                        <div class="pdm-mb-3">
                            <div class="pdm-flex-between pdm-mb-2">
                                <span>
                                    <span class="pdm-criticality-badge pdm-criticality-<?= $info['class'] ?>">
                                        <?= $info['label'] ?>
                                    </span>
                                </span>
                                <span>
                                    <strong><?= $count ?></strong>
                                    <small class="pdm-text-muted">(<?= $percent ?>%)</small>
                                </span>
                            </div>
                            <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                <div style="width: <?= $percent ?>%; height: 100%; background: var(--pdm-primary); transition: width 0.5s;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="pdm-empty-state">
                    <i class="fas fa-chart-pie"></i>
                    <p>هنوز دارایی‌ای ثبت نشده است.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>