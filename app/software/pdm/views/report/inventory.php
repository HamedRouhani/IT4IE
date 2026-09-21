<?php
/**
 * PdM Analyzer - گزارش موجودی قطعات
 * مسیر: app/software/pdm/views/report/inventory.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-puzzle-piece"></i> گزارش موجودی قطعات
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong> قطعه
                — کل موجودی: <strong><?= pdm_num($stats['total_quantity']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('report') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <!-- آمار -->
    <div class="stats-grid pdm-mb-3">
        <div class="pdm-stat-card">
            <small>کل قطعات</small>
            <h3><?= pdm_num($stats['total']) ?></h3>
        </div>
        <div class="pdm-stat-card warning">
            <small>کمبود موجودی</small>
            <h3><?= pdm_num($stats['low_stock']) ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small>کل موجودی (تعداد)</small>
            <h3><?= pdm_num($stats['total_quantity']) ?></h3>
        </div>
    </div>

    <!-- هشدار کمبود -->
    <?php if (!empty($lowStock)): ?>
    <div class="pdm-alert warning pdm-mb-3">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong><?= pdm_num(count($lowStock)) ?> قطعه زیر حد مجاز هستند.</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-right: 1.25rem;">
                <?php foreach (array_slice($lowStock, 0, 5) as $sp): ?>
                    <li>
                        <code><?= pdm_e($sp['code']) ?></code>
                        <?= pdm_e($sp['name']) ?>
                        — موجودی: <?= pdm_num($sp['stock_quantity']) ?> /
                        حد مجاز: <?= pdm_num($sp['minimum_stock']) ?>
                    </li>
                <?php endforeach; ?>
                <?php if (count($lowStock) > 5): ?>
                    <li>و <?= pdm_num(count($lowStock) - 5) ?> مورد دیگر...</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست قطعات
            </h3>
        </div>

        <?php if (empty($parts)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-puzzle-piece"></i>
                <h4>هیچ قطعه‌ای ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کد</th>
                            <th>نام</th>
                            <th>سازنده</th>
                            <th>موجودی</th>
                            <th>حد مجاز</th>
                            <th>واحد</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $i => $sp): ?>
                            <?php $isLow = (int) $sp['stock_quantity'] <= (int) $sp['minimum_stock']; ?>
                            <tr style="<?= $isLow ? 'background: #fef3c7;' : '' ?>">
                                <td><?= pdm_num($i + 1) ?></td>
                                <td><code><?= pdm_e($sp['code']) ?></code></td>
                                <td><?= pdm_e($sp['name']) ?></td>
                                <td><?= pdm_e($sp['manufacturer'] ?? '—') ?></td>
                                <td>
                                    <strong style="<?= $isLow ? 'color: var(--pdm-danger);' : '' ?>">
                                        <?= pdm_num($sp['stock_quantity']) ?>
                                    </strong>
                                </td>
                                <td><?= pdm_num($sp['minimum_stock']) ?></td>
                                <td><?= pdm_e($sp['unit'] ?? 'عدد') ?></td>
                                <td>
                                    <?php if ($isLow): ?>
                                        <span class="pdm-criticality-badge pdm-criticality-critical">کمبود</span>
                                    <?php else: ?>
                                        <span class="pdm-criticality-badge pdm-criticality-low">کافی</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>