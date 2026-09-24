<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-bar"></i> شاخص‌های کلیدی عملکرد
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — سیستمی: <strong><?= hr_num($stats['builtin']) ?></strong>
                — اختصاصی: <strong><?= hr_num($stats['custom']) ?></strong>
                — مقادیر ثبت‌شده: <strong><?= hr_num($stats['values_count']) ?></strong>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('kpi', 'values') ?>" class="btn-hr-outline">
                <i class="fas fa-list-ol"></i> مقادیر ثبت‌شده
            </a>
            <a href="<?= hr_url('kpi', 'create') ?>" class="btn-hr-primary">
                <i class="fas fa-plus"></i> شاخص جدید
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
            <form method="GET" action="<?= hr_url('kpi') ?>">
                <input type="hidden" name="controller" value="kpi">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="نام، کد یا توضیحات...">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">دسته‌بندی</label>
                        <select name="category" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($categoryOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">نوع</label>
                        <select name="kpi_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['kpi_type'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">منبع</label>
                        <select name="is_builtin" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="1" <?= $filters['is_builtin'] === '1' ? 'selected' : '' ?>>سیستمی</option>
                            <option value="0" <?= $filters['is_builtin'] === '0' ? 'selected' : '' ?>>اختصاصی</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">وضعیت</label>
                        <select name="is_active" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>فعال</option>
                            <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>غیرفعال</option>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('kpi') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست شاخص‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($kpis)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($kpis)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-chart-bar"></i>
                <h4>هیچ شاخصی یافت نشد</h4>
                <a href="<?= hr_url('kpi', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> شاخص جدید
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>نام / کد</th>
                            <th>دسته</th>
                            <th>واحد</th>
                            <th>آخرین مقدار</th>
                            <th>هدف</th>
                            <th>دستیابی</th>
                            <th>منبع</th>
                            <th>وضعیت</th>
                            <th style="width: 140px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kpis as $k): ?>
                            <?php
                            $achievement = null;
                            if ($k['latest_value'] !== null && $k['latest_target'] !== null && (float) $k['latest_target'] > 0) {
                                $achievement = round(((float) $k['latest_value'] / (float) $k['latest_target']) * 100, 1);
                            }
                            ?>
                            <tr>
                                <td>
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: <?= hr_e($k['color'] ?? '#1E40AF') ?>; color: white; display: flex; align-items: center; justify-content: center;">
                                        <i class="<?= hr_e($k['icon'] ?? 'fas fa-chart-bar') ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= hr_url('kpi', 'show', ['id' => $k['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($k['name']) ?>
                                    </a>
                                    <br><small class="hr-text-muted"><code><?= hr_e($k['code']) ?></code></small>
                                </td>
                                <td>
                                    <span class="hr-status-badge hr-status-info" style="font-size: 0.7rem;">
                                        <?= hr_e($categoryOptions[$k['category']] ?? $k['category']) ?>
                                    </span>
                                </td>
                                <td><?= hr_e($k['unit'] ?? '—') ?></td>
                                <td>
                                    <?php if ($k['latest_value'] !== null): ?>
                                        <strong><?= hr_num(number_format((float) $k['latest_value'], 2)) ?></strong>
                                        <?php if (!empty($k['latest_date'])): ?>
                                            <br><small class="hr-text-muted"><?= hr_date($k['latest_date'], 'Y/m/d') ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($k['latest_target'] !== null): ?>
                                        <?= hr_num(number_format((float) $k['latest_target'], 2)) ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($achievement !== null): ?>
                                        <span class="hr-status-badge <?= \App\Software\Hr\Models\KPIValue::achievementClass($achievement) ?>">
                                            <?= hr_num($achievement) ?>%
                                        </span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['is_builtin'])): ?>
                                        <span class="hr-status-badge hr-status-warning" style="font-size: 0.7rem;">
                                            سیستمی
                                        </span>
                                    <?php else: ?>
                                        <span class="hr-status-badge hr-status-info" style="font-size: 0.7rem;">
                                            اختصاصی
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['is_active'])): ?>
                                        <span class="hr-status-badge hr-status-active">فعال</span>
                                    <?php else: ?>
                                        <span class="hr-status-badge hr-status-inactive">غیرفعال</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('kpi', 'show', ['id' => $k['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('kpi', 'createValue', ['kpi_id' => $k['id']]) ?>"
                                           class="btn-hr-primary btn-sm" title="ثبت مقدار"><i class="fas fa-plus"></i></a>
                                        <?php if (empty($k['is_builtin'])): ?>
                                            <a href="<?= hr_url('kpi', 'edit', ['id' => $k['id']]) ?>"
                                               class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                            <a href="<?= hr_url('kpi', 'delete', ['id' => $k['id']]) ?>"
                                               class="btn-hr-outline btn-sm hr-confirm-delete"
                                               data-message="حذف شاخص '<?= hr_e($k['name']) ?>'؟"
                                               style="color: var(--hr-danger); border-color: var(--hr-danger);">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

</div>

<script src="/public/assets/js/software/hr.js"></script>