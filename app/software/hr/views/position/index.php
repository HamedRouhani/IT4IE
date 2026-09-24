<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <!-- هدر -->
    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-briefcase"></i> پست‌های سازمانی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong><?= hr_num($stats['active']) ?></strong>
                — پست‌های خالی: <strong style="color: var(--hr-danger);"><?= hr_num($stats['total_open']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('position', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> افزودن پست
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
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> فیلتر و جستجو</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('position') ?>">
                <input type="hidden" name="controller" value="position">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               placeholder="عنوان یا کد پست..."
                               value="<?= hr_e($filters['q']) ?>">
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

                    <div class="hr-form-group">
                        <label class="hr-form-label">طبقه شغلی</label>
                        <select name="grade_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_grades as $g): ?>
                                <option value="<?= (int) $g['id'] ?>"
                                    <?= (int) $filters['grade_id'] === (int) $g['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($g['code']) ?> — <?= hr_e($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                <?= $filters['is_active'] ? 'checked' : '' ?>>
                            <span>فقط پست‌های فعال</span>
                        </label>
                    </div>

                    <div class="hr-form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="has_open" value="1"
                                <?= $filters['has_open'] ? 'checked' : '' ?>>
                            <span>فقط پست‌های دارای ظرفیت خالی</span>
                        </label>
                    </div>

                    <div class="hr-form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_critical" value="1"
                                <?= $filters['is_critical'] ? 'checked' : '' ?>>
                            <span>فقط پست‌های کلیدی</span>
                        </label>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary">
                        <i class="fas fa-search"></i> اعمال فیلتر
                    </button>
                    <a href="<?= hr_url('position') ?>" class="btn-hr-outline">
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
                لیست پست‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($positions)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($positions)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-briefcase"></i>
                <h4>هیچ پستی یافت نشد</h4>
                <p>اولین پست سازمانی خود را ایجاد کنید.</p>
                <a href="<?= hr_url('position', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> افزودن پست
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>عنوان پست</th>
                            <th>دپارتمان</th>
                            <th>طبقه</th>
                            <th>ظرفیت</th>
                            <th>وضعیت</th>
                            <th style="width: 150px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions as $p): ?>
                            <tr>
                                <td><code><?= hr_e($p['code'] ?? '—') ?></code></td>
                                <td>
                                    <a href="<?= hr_url('position', 'show', ['id' => $p['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($p['title']) ?>
                                    </a>
                                    <?php if ($p['is_managerial']): ?>
                                        <span class="hr-status-badge hr-status-info"
                                              style="margin-right: 0.5rem; font-size: 0.65rem;">
                                            مدیریتی
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($p['is_critical']): ?>
                                        <span class="hr-status-badge hr-status-danger"
                                              style="margin-right: 0.5rem; font-size: 0.65rem;">
                                            کلیدی
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e($p['department_name'] ?? '—') ?></td>
                                <td><?= hr_e($p['grade_code'] ?? '—') ?></td>
                                <td>
                                    <strong><?= hr_num($p['filled_count']) ?></strong> /
                                    <?= hr_num($p['headcount']) ?>
                                    <?php if ((int) $p['open_count'] > 0): ?>
                                        <br><small style="color: var(--hr-danger);">
                                            <?= hr_num($p['open_count']) ?> خالی
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= $p['is_active'] ? 'hr-status-active' : 'hr-status-inactive' ?>">
                                        <?= $p['is_active'] ? 'فعال' : 'غیرفعال' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('position', 'show', ['id' => $p['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('position', 'edit', ['id' => $p['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('position', 'delete', ['id' => $p['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="آیا از حذف پست '<?= hr_e($p['title']) ?>' اطمینان دارید؟"
                                           title="حذف"
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