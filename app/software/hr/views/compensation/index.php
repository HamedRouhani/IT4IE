<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-money-bill-wave"></i> جبران خدمات
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع احکام: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — کارمندان: <strong><?= hr_num($stats['total_employees']) ?></strong>
                — میانگین حقوق: <strong><?= hr_money($stats['avg_salary']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('compensation', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> حکم حقوقی جدید
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
            <form method="GET" action="<?= hr_url('compensation') ?>">
                <input type="hidden" name="controller" value="compensation">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="نام یا کد کارمند...">
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
                        <label class="hr-form-label">واحد پول</label>
                        <select name="currency" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($currencyOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['currency'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">فقط فعال</label>
                        <select name="active_only" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="1" <?= !empty($filters['active_only']) ? 'selected' : '' ?>>بله</option>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('compensation') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> لیست احکام
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($compensations)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($compensations)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-money-bill-wave"></i>
                <h4>هیچ حکم حقوقی ثبت نشده است</h4>
                <a href="<?= hr_url('compensation', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد حکم
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کارمند</th>
                            <th>دپارتمان</th>
                            <th>پست</th>
                            <th>از تاریخ</th>
                            <th>تا تاریخ</th>
                            <th>حقوق کل</th>
                            <th>واحد</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compensations as $c): ?>
                            <tr>
                                <td>
                                    <a href="<?= hr_url('compensation', 'show', ['id' => $c['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?>
                                    </a>
                                    <?php if (!empty($c['employee_code'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($c['employee_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e($c['department_name'] ?? '—') ?></td>
                                <td><?= hr_e($c['position_title'] ?? '—') ?></td>
                                <td><?= hr_date($c['effective_date'], 'Y/m/d') ?></td>
                                <td>
                                    <?php if (!empty($c['end_date'])): ?>
                                        <?= hr_date($c['end_date'], 'Y/m/d') ?>
                                    <?php else: ?>
                                        <span class="hr-status-badge hr-status-active">فعلی</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= hr_money($c['total_fixed']) ?></strong></td>
                                <td><small><?= hr_e($currencyOptions[$c['currency']] ?? $c['currency']) ?></small></td>
                                <td>
                                    <?php if (empty($c['end_date']) || $c['end_date'] >= date('Y-m-d')): ?>
                                        <span class="hr-status-badge hr-status-active">فعال</span>
                                    <?php else: ?>
                                        <span class="hr-status-badge hr-status-inactive">منقضی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('compensation', 'show', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده"><i class="fas fa-eye"></i></a>
                                        <a href="<?= hr_url('compensation', 'edit', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش"><i class="fas fa-edit"></i></a>
                                        <a href="<?= hr_url('compensation', 'delete', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این حکم حقوقی؟"
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