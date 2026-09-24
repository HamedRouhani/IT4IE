<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-graduation-cap"></i> دوره‌های آموزشی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — ثبت‌نام باز: <strong><?= hr_num($stats['open']) ?></strong>
                — در جریان: <strong><?= hr_num($stats['in_progress']) ?></strong>
                — تکمیل: <strong style="color: var(--hr-success);"><?= hr_num($stats['completed']) ?></strong>
                — کل ثبت‌نام‌ها: <strong><?= hr_num($stats['total_enrolled']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('training', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> دوره جدید
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فیلتر -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فیلتر</h3></div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('training') ?>">
                <input type="hidden" name="controller" value="training">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="عنوان، کد، ارائه‌دهنده...">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">نوع</label>
                        <select name="training_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['training_type'] === $k ? 'selected' : '' ?>>
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
                    <a href="<?= hr_url('training') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست دوره‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($trainings)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($trainings)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-graduation-cap"></i>
                <h4>هیچ دوره‌ای ثبت نشده است</h4>
                <a href="<?= hr_url('training', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد دوره
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>نوع</th>
                            <th>ارائه‌دهنده</th>
                            <th>بازه</th>
                            <th>شرکت‌کنندگان</th>
                            <th>هزینه</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainings as $t): ?>
                            <tr>
                                <td>
                                    <a href="<?= hr_url('training', 'show', ['id' => $t['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(hr_truncate($t['title'], 40)) ?>
                                    </a>
                                    <?php if (!empty($t['code'])): ?>
                                        <br><small class="hr-text-muted"><code><?= hr_e($t['code']) ?></code></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= hr_e($typeOptions[$t['training_type']] ?? $t['training_type']) ?></small></td>
                                <td><?= hr_e($t['provider'] ?? '—') ?></td>
                                <td>
                                    <small>
                                        <?= hr_date($t['start_date'], 'Y/m/d') ?>
                                        <?php if ($t['end_date'] && $t['end_date'] !== $t['start_date']): ?>
                                            - <?= hr_date($t['end_date'], 'Y/m/d') ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= hr_num($t['current_participants'] ?? 0) ?></strong>
                                    <?php if (!empty($t['max_participants'])): ?>
                                        / <?= hr_num($t['max_participants']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($t['cost_per_person'])): ?>
                                        <small><?= hr_money($t['cost_per_person']) ?></small>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($t['status']) ?>">
                                        <?= hr_e($statusOptions[$t['status']] ?? $t['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('training', 'show', ['id' => $t['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('enrollment', 'create', ['training_id' => $t['id']]) ?>"
                                           class="btn-hr-primary btn-sm" title="ثبت‌نام"><i class="fas fa-user-plus"></i></a>
                                        <a href="<?= hr_url('training', 'edit', ['id' => $t['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('training', 'delete', ['id' => $t['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این دوره؟"
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