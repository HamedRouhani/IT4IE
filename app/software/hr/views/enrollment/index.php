<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-check"></i> ثبت‌نام‌های آموزشی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — در انتظار: <strong style="color: var(--hr-warning);"><?= hr_num($stats['pending']) ?></strong>
                — تأیید شده: <strong><?= hr_num($stats['approved']) ?></strong>
                — تکمیل: <strong style="color: var(--hr-success);"><?= hr_num($stats['completed']) ?></strong>
                — میانگین امتیاز: <strong><?= hr_num($stats['avg_score']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('enrollment', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> ثبت‌نام جدید
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
            <form method="GET" action="<?= hr_url('enrollment') ?>">
                <input type="hidden" name="controller" value="enrollment">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="نام کارمند یا عنوان دوره...">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">دوره</label>
                        <select name="training_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_trainings as $t): ?>
                                <option value="<?= (int) $t['id'] ?>"
                                    <?= (int) $filters['training_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($t['title']) ?>
                                    <?php if (!empty($t['code'])): ?>
                                        (<?= hr_e($t['code']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                    <a href="<?= hr_url('enrollment') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست ثبت‌نام‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($enrollments)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($enrollments)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-user-check"></i>
                <h4>هیچ ثبت‌نامی وجود ندارد</h4>
                <p>اولین ثبت‌نام را اضافه کنید.</p>
                <a href="<?= hr_url('enrollment', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ثبت‌نام جدید
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کارمند</th>
                            <th>دوره</th>
                            <th>تاریخ ثبت‌نام</th>
                            <th>حضور</th>
                            <th>امتیاز</th>
                            <th>گواهی</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $en): ?>
                            <tr>
                                <td><code>#<?= hr_num($en['id']) ?></code></td>
                                <td>
                                    <a href="<?= hr_url('employee', 'show', ['id' => $en['employee_id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(trim(($en['first_name'] ?? '') . ' ' . ($en['last_name'] ?? ''))) ?>
                                    </a>
                                    <?php if (!empty($en['employee_code'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($en['employee_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($en['training_id'])): ?>
                                        <a href="<?= hr_url('training', 'show', ['id' => $en['training_id']]) ?>">
                                            <?= hr_e(hr_truncate($en['training_title'] ?? '', 40)) ?>
                                        </a>
                                        <?php if (!empty($en['training_code'])): ?>
                                            <br><small class="hr-text-muted"><?= hr_e($en['training_code']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_date($en['enrolled_date'], 'Y/m/d') ?></td>
                                <td>
                                    <?php if ($en['attendance_percent'] !== null): ?>
                                        <strong><?= hr_num($en['attendance_percent']) ?>%</strong>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($en['score'] !== null): ?>
                                        <strong><?= hr_num($en['score']) ?></strong>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($en['certificate_issued'])): ?>
                                        <span class="hr-status-badge hr-status-active">
                                            <i class="fas fa-certificate"></i> صادر شده
                                        </span>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($en['status']) ?>">
                                        <?= hr_e($statusOptions[$en['status']] ?? $en['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('enrollment', 'show', ['id' => $en['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('enrollment', 'edit', ['id' => $en['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('enrollment', 'delete', ['id' => $en['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این ثبت‌نام؟"
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