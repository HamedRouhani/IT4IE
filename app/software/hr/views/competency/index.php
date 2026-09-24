<?php
use App\Software\Hr\Models\Competency;
?>
<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-cubes"></i> شایستگی‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — هسته‌ای: <strong style="color: var(--hr-warning);"><?= hr_num($stats['core_count']) ?></strong>
                — غیرفعال: <strong><?= hr_num($stats['inactive']) ?></strong>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('competency', 'tree') ?>" class="btn-hr-outline">
                <i class="fas fa-sitemap"></i> نمای درختی
            </a>
            <a href="<?= hr_url('competency', 'create') ?>" class="btn-hr-primary">
                <i class="fas fa-plus"></i> شایستگی جدید
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
            <form method="GET" action="<?= hr_url('competency') ?>">
                <input type="hidden" name="controller" value="competency">
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
                        <select name="competency_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['competency_type'] === $k ? 'selected' : '' ?>>
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

                    <div class="hr-form-group">
                        <label class="hr-form-label">هسته‌ای</label>
                        <select name="is_core" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="1" <?= $filters['is_core'] === '1' ? 'selected' : '' ?>>بله</option>
                            <option value="0" <?= $filters['is_core'] === '0' ? 'selected' : '' ?>>خیر</option>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('competency') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست شایستگی‌ها
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($competencies)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($competencies)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-cubes"></i>
                <h4>هیچ شایستگی‌ای ثبت نشده است</h4>
                <p>اولین شایستگی را اضافه کنید.</p>
                <a href="<?= hr_url('competency', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> شایستگی جدید
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام</th>
                            <th>والد</th>
                            <th>دسته‌بندی</th>
                            <th>نوع</th>
                            <th>هسته‌ای</th>
                            <th>وضعیت</th>
                            <th style="width: 140px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competencies as $c): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($c['code'])): ?>
                                        <code><?= hr_e($c['code']) ?></code>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= hr_url('competency', 'show', ['id' => $c['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(hr_truncate($c['name'], 50)) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($c['parent_name'])): ?>
                                        <small><?= hr_e($c['parent_name']) ?></small>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= Competency::getCategoryClass($c['category']) ?>">
                                        <?= hr_e($categoryOptions[$c['category']] ?? $c['category']) ?>
                                    </span>
                                </td>
                                <td><small><?= hr_e($typeOptions[$c['competency_type']] ?? $c['competency_type']) ?></small></td>
                                <td>
                                    <?php if (!empty($c['is_core'])): ?>
                                        <span class="hr-status-badge hr-status-danger">
                                            <i class="fas fa-star"></i> هسته‌ای
                                        </span>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($c['status']) ?>">
                                        <?= hr_e($statusOptions[$c['status']] ?? $c['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('competency', 'show', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('competency', 'edit', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('competency', 'delete', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف شایستگی '<?= hr_e($c['name']) ?>'؟"
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