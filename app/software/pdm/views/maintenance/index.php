<?php
/**
 * PdM Analyzer - لیست برنامه‌های نگهداری
 * مسیر: app/software/pdm/views/maintenance/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-calendar-check"></i> برنامه‌های نگهداری پیشگیرانه
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong> برنامه
                — فعال: <strong><?= pdm_num($stats['active']) ?></strong>
                — سررسید شده: <strong style="color: var(--pdm-danger);"><?= pdm_num($stats['due']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('maintenance', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> افزودن برنامه
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- هشدار برنامه‌های سررسید شده -->
    <?php if (!empty($duePlans)): ?>
        <div class="pdm-alert warning pdm-mb-3">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong><?= pdm_num(count($duePlans)) ?> برنامه سررسید شده!</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-right: 1.25rem;">
                    <?php foreach (array_slice($duePlans, 0, 3) as $dp): ?>
                        <li>
                            <a href="<?= pdm_url('maintenance', 'edit', ['id' => $dp['id']]) ?>"
                               style="color: inherit;">
                                <?= pdm_e($dp['title']) ?>
                            </a>
                            — سررسید: <?= pdm_date($dp['next_execution'], 'Y/m/d') ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (count($duePlans) > 3): ?>
                        <li>و <?= pdm_num(count($duePlans) - 3) ?> مورد دیگر...</li>
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
                لیست برنامه‌ها
                <span class="pdm-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= pdm_num(count($plans)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($plans)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-calendar-check"></i>
                <h4>هنوز برنامه‌ای ثبت نشده است</h4>
                <p>اولین برنامه نگهداری پیشگیرانه خود را ایجاد کنید.</p>
                <a href="<?= pdm_url('maintenance', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> ایجاد اولین برنامه
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>دارایی</th>
                            <th>نوع</th>
                            <th>فرکانس</th>
                            <th>اجرای بعدی</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th style="width: 180px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <?php
                            $isDue = !empty($plan['next_execution'])
                                     && $plan['next_execution'] <= date('Y-m-d')
                                     && $plan['status'] === 'active';
                            ?>
                            <tr style="<?= $isDue ? 'background: #fef3c7;' : '' ?>">
                                <td>
                                    <strong><?= pdm_e($plan['title']) ?></strong>
                                    <?php if ($isDue): ?>
                                        <span class="pdm-criticality-badge pdm-criticality-critical"
                                              style="margin-right: 0.5rem; font-size: 0.65rem;">
                                            سررسید
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= pdm_url('asset', 'show', ['id' => $plan['asset_id']]) ?>"
                                       style="color: var(--pdm-primary-dark);">
                                        <?= pdm_e($plan['asset_name'] ?? '—') ?>
                                    </a>
                                    <?php if (!empty($plan['asset_code'])): ?>
                                        <br><small class="pdm-text-muted">
                                            <code><?= pdm_e($plan['asset_code']) ?></code>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= pdm_e($plan['maintenance_type_name'] ?? '—') ?></td>
                                <td>
                                    هر <?= pdm_num($plan['frequency_value']) ?>
                                    <?= pdm_e(\App\Software\Pdm\Models\MaintenancePlan::frequencyUnitLabel($plan['frequency_unit'])) ?>
                                </td>
                                <td><?= pdm_date($plan['next_execution'], 'Y/m/d') ?></td>
                                <td>
                                    <span class="pdm-criticality-badge <?= pdm_wo_priority_class($plan['priority']) ?>">
                                        <?= pdm_wo_priority_label($plan['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($plan['status'] === 'active'): ?>
                                        <span class="pdm-status-badge pdm-status-active">فعال</span>
                                    <?php else: ?>
                                        <span class="pdm-status-badge pdm-status-inactive">غیرفعال</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('workorder', 'create', ['plan_id' => $plan['id'], 'asset_id' => $plan['asset_id']]) ?>"
                                           class="btn-pdm-primary btn-sm"
                                           title="ایجاد دستورکار">
                                            <i class="fas fa-clipboard-list"></i>
                                        </a>
                                        <a href="<?= pdm_url('maintenance', 'edit', ['id' => $plan['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= pdm_url('maintenance', 'delete', ['id' => $plan['id']]) ?>"
                                           class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                           data-message="آیا از حذف برنامه '<?= pdm_e($plan['title']) ?>' اطمینان دارید؟"
                                           title="حذف"
                                           style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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