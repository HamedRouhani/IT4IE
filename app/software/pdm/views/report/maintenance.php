<?php
/**
 * PdM Analyzer - گزارش برنامه‌های نگهداری
 * مسیر: app/software/pdm/views/report/maintenance.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-calendar-check"></i> گزارش برنامه‌های نگهداری
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num(count($plans)) ?></strong> برنامه
            </p>
        </div>
        <a href="<?= pdm_url('report') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <!-- برنامه‌های سررسید شده -->
    <?php if (!empty($duePlans)): ?>
    <div class="card pdm-mb-3" style="border-right: 4px solid var(--pdm-danger);">
        <div class="card-header" style="background: #fee2e2;">
            <h3 class="card-title" style="color: #991b1b;">
                <i class="fas fa-exclamation-triangle"></i>
                برنامه‌های سررسید شده (<?= pdm_num(count($duePlans)) ?>)
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="pdm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>دارایی</th>
                        <th>تاریخ سررسید</th>
                        <th>اولویت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($duePlans as $i => $p): ?>
                        <tr>
                            <td><?= pdm_num($i + 1) ?></td>
                            <td>
                                <a href="<?= pdm_url('maintenance', 'edit', ['id' => $p['id']]) ?>"
                                   style="color: var(--pdm-primary-dark);">
                                    <?= pdm_e($p['title']) ?>
                                </a>
                            </td>
                            <td><?= pdm_e($p['asset_name'] ?? '—') ?></td>
                            <td style="color: var(--pdm-danger); font-weight: 600;">
                                <?= pdm_date($p['next_execution'], 'Y/m/d') ?>
                            </td>
                            <td>
                                <span class="pdm-criticality-badge <?= pdm_wo_priority_class($p['priority']) ?>">
                                    <?= pdm_wo_priority_label($p['priority']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- برنامه‌های نزدیک به سررسید -->
    <?php if (!empty($upcomingPlans)): ?>
    <div class="card pdm-mb-3" style="border-right: 4px solid var(--pdm-warning);">
        <div class="card-header" style="background: #fef3c7;">
            <h3 class="card-title" style="color: #92400e;">
                <i class="fas fa-clock"></i>
                برنامه‌های نزدیک به سررسید (۳۰ روز آینده) — <?= pdm_num(count($upcomingPlans)) ?>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="pdm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>دارایی</th>
                        <th>تاریخ سررسید</th>
                        <th>اولویت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingPlans as $i => $p): ?>
                        <tr>
                            <td><?= pdm_num($i + 1) ?></td>
                            <td><?= pdm_e($p['title']) ?></td>
                            <td><?= pdm_e($p['asset_name'] ?? '—') ?></td>
                            <td style="font-weight: 600;"><?= pdm_date($p['next_execution'], 'Y/m/d') ?></td>
                            <td>
                                <span class="pdm-criticality-badge <?= pdm_wo_priority_class($p['priority']) ?>">
                                    <?= pdm_wo_priority_label($p['priority']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- همه برنامه‌ها -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                همه برنامه‌ها
            </h3>
        </div>

        <?php if (empty($plans)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-calendar-check"></i>
                <h4>هیچ برنامه‌ای ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان</th>
                            <th>دارایی</th>
                            <th>نوع</th>
                            <th>فرکانس</th>
                            <th>اجرای بعدی</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $i => $p): ?>
                            <tr>
                                <td><?= pdm_num($i + 1) ?></td>
                                <td><?= pdm_e($p['title']) ?></td>
                                <td><?= pdm_e($p['asset_name'] ?? '—') ?></td>
                                <td><?= pdm_e($p['maintenance_type_name'] ?? '—') ?></td>
                                <td>
                                    هر <?= pdm_num($p['frequency_value']) ?>
                                    <?= pdm_e(\App\Software\Pdm\Models\MaintenancePlan::frequencyUnitLabel($p['frequency_unit'])) ?>
                                </td>
                                <td><?= pdm_date($p['next_execution'], 'Y/m/d') ?></td>
                                <td>
                                    <span class="pdm-status-badge pdm-status-<?= $p['status'] === 'active' ? 'active' : 'inactive' ?>">
                                        <?= $p['status'] === 'active' ? 'فعال' : 'غیرفعال' ?>
                                    </span>
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