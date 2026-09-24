<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-users"></i> گزارش کارکنان
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — شاغل: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — مرخصی: <strong><?= hr_num($stats['on_leave']) ?></strong>
                — خاتمه: <strong><?= hr_num($stats['terminated']) ?></strong>
            </p>
        </div>
        <div class="hr-report-actions">
            <button onclick="window.print()" class="btn-hr-outline">
                <i class="fas fa-print"></i> چاپ
            </button>
            <a href="<?= hr_url('report') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <!-- فیلترها -->
    <div class="card hr-mb-3 no-print">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فیلتر</h3></div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('report', 'employees') ?>">
                <input type="hidden" name="controller" value="report">
                <input type="hidden" name="action" value="employees">

                <div class="hr-form-row">
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
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['employment_status'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">نوع قرارداد</label>
                        <select name="contract_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($contractOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $filters['contract_type'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('report', 'employees') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست کارکنان (<?= hr_num(count($employees)) ?>)
            </h3>
        </div>

        <?php if (empty($employees)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-users"></i>
                <p>کارمندی با این فیلترها یافت نشد.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام</th>
                            <th>دپارتمان</th>
                            <th>پست</th>
                            <th>تاریخ استخدام</th>
                            <th>سابقه</th>
                            <th>نوع قرارداد</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td><code><?= hr_e($e['employee_code']) ?></code></td>
                                <td>
                                    <a href="<?= hr_url('employee', 'show', ['id' => $e['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($e['first_name'] . ' ' . $e['last_name']) ?>
                                    </a>
                                    <?php if (!empty($e['mobile'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($e['mobile']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e($e['department_name'] ?? '—') ?></td>
                                <td><?= hr_e($e['position_title'] ?? '—') ?></td>
                                <td><?= hr_date($e['hire_date'], 'Y/m/d') ?></td>
                                <td><?= hr_num(hr_calculate_tenure($e['hire_date'])) ?> سال</td>
                                <td>
                                    <small><?= hr_e($contractOptions[$e['contract_type']] ?? $e['contract_type']) ?></small>
                                </td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($e['employment_status']) ?>">
                                        <?= hr_e($statusOptions[$e['employment_status']] ?? $e['employment_status']) ?>
                                    </span>
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