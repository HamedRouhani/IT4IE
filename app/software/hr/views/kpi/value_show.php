<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-bar"></i> جزئیات مقدار
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?= hr_e($value['kpi_name']) ?>
                — دوره: <code><?= hr_e($value['period']) ?></code>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('kpi', 'editValue', ['id' => $value['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('kpi', 'values') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card <?= !empty($value['color']) ? '' : '' ?>" style="border-top: 3px solid <?= hr_e($value['color'] ?? '#1E40AF') ?>;">
            <small>مقدار</small>
            <h3><?= hr_num(number_format((float) $value['value'], 2)) ?></h3>
        </div>
        <?php if ($value['target_value'] !== null): ?>
            <div class="hr-stat-card warning">
                <small>هدف</small>
                <h3><?= hr_num(number_format((float) $value['target_value'], 2)) ?></h3>
            </div>
        <?php endif; ?>
        <?php $ach = \App\Software\Hr\Models\KPIValue::achievementPercent($value['value'], $value['target_value']); ?>
        <?php if ($ach !== null): ?>
            <div class="hr-stat-card <?= $ach >= 100 ? 'success' : ($ach >= 80 ? '' : 'critical') ?>">
                <small>دستیابی</small>
                <h3><?= hr_num($ach) ?>%</h3>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات مقدار</h3></div>
        <div class="card-body">
            <table class="hr-table">
                <tbody>
                    <tr><th style="width: 180px;">شاخص</th><td>
                        <a href="<?= hr_url('kpi', 'show', ['id' => $value['kpi_id']]) ?>">
                            <?= hr_e($value['kpi_name']) ?>
                        </a>
                        <code class="hr-text-muted">(<?= hr_e($value['kpi_code']) ?>)</code>
                    </td></tr>
                    <tr><th>دسته</th><td><?= hr_e(\App\Software\Hr\Models\KPI::getCategoryOptions()[$value['category']] ?? $value['category']) ?></td></tr>
                    <tr><th>واحد</th><td><?= hr_e($value['unit'] ?? '—') ?></td></tr>
                    <tr><th>دوره</th><td><code><?= hr_e($value['period']) ?></code></td></tr>
                    <tr><th>تاریخ دوره</th><td><?= hr_date($value['period_date'], 'Y/m/d') ?></td></tr>
                    <tr><th>مقدار</th><td><strong><?= hr_num(number_format((float) $value['value'], 2)) ?></strong></td></tr>
                    <tr><th>هدف</th><td>
                        <?php if ($value['target_value'] !== null): ?>
                            <?= hr_num(number_format((float) $value['target_value'], 2)) ?>
                        <?php else: ?>—<?php endif; ?>
                    </td></tr>
                    <?php if (!empty($value['notes'])): ?>
                        <tr><th>یادداشت</th><td><?= nl2br(hr_e($value['notes'])) ?></td></tr>
                    <?php endif; ?>
                    <tr><th>تاریخ ثبت</th><td><?= hr_date($value['created_at'] ?? null, 'Y/m/d H:i') ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>