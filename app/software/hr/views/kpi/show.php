<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <div style="display: inline-block; width: 48px; height: 48px; border-radius: 10px; background: <?= hr_e($kpi['color'] ?? '#1E40AF') ?>; color: white; text-align: center; line-height: 48px; margin-left: 0.5rem; vertical-align: middle;">
                    <i class="<?= hr_e($kpi['icon'] ?? 'fas fa-chart-bar') ?>"></i>
                </div>
                <?= hr_e($kpi['name']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کد: <code><?= hr_e($kpi['code']) ?></code> —
                <span class="hr-status-badge hr-status-info">
                    <?= hr_e($categoryOptions[$kpi['category']] ?? '') ?>
                </span>
                <?php if (!empty($kpi['is_builtin'])): ?>
                    <span class="hr-status-badge hr-status-warning">سیستمی</span>
                <?php endif; ?>
                <?php if (!empty($kpi['is_active'])): ?>
                    <span class="hr-status-badge hr-status-active">فعال</span>
                <?php else: ?>
                    <span class="hr-status-badge hr-status-inactive">غیرفعال</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('kpi', 'createValue', ['kpi_id' => $kpi['id']]) ?>" class="btn-hr-primary">
                <i class="fas fa-plus"></i> ثبت مقدار
            </a>
            <?php if (empty($kpi['is_builtin'])): ?>
                <a href="<?= hr_url('kpi', 'edit', ['id' => $kpi['id']]) ?>" class="btn-hr-outline">
                    <i class="fas fa-edit"></i> ویرایش
                </a>
            <?php endif; ?>
            <a href="<?= hr_url('kpi') ?>" class="btn-hr-outline">
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

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات شاخص</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">کد</th><td><code><?= hr_e($kpi['code']) ?></code></td></tr>
                            <tr><th>نام</th><td><?= hr_e($kpi['name']) ?></td></tr>
                            <tr><th>دسته‌بندی</th><td><?= hr_e($categoryOptions[$kpi['category']] ?? '') ?></td></tr>
                            <tr><th>نوع</th><td><?= hr_e($typeOptions[$kpi['kpi_type']] ?? '') ?></td></tr>
                            <tr><th>واحد</th><td><?= hr_e($kpi['unit'] ?? '—') ?></td></tr>
                            <tr><th>فرمول</th><td><?= hr_e($kpi['formula'] ?? '—') ?></td></tr>
                            <?php if (!empty($kpi['description'])): ?>
                                <tr><th>توضیحات</th><td><?= nl2br(hr_e($kpi['description'])) ?></td></tr>
                            <?php endif; ?>
                            <tr><th>منبع</th><td>
                                <?= !empty($kpi['is_builtin']) ? 'سیستمی (از پیش تعریف‌شده)' : 'اختصاصی' ?>
                            </td></tr>
                            <tr><th>تاریخ ایجاد</th><td><?= hr_date($kpi['created_at'] ?? null, 'Y/m/d H:i') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-ol"></i>
                        مقادیر ثبت‌شده (<?= hr_num(count($values)) ?>)
                    </h3>
                    <a href="<?= hr_url('kpi', 'createValue', ['kpi_id' => $kpi['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> مقدار جدید
                    </a>
                </div>

                <?php if (empty($values)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-list-ol"></i>
                        <p>هنوز مقداری ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>دوره</th>
                                    <th>تاریخ</th>
                                    <th>مقدار</th>
                                    <th>هدف</th>
                                    <th>دستیابی</th>
                                    <th style="width: 100px; text-align: center;">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($values as $v): ?>
                                    <?php $ach = \App\Software\Hr\Models\KPIValue::achievementPercent($v['value'], $v['target_value']); ?>
                                    <tr>
                                        <td><code><?= hr_e($v['period']) ?></code></td>
                                        <td><?= hr_date($v['period_date'], 'Y/m/d') ?></td>
                                        <td><strong><?= hr_num(number_format((float) $v['value'], 2)) ?></strong></td>
                                        <td>
                                            <?php if ($v['target_value'] !== null): ?>
                                                <?= hr_num(number_format((float) $v['target_value'], 2)) ?>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ach !== null): ?>
                                                <span class="hr-status-badge <?= \App\Software\Hr\Models\KPIValue::achievementClass($ach) ?>">
                                                    <?= hr_num($ach) ?>%
                                                </span>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                                <a href="<?= hr_url('kpi', 'editValue', ['id' => $v['id']]) ?>"
                                                   class="btn-hr-outline btn-sm"><i class="fas fa-edit"></i></a>
                                                <a href="<?= hr_url('kpi', 'deleteValue', ['id' => $v['id']]) ?>"
                                                   class="btn-hr-outline btn-sm hr-confirm-delete"
                                                   data-message="حذف این مقدار؟"
                                                   style="color: var(--hr-danger); border-color: var(--hr-danger);">
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

        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line"></i> خلاصه</h3></div>
                <div class="card-body">
                    <?php
                    $latest = !empty($values) ? $values[0] : null;
                    $latestAch = $latest ? \App\Software\Hr\Models\KPIValue::achievementPercent($latest['value'], $latest['target_value']) : null;
                    ?>
                    <?php if ($latest): ?>
                        <div style="text-align: center; padding: 1rem 0;">
                            <div style="font-size: 2.5rem; font-weight: bold; color: <?= hr_e($kpi['color'] ?? '#1E40AF') ?>;">
                                <?= hr_num(number_format((float) $latest['value'], 2)) ?>
                            </div>
                            <small class="hr-text-muted">آخرین مقدار (<?= hr_e($latest['period']) ?>)</small>
                        </div>
                        <?php if ($latestAch !== null): ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span>دستیابی به هدف</span>
                                <span class="hr-status-badge <?= \App\Software\Hr\Models\KPIValue::achievementClass($latestAch) ?>">
                                    <?= hr_num($latestAch) ?>%
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="hr-text-muted">هنوز مقداری ثبت نشده است.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>