<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-line"></i> ارزیابی عملکرد
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — تکمیل: <strong style="color: var(--hr-success);"><?= hr_num($stats['completed']) ?></strong>
                — در جریان: <strong><?= hr_num($stats['in_progress']) ?></strong>
                — میانگین امتیاز: <strong><?= hr_num($stats['avg_score']) ?> / ۵</strong>
            </p>
        </div>
        <a href="<?= hr_url('performance', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> ارزیابی جدید
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
            <form method="GET" action="<?= hr_url('performance') ?>">
                <input type="hidden" name="controller" value="performance">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>" placeholder="نام کارمند یا دوره...">
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
                        <label class="hr-form-label">نوع ارزیابی</label>
                        <select name="review_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['review_type'] === $k ? 'selected' : '' ?>>
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
                    <a href="<?= hr_url('performance') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست ارزیابی‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($reviews)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-chart-line"></i>
                <h4>هیچ ارزیابی‌ای ثبت نشده است</h4>
                <a href="<?= hr_url('performance', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد ارزیابی
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کارمند</th>
                            <th>دوره</th>
                            <th>نوع</th>
                            <th>بازه</th>
                            <th>امتیاز</th>
                            <th>رتبه</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td>
                                    <a href="<?= hr_url('performance', 'show', ['id' => $r['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?>
                                    </a>
                                    <?php if (!empty($r['employee_code'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($r['employee_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= hr_e($r['review_period']) ?></code></td>
                                <td><small><?= hr_e($typeOptions[$r['review_type']] ?? '') ?></small></td>
                                <td><small><?= hr_date($r['period_start'], 'Y/m/d') ?> - <?= hr_date($r['period_end'], 'Y/m/d') ?></small></td>
                                <td>
                                    <?php if ($r['overall_score'] !== null): ?>
                                        <strong><?= hr_num($r['overall_score']) ?></strong> / ۵
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['rating'])): ?>
                                        <span class="hr-status-badge <?= hr_rating_class($r['rating']) ?>">
                                            <?= hr_e($ratingOptions[$r['rating']] ?? '') ?>
                                        </span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($r['status']) ?>">
                                        <?= hr_e($statusOptions[$r['status']] ?? $r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('performance', 'show', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('performance', 'edit', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('performance', 'delete', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این ارزیابی؟"
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