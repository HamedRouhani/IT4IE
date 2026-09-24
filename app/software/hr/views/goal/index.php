<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-bullseye"></i> اهداف سازمانی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong><?= hr_num($stats['active']) ?></strong>
                — تکمیل: <strong style="color: var(--hr-success);"><?= hr_num($stats['completed']) ?></strong>
                — عقب‌افتاده: <strong style="color: var(--hr-danger);"><?= hr_num($stats['overdue']) ?></strong>
                — میانگین پیشرفت: <strong><?= hr_num($stats['avg_progress']) ?>%</strong>
            </p>
        </div>
        <a href="<?= hr_url('goal', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> هدف جدید
        </a>
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
            <form method="GET" action="<?= hr_url('goal') ?>">
                <input type="hidden" name="controller" value="goal">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>" placeholder="عنوان یا توضیحات...">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label">کارمند</label>
                        <select name="employee_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $filters['employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label">نوع هدف</label>
                        <select name="goal_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['goal_type'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label">وضعیت</label>
                        <select name="status" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('goal') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست اهداف
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($goals)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($goals)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-bullseye"></i>
                <h4>هیچ هدفی ثبت نشده است</h4>
                <a href="<?= hr_url('goal', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد هدف
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>کارمند</th>
                            <th>دپارتمان</th>
                            <th>نوع</th>
                            <th>پیشرفت</th>
                            <th>مهلت</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($goals as $g): ?>
                            <tr>
                                <td>
                                    <a href="<?= hr_url('goal', 'show', ['id' => $g['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(hr_truncate($g['title'], 40)) ?>
                                    </a>
                                    <?php if (!empty($g['parent_title'])): ?>
                                        <br><small class="hr-text-muted">
                                            ↳ <?= hr_e(hr_truncate($g['parent_title'], 30)) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e(trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''))) ?: '—' ?></td>
                                <td><?= hr_e($g['department_name'] ?? '—') ?></td>
                                <td><small><?= hr_e($typeOptions[$g['goal_type']] ?? $g['goal_type']) ?></small></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;">
                                            <div style="width:<?= (int)$g['progress'] ?>%;background:var(--hr-primary);height:100%;"></div>
                                        </div>
                                        <small><strong><?= hr_num($g['progress']) ?>%</strong></small>
                                    </div>
                                </td>
                                <td>
                                    <?= hr_date($g['due_date'], 'Y/m/d') ?>
                                    <?php if ($g['due_date'] < date('Y-m-d') && $g['status'] === 'active'): ?>
                                        <br><small style="color: var(--hr-danger);">
                                            <i class="fas fa-exclamation-triangle"></i> عقب‌افتاده
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_priority_class($g['priority']) ?>">
                                        <?= hr_priority_label($g['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($g['status']) ?>">
                                        <?= hr_e($statusOptions[$g['status']] ?? $g['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('goal', 'show', ['id' => $g['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('goal', 'edit', ['id' => $g['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('goal', 'delete', ['id' => $g['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این هدف؟"
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