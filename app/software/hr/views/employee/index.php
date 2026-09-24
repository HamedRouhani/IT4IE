<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <!-- هدر -->
    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-tie"></i> کارکنان
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — شاغل: <strong><?= hr_num($stats['active']) ?></strong>
                — مرخصی: <strong><?= hr_num($stats['on_leave']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('employee', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-user-plus"></i> افزودن کارمند
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
            <form method="GET" action="<?= hr_url('employee') ?>">
                <input type="hidden" name="controller" value="employee">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               placeholder="نام، کد پرسنلی، کد ملی، موبایل..."
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
                        <label class="hr-form-label">وضعیت اشتغال</label>
                        <select name="employment_status" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="active" <?= $filters['employment_status'] === 'active' ? 'selected' : '' ?>>شاغل</option>
                            <option value="on_leave" <?= $filters['employment_status'] === 'on_leave' ? 'selected' : '' ?>>مرخصی</option>
                            <option value="suspended" <?= $filters['employment_status'] === 'suspended' ? 'selected' : '' ?>>تعلیق</option>
                            <option value="terminated" <?= $filters['employment_status'] === 'terminated' ? 'selected' : '' ?>>خاتمه همکاری</option>
                            <option value="retired" <?= $filters['employment_status'] === 'retired' ? 'selected' : '' ?>>بازنشسته</option>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جنسیت</label>
                        <select name="gender" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="male" <?= $filters['gender'] === 'male' ? 'selected' : '' ?>>مرد</option>
                            <option value="female" <?= $filters['gender'] === 'female' ? 'selected' : '' ?>>زن</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">مقطع تحصیلی</label>
                        <select name="education_level" class="hr-form-control">
                            <option value="">— همه —</option>
                            <option value="below_diploma" <?= $filters['education_level'] === 'below_diploma' ? 'selected' : '' ?>>زیر دیپلم</option>
                            <option value="diploma" <?= $filters['education_level'] === 'diploma' ? 'selected' : '' ?>>دیپلم</option>
                            <option value="associate" <?= $filters['education_level'] === 'associate' ? 'selected' : '' ?>>کاردانی</option>
                            <option value="bachelor" <?= $filters['education_level'] === 'bachelor' ? 'selected' : '' ?>>کارشناسی</option>
                            <option value="master" <?= $filters['education_level'] === 'master' ? 'selected' : '' ?>>کارشناسی ارشد</option>
                            <option value="phd" <?= $filters['education_level'] === 'phd' ? 'selected' : '' ?>>دکتری</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">فیلترهای ویژه</label>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="expiring_contract" value="1"
                                    <?= $filters['expiring_contract'] ? 'checked' : '' ?>>
                                <span style="font-size: 0.85rem;">قراردادهای نزدیک به انقضا (۳۰ روز)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="expiring_probation" value="1"
                                    <?= $filters['expiring_probation'] ? 'checked' : '' ?>>
                                <span style="font-size: 0.85rem;">پایان دوره آزمایشی (۱۴ روز)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary">
                        <i class="fas fa-search"></i> اعمال فیلتر
                    </button>
                    <a href="<?= hr_url('employee') ?>" class="btn-hr-outline">
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
                لیست کارکنان
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($employees)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($employees)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-user-tie"></i>
                <h4>هیچ کارمندی یافت نشد</h4>
                <p>اولین کارمند خود را اضافه کنید.</p>
                <a href="<?= hr_url('employee', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-user-plus"></i> افزودن کارمند
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کد پرسنلی</th>
                            <th>نام و نام خانوادگی</th>
                            <th>دپارتمان</th>
                            <th>پست</th>
                            <th>موبایل</th>
                            <th>تاریخ استخدام</th>
                            <th>وضعیت</th>
                            <th style="width: 150px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><code><?= hr_e($emp['employee_code']) ?></code></td>
                                <td>
                                    <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e(hr_full_name($emp)) ?>
                                    </a>
                                    <?php if (!empty($emp['national_id'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($emp['national_id']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e($emp['department_name'] ?? '—') ?></td>
                                <td><?= hr_e($emp['position_title'] ?? '—') ?></td>
                                <td><?= hr_e($emp['mobile'] ?? '—') ?></td>
                                <td><?= hr_date($emp['hire_date'], 'Y/m/d') ?></td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($emp['employment_status']) ?>">
                                        <?= hr_employment_status_label($emp['employment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('employee', 'edit', ['id' => $emp['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('employee', 'delete', ['id' => $emp['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="آیا از حذف کارمند '<?= hr_e(hr_full_name($emp)) ?>' اطمینان دارید؟ تمام اسناد و تاریخچه نیز حذف می‌شوند."
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