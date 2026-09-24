<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-money-bill-wave"></i> حکم حقوقی
                <?= hr_e($compensation['employee_name'] ?? '') ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                از <strong><?= hr_date($compensation['effective_date'], 'Y/m/d') ?></strong>
                <?php if (!empty($compensation['end_date'])): ?>
                    تا <strong><?= hr_date($compensation['end_date'], 'Y/m/d') ?></strong>
                <?php else: ?>
                    <span class="hr-status-badge hr-status-active">فعلی</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('compensation', 'edit', ['id' => $compensation['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('compensation') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های خلاصه -->
    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card success">
            <small><i class="fas fa-coins"></i> حقوق کل</small>
            <h3 style="font-size: 1.25rem;"><?= hr_money($compensation['total_fixed']) ?></h3>
        </div>
        <div class="hr-stat-card">
            <small><i class="fas fa-money-check"></i> حقوق پایه</small>
            <h3 style="font-size: 1.25rem;"><?= hr_money($compensation['base_salary']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-hand-holding-usd"></i> جمع مزایا</small>
            <h3 style="font-size: 1.25rem;">
                <?php
                $allowances = (float) $compensation['housing_allowance']
                    + (float) $compensation['food_allowance']
                    + (float) $compensation['transportation_allowance']
                    + (float) $compensation['child_allowance']
                    + (float) $compensation['seniority_allowance']
                    + (float) $compensation['other_allowances'];
                echo hr_money($allowances);
                ?>
            </h3>
        </div>
        <div class="hr-stat-card">
            <small><i class="fas fa-clock"></i> نرخ اضافه‌کاری</small>
            <h3 style="font-size: 1.25rem;"><?= hr_money($compensation['overtime_rate']) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">

        <div>
            <!-- اطلاعات کارمند -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user"></i> اطلاعات کارمند</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">نام</th><td>
                                <a href="<?= hr_url('employee', 'show', ['id' => $compensation['employee_id']]) ?>">
                                    <?= hr_e($compensation['employee_name'] ?? '—') ?>
                                </a>
                            </td></tr>
                            <tr><th>کد پرسنلی</th><td><?= hr_e($compensation['employee_code'] ?? '—') ?></td></tr>
                            <tr><th>دپارتمان</th><td><?= hr_e($compensation['department_name'] ?? '—') ?></td></tr>
                            <tr><th>پست</th><td><?= hr_e($compensation['position_title'] ?? '—') ?></td></tr>
                            <tr><th>تاریخ استخدام</th><td><?= hr_date($compensation['hire_date'] ?? null, 'Y/m/d') ?></td></tr>
                            <tr><th>موبایل</th><td><?= hr_e($compensation['mobile'] ?? '—') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- تفصیل حقوق و مزایا -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-list-ul"></i> تفصیل حقوق و مزایا</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <?php foreach ($salaryFields as $field => $label): ?>
                                <tr>
                                    <th style="width: 200px;"><?= hr_e($label) ?></th>
                                    <td><?= hr_money($compensation[$field] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="background: #f1f5f9; font-weight: bold;">
                                <th>جمع حقوق ثابت</th>
                                <td><?= hr_money($compensation['total_fixed']) ?></td>
                            </tr>
                            <tr>
                                <th>نرخ اضافه‌کاری (ساعتی)</th>
                                <td><?= hr_money($compensation['overtime_rate']) ?></td>
                            </tr>
                            <tr>
                                <th>واحد پول</th>
                                <td><?= hr_e($currencyOptions[$compensation['currency']] ?? $compensation['currency']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- اطلاعات تکمیلی -->
            <?php if (!empty($compensation['change_reason']) || !empty($compensation['document_ref']) || !empty($compensation['notes']) || !empty($compensation['approver_name'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات تکمیلی</h3></div>
                    <div class="card-body">
                        <table class="hr-table">
                            <tbody>
                                <?php if (!empty($compensation['change_reason'])): ?>
                                    <tr><th style="width: 180px;">دلیل تغییر</th><td><?= hr_e($compensation['change_reason']) ?></td></tr>
                                <?php endif; ?>
                                <?php if (!empty($compensation['document_ref'])): ?>
                                    <tr><th>شماره سند</th><td><?= hr_e($compensation['document_ref']) ?></td></tr>
                                <?php endif; ?>
                                <?php if (!empty($compensation['approver_name'])): ?>
                                    <tr><th>تأییدکننده</th><td><?= hr_e($compensation['approver_name']) ?></td></tr>
                                <?php endif; ?>
                                <?php if (!empty($compensation['notes'])): ?>
                                    <tr><th>یادداشت</th><td><?= nl2br(hr_e($compensation['notes'])) ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ستون راست: تاریخچه -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        تاریخچه حقوق (<?= hr_num(count($history)) ?>)
                    </h3>
                </div>
                <?php if (empty($history)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-history"></i>
                        <p>تاریخچه‌ای موجود نیست.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($history as $h): ?>
                            <?php $isCurrent = (int) $h['id'] === (int) $compensation['id']; ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb; <?= $isCurrent ? 'background:#eff6ff; border-radius:6px; padding-right:0.5rem; padding-left:0.5rem;' : '' ?>">
                                <div class="hr-flex-between">
                                    <div>
                                        <strong><?= hr_date($h['effective_date'], 'Y/m/d') ?></strong>
                                        <?php if (!empty($h['end_date'])): ?>
                                            تا <?= hr_date($h['end_date'], 'Y/m/d') ?>
                                        <?php else: ?>
                                            <span class="hr-status-badge hr-status-active" style="font-size:0.7rem;">فعلی</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="hr-text-muted">حقوق کل: <strong><?= hr_money($h['total_fixed']) ?></strong></small>
                                    </div>
                                    <div>
                                        <?php if (!$isCurrent): ?>
                                            <a href="<?= hr_url('compensation', 'show', ['id' => $h['id']]) ?>"
                                               class="btn-hr-outline btn-sm"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>