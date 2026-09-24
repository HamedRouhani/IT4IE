<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-list-ol"></i> مقادیر شاخص‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — این ماه: <strong><?= hr_num($stats['this_month']) ?></strong>
                — محقق‌شده: <strong style="color: var(--hr-success);"><?= hr_num($stats['achieved']) ?></strong>
                — میانگین دستیابی: <strong><?= hr_num($stats['avg_achievement']) ?>%</strong>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('kpi') ?>" class="btn-hr-outline">
                <i class="fas fa-chart-bar"></i> شاخص‌ها
            </a>
            <a href="<?= hr_url('kpi', 'createValue') ?>" class="btn-hr-primary">
                <i class="fas fa-plus"></i> ثبت مقدار
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فیلترها -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فیلتر</h3></div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('kpi', 'values') ?>">
                <input type="hidden" name="controller" value="kpi">
                <input type="hidden" name="action" value="values">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="نام یا کد شاخص...">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">شاخص</label>
                        <select name="kpi_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_kpis as $k): ?>
                                <option value="<?= (int) $k['id'] ?>"
                                    <?= (int) $filters['kpi_id'] === (int) $k['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($k['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">دوره</label>
                        <input type="text" name="period" class="hr-form-control"
                               value="<?= hr_e($filters['period']) ?>"
                               placeholder="مثال: 1404-Q1">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">دسته</label>
                        <select name="category" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($categoryOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('kpi', 'values') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست مقادیر
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($values)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($values)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-list-ol"></i>
                <h4>هیچ مقداری ثبت نشده است</h4>
                <a href="<?= hr_url('kpi', 'createValue') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ثبت مقدار
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>شاخص</th>
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
                                <td>
                                    <div style="width: 32px; height: 32px; border-radius: 6px; background: <?= hr_e($v['color'] ?? '#1E40AF') ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                        <i class="<?= hr_e($v['icon'] ?? 'fas fa-chart-bar') ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= hr_url('kpi', 'show', ['id' => $v['kpi_id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($v['kpi_name']) ?>
                                    </a>
                                    <br><small class="hr-text-muted"><code><?= hr_e($v['kpi_code']) ?></code></small>
                                </td>
                                <td><code><?= hr_e($v['period']) ?></code></td>
                                <td><?= hr_date($v['period_date'], 'Y/m/d') ?></td>
                                <td>
                                    <strong><?= hr_num(number_format((float) $v['value'], 2)) ?></strong>
                                    <?php if (!empty($v['unit'])): ?>
                                        <small class="hr-text-muted"><?= hr_e($v['unit']) ?></small>
                                    <?php endif; ?>
                                </td>
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

<script src="/public/assets/js/software/hr.js"></script>