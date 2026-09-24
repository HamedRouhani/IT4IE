<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-briefcase"></i> <?= hr_e($position['title']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?php if (!empty($position['code'])): ?>
                    کد: <code><?= hr_e($position['code']) ?></code> —
                <?php endif; ?>
                دپارتمان: <strong><?= hr_e($position['department_name'] ?? '—') ?></strong>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('position', 'edit', ['id' => $position['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('position') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-users"></i> ظرفیت</small>
            <h3><?= hr_num($position['headcount']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-user-check"></i> تکمیل‌شده</small>
            <h3><?= hr_num($position['filled_count']) ?></h3>
        </div>
        <div class="hr-stat-card <?= ($position['headcount'] - $position['filled_count']) > 0 ? 'critical' : '' ?>">
            <small><i class="fas fa-user-plus"></i> ظرفیت خالی</small>
            <h3><?= hr_num(max(0, $position['headcount'] - $position['filled_count'])) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پست</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 200px;">کد پست</th><td><code><?= hr_e($position['code'] ?? '—') ?></code></td></tr>
                            <tr><th>دپارتمان</th><td><?= hr_e($position['department_name'] ?? '—') ?></td></tr>
                            <tr><th>طبقه شغلی</th><td><?= hr_e($position['grade_code'] ?? '—') ?> — <?= hr_e($position['grade_name'] ?? '') ?></td></tr>
                            <tr><th>پست بالادستی</th><td><?= hr_e($position['parent_title'] ?? '—') ?></td></tr>
                            <tr><th>نوع پست</th><td><?= hr_e(ucfirst($position['position_type'])) ?></td></tr>
                            <tr><th>نوع استخدام</th><td><?= hr_employment_type_label($position['employment_type']) ?></td></tr>
                            <tr><th>حداقل تحصیلات</th><td><?= hr_e($position['min_education'] ?? '—') ?></td></tr>
                            <tr><th>حداقل سابقه</th><td><?= hr_num($position['min_experience_years']) ?> سال</td></tr>
                            <tr><th>مدیریتی</th><td><?= $position['is_managerial'] ? '✓ بله' : '—' ?></td></tr>
                            <tr><th>کلیدی</th><td><?= $position['is_critical'] ? '✓ بله' : '—' ?></td></tr>
                            <tr><th>وضعیت</th><td>
                                <span class="hr-status-badge <?= $position['is_active'] ? 'hr-status-active' : 'hr-status-inactive' ?>">
                                    <?= $position['is_active'] ? 'فعال' : 'غیرفعال' ?>
                                </span>
                            </td></tr>
                            <?php if (!empty($position['description'])): ?>
                            <tr><th>توضیحات</th><td><?= nl2br(hr_e($position['description'])) ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i>
                        کارکنان این پست
                    </h3>
                </div>
                <?php if (empty($employees)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-user-slash"></i>
                        <p>هیچ کارمندی در این پست نیست.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($employees as $emp): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>"
                                   style="color: var(--hr-primary-dark); font-weight: 600;">
                                    <?= hr_e(hr_full_name($emp)) ?>
                                </a>
                                <br><small class="hr-text-muted">
                                    <code><?= hr_e($emp['employee_code']) ?></code> —
                                    <?= hr_date($emp['hire_date'], 'Y/m/d') ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>