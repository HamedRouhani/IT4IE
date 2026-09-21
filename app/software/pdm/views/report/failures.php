<?php
/**
 * PdM Analyzer - گزارش خرابی‌ها (Pareto)
 * مسیر: app/software/pdm/views/report/failures.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-exclamation-triangle"></i> گزارش خرابی‌ها (Pareto)
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                تحلیل حالات خرابی بر اساس RPN (Severity × Occurrence × Detection)
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('report', 'printFailures') ?>" target="_blank" class="btn-pdm-primary">
                <i class="fas fa-print"></i> چاپ گزارش
            </a>
            <a href="<?= pdm_url('report') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <!-- آمار -->
    <div class="stats-grid pdm-mb-3">
        <div class="pdm-stat-card critical">
            <small>بحرانی (RPN ≥ 200)</small>
            <h3><?= pdm_num($summary['critical']) ?></h3>
        </div>
        <div class="pdm-stat-card warning">
            <small>بالا (100-199)</small>
            <h3><?= pdm_num($summary['high']) ?></h3>
        </div>
        <div class="pdm-stat-card">
            <small>متوسط (50-99)</small>
            <h3><?= pdm_num($summary['medium']) ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small>پایین (< 50)</small>
            <h3><?= pdm_num($summary['low']) ?></h3>
        </div>
    </div>

    <!-- جدول Pareto -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sort-amount-down"></i>
                تحلیل Pareto (مرتب‌شده بر اساس RPN)
            </h3>
        </div>

        <?php if (empty($failureModes)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-shield-alt"></i>
                <h4>هیچ حالت خرابی ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کد</th>
                            <th>نام</th>
                            <th>دارایی</th>
                            <th>S</th>
                            <th>O</th>
                            <th>D</th>
                            <th>RPN</th>
                            <th>درصد</th>
                            <th>تجمعی</th>
                            <th>سطح</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failureModes as $i => $fm): ?>
                            <?php
                            $rpn = (int) $fm['rpn'];
                            $percent = $summary['total_rpn'] > 0 
                                ? round(($rpn / $summary['total_rpn']) * 100, 1) 
                                : 0;
                            ?>
                            <tr>
                                <td><?= pdm_num($i + 1) ?></td>
                                <td><code><?= pdm_e($fm['code'] ?? '—') ?></code></td>
                                <td><?= pdm_e($fm['name']) ?></td>
                                <td><?= pdm_e($fm['asset_name'] ?? '—') ?></td>
                                <td><?= pdm_num($fm['severity']) ?></td>
                                <td><?= pdm_num($fm['occurrence']) ?></td>
                                <td><?= pdm_num($fm['detection']) ?></td>
                                <td><strong><?= pdm_num($rpn) ?></strong></td>
                                <td><?= pdm_num($percent) ?>٪</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span><?= pdm_num($fm['cumulative_percent']) ?>٪</span>
                                        <div style="flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; min-width: 60px;">
                                            <div style="width: <?= $fm['cumulative_percent'] ?>%; height: 100%; background: var(--pdm-primary);"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="pdm-criticality-badge <?= \App\Software\Pdm\Models\FailureMode::getRiskClass($rpn) ?>">
                                        <?= \App\Software\Pdm\Models\FailureMode::getRiskLabel($rpn) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- خلاصه ۲۰٪ بحرانی -->
            <div class="pdm-alert info pdm-mt-3">
                <i class="fas fa-chart-line"></i>
                <div>
                    <strong>قاعده Pareto (80/20):</strong>
                    <?php
                    $criticalCount = 0;
                    foreach ($failureModes as $fm) {
                        if ($fm['cumulative_percent'] <= 80) {
                            $criticalCount++;
                        } else break;
                    }
                    ?>
                    <strong><?= pdm_num($criticalCount) ?></strong> حالت خرابی اول،
                    باعث <strong>۸۰٪</strong> از کل RPN سیستم می‌شوند.
                    روی این موارد تمرکز کنید.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>