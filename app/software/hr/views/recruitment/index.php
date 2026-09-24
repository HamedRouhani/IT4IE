<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-plus"></i> نیازهای استخدامی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — باز: <strong><?= hr_num($stats['open']) ?></strong>
                — ظرفیت خالی: <strong style="color: var(--hr-danger);"><?= hr_num($stats['total_open']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('recruitment', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> ایجاد نیاز استخدامی
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
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> فیلتر و جستجو</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('recruitment') ?>">
                <input type="hidden" name="controller" value="recruitment">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               placeholder="عنوان یا شماره درخواست..."
                               value="<?= hr_e($filters['q']) ?>">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">وضعیت</label>
                        <select name="status" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach (\App\Software\Hr\Models\Recruitment::getStatusOptions() as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">اولویت</label>
                        <select name="priority" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach (\App\Software\Hr\Models\Recruitment::getPriorityOptions() as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $filters['priority'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">دپارتمان</label>
                        <select name="department_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>"
                                    <?= (int) $filters['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary">
                        <i class="fas fa-search"></i> اعمال فیلتر
                    </button>
                    <a href="<?= hr_url('recruitment') ?>" class="btn-hr-outline">
                        <i class="fas fa-redo"></i> پاک کردن
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست نیازها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($recruitments)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($recruitments)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-user-plus"></i>
                <h4>هیچ نیاز استخدامی ثبت نشده است</h4>
                <p>اولین نیاز استخدامی خود را ایجاد کنید.</p>
                <a href="<?= hr_url('recruitment', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد نیاز
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>شماره</th>
                            <th>عنوان</th>
                            <th>پست</th>
                            <th>دپارتمان</th>
                            <th>ظرفیت</th>
                            <th>متقاضی</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th style="width: 180px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recruitments as $r): ?>
                            <tr>
                                <td><code><?= hr_e($r['request_number']) ?></code></td>
                                <td>
                                    <a href="<?= hr_url('recruitment', 'show', ['id' => $r['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(hr_truncate($r['title'], 40)) ?>
                                    </a>
                                </td>
                                <td><?= hr_e($r['position_title'] ?? '—') ?></td>
                                <td><?= hr_e($r['department_name'] ?? '—') ?></td>
                                <td>
                                    <strong><?= hr_num($r['filled_count']) ?></strong> /
                                    <?= hr_num($r['headcount']) ?>
                                    <?php if ((int) $r['open_count'] > 0): ?>
                                        <br><small style="color: var(--hr-danger);">
                                            <?= hr_num($r['open_count']) ?> خالی
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge hr-status-info">
                                        <?= hr_num($r['candidates_count']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_priority_class($r['priority']) ?>">
                                        <?= hr_priority_label($r['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($r['status']) ?>">
                                        <?= hr_e(\App\Software\Hr\Models\Recruitment::getStatusOptions()[$r['status']] ?? $r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('recruitment', 'show', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('candidate', 'create', ['recruitment_id' => $r['id']]) ?>"
                                           class="btn-hr-primary btn-sm" title="افزودن متقاضی">
                                            <i class="fas fa-user-plus"></i>
                                        </a>
                                        <a href="<?= hr_url('recruitment', 'edit', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('recruitment', 'delete', ['id' => $r['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف نیاز '<?= hr_e($r['request_number']) ?>'؟"
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