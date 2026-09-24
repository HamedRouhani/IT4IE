<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-graduation-cap"></i> <?= hr_e($training['title']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?php if (!empty($training['code'])): ?>
                    کد: <code><?= hr_e($training['code']) ?></code> —
                <?php endif; ?>
                <span class="hr-status-badge <?= hr_status_class($training['status']) ?>">
                    <?= hr_e($statusOptions[$training['status']] ?? '') ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('enrollment', 'create', ['training_id' => $training['id']]) ?>"
               class="btn-hr-primary">
                <i class="fas fa-user-plus"></i> ثبت‌نام
            </a>
            <a href="<?= hr_url('training', 'edit', ['id' => $training['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('training') ?>" class="btn-hr-outline">
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

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-users"></i> ظرفیت</small>
            <h3>
                <?= hr_num($training['current_participants'] ?? 0) ?>
                <?php if (!empty($training['max_participants'])): ?>
                    / <?= hr_num($training['max_participants']) ?>
                <?php endif; ?>
            </h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-clock"></i> مدت</small>
            <h3><?= hr_num($training['duration_hours'] ?? 0) ?> ساعت</h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-calendar-alt"></i> شروع</small>
            <h3 style="font-size: 1rem;"><?= hr_date($training['start_date'], 'Y/m/d') ?></h3>
        </div>
        <?php if (!empty($training['cost_per_person'])): ?>
            <div class="hr-stat-card critical">
                <small><i class="fas fa-money-bill"></i> هزینه سرانه</small>
                <h3 style="font-size: 1rem;"><?= hr_money($training['cost_per_person']) ?></h3>
            </div>
        <?php endif; ?>
    </div>

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات دوره</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 200px;">نوع</th><td><?= hr_e($typeOptions[$training['training_type']] ?? '') ?></td></tr>
                            <tr><th>دسته‌بندی</th><td><?= hr_e($training['category'] ?? '—') ?></td></tr>
                            <tr><th>ارائه‌دهنده</th><td><?= hr_e($training['provider'] ?? '—') ?></td></tr>
                            <tr><th>مدرس</th><td><?= hr_e($training['instructor'] ?? '—') ?></td></tr>
                            <tr><th>شیوه برگزاری</th><td><?= hr_e($deliveryModeOptions[$training['delivery_mode']] ?? $training['delivery_mode']) ?></td></tr>
                            <tr><th>مکان</th><td><?= hr_e($training['location'] ?? '—') ?></td></tr>
                            <tr><th>شروع</th><td><?= hr_date($training['start_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>پایان</th><td><?= hr_date($training['end_date'], 'Y/m/d') ?></td></tr>
                            <?php if (!empty($training['registration_deadline'])): ?>
                                <tr><th>مهلت ثبت‌نام</th><td><?= hr_date($training['registration_deadline'], 'Y/m/d') ?></td></tr>
                            <?php endif; ?>
                            <tr><th>گواهی‌نامه</th><td>
                                <?php if (!empty($training['has_certificate'])): ?>
                                    <span class="hr-status-badge hr-status-active">دارد</span>
                                <?php else: ?>
                                    <span class="hr-text-muted">ندارد</span>
                                <?php endif; ?>
                            </td></tr>
                            <?php if (!empty($training['description'])): ?>
                                <tr><th>توضیحات</th><td><?= nl2br(hr_e($training['description'])) ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- لیست ثبت‌نام‌ها -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-check"></i>
                        شرکت‌کنندگان (<?= hr_num(count($enrollments)) ?>)
                    </h3>
                    <a href="<?= hr_url('enrollment', 'create', ['training_id' => $training['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> ثبت‌نام جدید
                    </a>
                </div>

                <?php if (empty($enrollments)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-users"></i>
                        <p>هنوز کسی ثبت‌نام نکرده است.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>کارمند</th>
                                    <th>تاریخ ثبت‌نام</th>
                                    <th>وضعیت</th>
                                    <th>امتیاز</th>
                                    <th style="width: 80px; text-align: center;">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $en): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= hr_url('enrollment', 'show', ['id' => $en['id']]) ?>"
                                               style="color: var(--hr-primary-dark); font-weight: 600;">
                                                <?= hr_e(trim(($en['first_name'] ?? '') . ' ' . ($en['last_name'] ?? ''))) ?>
                                            </a>
                                        </td>
                                        <td><?= hr_date($en['enrolled_date'], 'Y/m/d') ?></td>
                                        <td>
                                            <span class="hr-status-badge <?= hr_status_class($en['status']) ?>">
                                                <?= hr_e($enrollStatusOptions[$en['status']] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($en['score'] !== null): ?>
                                                <strong><?= hr_num($en['score']) ?></strong>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= hr_url('enrollment', 'show', ['id' => $en['id']]) ?>"
                                               class="btn-hr-outline btn-sm"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie"></i> خلاصه</h3></div>
                <div class="card-body">
                    <?php
                    $statusCounts = [];
                    foreach ($enrollments as $en) {
                        $statusCounts[$en['status']] = ($statusCounts[$en['status']] ?? 0) + 1;
                    }
                    ?>
                    <?php if (empty($statusCounts)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php foreach ($enrollStatusOptions as $k => $v): ?>
                            <?php if (!empty($statusCounts[$k])): ?>
                                <div class="hr-flex-between hr-mb-2">
                                    <span class="hr-status-badge <?= hr_status_class($k) ?>"><?= hr_e($v) ?></span>
                                    <strong><?= hr_num($statusCounts[$k]) ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>