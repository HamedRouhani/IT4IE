<?php
/**
 * PdM Analyzer - شاخص‌های کلیدی (KPI) - نسخه پویا
 * مسیر: app/software/pdm/views/kpi/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-chart-line"></i> شاخص‌های کلیدی (KPI)
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num(count($allKPIs)) ?></strong> شاخص
                — فعال: <strong><?= pdm_num(count($activeKPIs)) ?></strong>
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('kpi', 'seedBuiltin') ?>" class="btn-pdm-outline">
                <i class="fas fa-sync"></i> بازنشانی شاخص‌های داخلی
            </a>
            <a href="<?= pdm_url('kpi', 'create') ?>" class="btn-pdm-primary">
                <i class="fas fa-plus"></i> افزودن شاخص
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های KPI فعال -->
    <?php if (!empty($activeKPIs)): ?>
        <div class="stats-grid pdm-mb-4">
            <?php foreach ($activeKPIs as $kpi): ?>
                <?php
                $value = $kpi['current_value'];
                $displayValue = '—';
                if ($value !== null) {
                    $displayValue = pdm_num(number_format($value, 2));
                    if (!empty($kpi['unit']) && $kpi['unit'] !== 'درصد' && $kpi['unit'] !== '%') {
                        $displayValue .= ' ' . pdm_e($kpi['unit']);
                    } elseif ($kpi['unit'] === 'درصد' || $kpi['unit'] === '%') {
                        $displayValue .= '٪';
                    }
                }
                ?>
                <div class="pdm-stat-card" style="border-right-color: <?= pdm_e($kpi['color'] ?? '#0F766E') ?>;">
                    <small>
                        <i class="<?= pdm_e($kpi['icon'] ?? 'fas fa-chart-line') ?>"
                           style="color: <?= pdm_e($kpi['color'] ?? '#0F766E') ?>;"></i>
                        <?= pdm_e($kpi['name']) ?>
                    </small>
                    <h3 style="color: <?= pdm_e($kpi['color'] ?? '#0F766E') ?>;">
                        <?= $displayValue ?>
                    </h3>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card pdm-mb-4">
            <div class="pdm-empty-state">
                <i class="fas fa-chart-line"></i>
                <h4>هیچ شاخص فعالی وجود ندارد</h4>
                <p>می‌توانید شاخص‌های داخلی را بازنشانی کنید یا شاخص جدید بسازید.</p>
                <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                    <a href="<?= pdm_url('kpi', 'seedBuiltin') ?>" class="btn-pdm-outline">
                        <i class="fas fa-sync"></i> بازنشانی شاخص‌های داخلی
                    </a>
                    <a href="<?= pdm_url('kpi', 'create') ?>" class="btn-pdm-primary">
                        <i class="fas fa-plus"></i> افزودن شاخص جدید
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- جدول مدیریت KPIها -->
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                مدیریت شاخص‌ها
            </h3>
        </div>

        <?php if (empty($allKPIs)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-chart-line"></i>
                <h4>هیچ شاخصی ثبت نشده است</h4>
                <p>شاخص‌های داخلی را بازنشانی کنید یا شاخص جدید بسازید.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ترتیب</th>
                            <th>کد</th>
                            <th>نام</th>
                            <th>فرمول</th>
                            <th>واحد</th>
                            <th>مقدار فعلی</th>
                            <th>نوع</th>
                            <th>وضعیت</th>
                            <th style="width: 200px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allKPIs as $kpi): ?>
                            <?php
                            $isBuiltin = (int) $kpi['is_builtin'] === 1;
                            $isActive = (int) $kpi['is_active'] === 1;
                            $value = $kpiValues[$kpi['code']] ?? null;
                            ?>
                            <tr style="<?= !$isActive ? 'opacity: 0.6;' : '' ?>">
                                <td><?= pdm_num($kpi['sort_order'] ?? 0) ?></td>
                                <td>
                                    <code style="color: <?= pdm_e($kpi['color'] ?? '#0F766E') ?>;">
                                        <?= pdm_e($kpi['code']) ?>
                                    </code>
                                </td>
                                <td>
                                    <i class="<?= pdm_e($kpi['icon'] ?? 'fas fa-chart-line') ?>"
                                       style="color: <?= pdm_e($kpi['color'] ?? '#0F766E') ?>;"></i>
                                    <strong><?= pdm_e($kpi['name']) ?></strong>
                                </td>
                                <td>
                                    <small class="pdm-text-muted">
                                        <?= pdm_e(pdm_truncate($kpi['formula'] ?? '—', 50)) ?>
                                    </small>
                                </td>
                                <td><?= pdm_e($kpi['unit'] ?? '—') ?></td>
                                <td>
                                    <?php if ($value !== null): ?>
                                        <strong style="color: var(--pdm-primary-dark);">
                                            <?= pdm_num(number_format($value, 2)) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="pdm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isBuiltin): ?>
                                        <span class="pdm-criticality-badge pdm-criticality-medium">
                                            داخلی
                                        </span>
                                    <?php else: ?>
                                        <span class="pdm-criticality-badge pdm-criticality-low">
                                            سفارشی
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="pdm-status-badge pdm-status-active">فعال</span>
                                    <?php else: ?>
                                        <span class="pdm-status-badge pdm-status-inactive">غیرفعال</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('kpi', 'edit', ['id' => $kpi['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= pdm_url('kpi', 'toggle', ['id' => $kpi['id']]) ?>"
                                           class="btn-pdm-outline btn-sm"
                                           title="<?= $isActive ? 'غیرفعال کردن' : 'فعال کردن' ?>">
                                            <i class="fas fa-<?= $isActive ? 'toggle-on' : 'toggle-off' ?>"></i>
                                        </a>
                                        <?php if (!$isBuiltin): ?>
                                            <a href="<?= pdm_url('kpi', 'delete', ['id' => $kpi['id']]) ?>"
                                               class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                               data-message="آیا از حذف شاخص '<?= pdm_e($kpi['name']) ?>' اطمینان دارید؟"
                                               title="حذف"
                                               style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn-pdm-outline btn-sm"
                                                  style="opacity: 0.3; cursor: not-allowed;"
                                                  title="شاخص داخلی قابل حذف نیست">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- آمار پایه -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-database"></i>
                آمار پایه سیستم
            </h3>
        </div>
        <div class="card-body">
            <table class="pdm-table">
                <tbody>
                    <tr>
                        <th style="width: 250px;">تعداد کل دارایی‌ها</th>
                        <td><?= pdm_num($baseStats['total_assets']) ?></td>
                    </tr>
                    <tr>
                        <th>تعداد کل دستورکارها</th>
                        <td><?= pdm_num($baseStats['total_work_orders']) ?></td>
                    </tr>
                    <tr>
                        <th>دستورکارهای تکمیل‌شده</th>
                        <td><?= pdm_num($baseStats['completed_wos']) ?></td>
                    </tr>
                    <tr>
                        <th>دستورکارهای باز</th>
                        <td><?= pdm_num($baseStats['open_wos']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>