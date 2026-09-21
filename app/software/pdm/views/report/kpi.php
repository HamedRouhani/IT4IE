<?php
/**
 * PdM Analyzer - گزارش KPIها
 * مسیر: app/software/pdm/views/report/kpi.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-chart-line"></i> گزارش شاخص‌ها (KPI)
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                تحلیل شاخص‌های قابلیت اطمینان و نگهداری
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('report', 'printKPI') ?>" target="_blank" class="btn-pdm-primary">
                <i class="fas fa-print"></i> چاپ گزارش
            </a>
            <a href="<?= pdm_url('report') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <!-- کارت‌های KPI -->
    <div class="stats-grid pdm-mb-4">
        <div class="pdm-stat-card">
            <small><i class="fas fa-clock"></i> MTTR</small>
            <h3><?= $kpiValues['MTTR'] !== null ? pdm_num(number_format($kpiValues['MTTR'], 2)) . ' ساعت' : '—' ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-history"></i> MTBF</small>
            <h3><?= $kpiValues['MTBF'] !== null ? pdm_num(number_format($kpiValues['MTBF'], 2)) . ' ساعت' : '—' ?></h3>
        </div>
        <div class="pdm-stat-card">
            <small><i class="fas fa-percentage"></i> دسترس‌پذیری</small>
            <h3><?= $kpiValues['Availability'] !== null ? pdm_num($kpiValues['Availability']) . '٪' : '—' ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-shield-alt"></i> قابلیت اطمینان</small>
            <h3><?= $kpiValues['Reliability'] !== null ? pdm_num($kpiValues['Reliability']) . '٪' : '—' ?></h3>
        </div>
    </div>

    <!-- جدول KPIها -->
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                شاخص‌ها
            </h3>
        </div>
        <div class="card-body">
            <table class="pdm-table">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام</th>
                        <th>فرمول</th>
                        <th>واحد</th>
                        <th>مقدار فعلی</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kpiDefinitions as $kpi): ?>
                        <?php $val = $kpiValues[$kpi['code']] ?? null; ?>
                        <tr>
                            <td><code><?= pdm_e($kpi['code']) ?></code></td>
                            <td><?= pdm_e($kpi['name']) ?></td>
                            <td><small><?= pdm_e($kpi['formula'] ?? '—') ?></small></td>
                            <td><?= pdm_e($kpi['unit'] ?? '—') ?></td>
                            <td>
                                <?php if ($val !== null): ?>
                                    <strong style="color: var(--pdm-primary-dark);">
                                        <?= pdm_num(number_format($val, 2)) ?>
                                    </strong>
                                <?php else: ?>
                                    <span class="pdm-text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- نمودار روند MTTR -->
    <?php if (!empty($recentRepairs)): ?>
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-area"></i>
                روند MTTR (آخرین ۱۰ تعمیر)
            </h3>
        </div>
        <div class="card-body">
            <table class="pdm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>شماره دستورکار</th>
                        <th>عنوان</th>
                        <th>مدت تعمیر (ساعت)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRepairs as $i => $rep): ?>
                        <tr>
                            <td><?= pdm_num($i + 1) ?></td>
                            <td><code><?= pdm_e($rep['wo_number']) ?></code></td>
                            <td><?= pdm_e(pdm_truncate($rep['title'], 40)) ?></td>
                            <td>
                                <strong><?= pdm_num(number_format((float) $rep['repair_hours'], 2)) ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="/public/assets/js/software/pdm.js"></script>