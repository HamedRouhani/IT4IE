<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-check"></i> ثبت‌نام #<?= hr_num($enrollment['id']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <span class="hr-status-badge <?= hr_status_class($enrollment['status']) ?>">
                    <?= hr_e($statusOptions[$enrollment['status']] ?? $enrollment['status']) ?>
                </span>
                <?php if (!empty($enrollment['rating'])): ?>
                    <span class="hr-status-badge <?= hr_rating_class($enrollment['rating']) ?>">
                        <?= hr_e($ratingOptions[$enrollment['rating']] ?? '') ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('enrollment', 'edit', ['id' => $enrollment['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('enrollment') ?>" class="btn-hr-outline">
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
        <?php if ($enrollment['attendance_percent'] !== null): ?>
            <div class="hr-stat-card">
                <small><i class="fas fa-user-clock"></i> حضور</small>
                <h3><?= hr_num($enrollment['attendance_percent']) ?>%</h3>
            </div>
        <?php endif; ?>
        <?php if ($enrollment['score'] !== null): ?>
            <div class="hr-stat-card success">
                <small><i class="fas fa-award"></i> امتیاز</small>
                <h3><?= hr_num($enrollment['score']) ?></h3>
            </div>
        <?php endif; ?>
        <div class="hr-stat-card <?= !empty($enrollment['certificate_issued']) ? 'success' : '' ?>">
            <small><i class="fas fa-certificate"></i> گواهی‌نامه</small>
            <h3 style="font-size: 1rem;">
                <?= !empty($enrollment['certificate_issued']) ? 'صادر شده' : 'صادر نشده' ?>
            </h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-calendar-plus"></i> تاریخ ثبت‌نام</small>
            <h3 style="font-size: 1rem;"><?= hr_date($enrollment['enrolled_date'], 'Y/m/d') ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">

        <!-- ستون چپ: اطلاعات -->
        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات ثبت‌نام</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">کارمند</th>
                                <td>
                                    <a href="<?= hr_url('employee', 'show', ['id' => $enrollment['employee_id']]) ?>">
                                        <?= hr_e($enrollment['employee_name'] ?? '—') ?>
                                    </a>
                                    <?php if (!empty($enrollment['employee_code'])): ?>
                                        <br><small class="hr-text-muted"><?= hr_e($enrollment['employee_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>دپارتمان</th>
                                <td><?= hr_e($enrollment['department_name'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>موبایل</th>
                                <td><?= hr_e($enrollment['mobile'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>ایمیل</th>
                                <td><?= hr_e($enrollment['email'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>دوره</th>
                                <td>
                                    <?php if (!empty($enrollment['training_id'])): ?>
                                        <a href="<?= hr_url('training', 'show', ['id' => $enrollment['training_id']]) ?>">
                                            <?= hr_e($enrollment['training_title'] ?? '—') ?>
                                        </a>
                                        <?php if (!empty($enrollment['training_code'])): ?>
                                            <br><small class="hr-text-muted"><?= hr_e($enrollment['training_code']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>تاریخ شروع / پایان دوره</th>
                                <td>
                                    <?= hr_date($enrollment['training_start'] ?? null, 'Y/m/d') ?>
                                    -
                                    <?= hr_date($enrollment['training_end'] ?? null, 'Y/m/d') ?>
                                </td>
                            </tr>
                            <?php if (!empty($enrollment['duration_hours'])): ?>
                                <tr>
                                    <th>مدت دوره</th>
                                    <td><?= hr_num($enrollment['duration_hours']) ?> ساعت</td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($enrollment['instructor'])): ?>
                                <tr>
                                    <th>مدرس</th>
                                    <td><?= hr_e($enrollment['instructor']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($enrollment['location'])): ?>
                                <tr>
                                    <th>مکان</th>
                                    <td><?= hr_e($enrollment['location']) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($enrollment['employee_notes']) || !empty($enrollment['manager_notes']) || !empty($enrollment['feedback'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> یادداشت‌ها</h3></div>
                    <div class="card-body">
                        <?php if (!empty($enrollment['feedback'])): ?>
                            <div class="hr-mb-3">
                                <strong><i class="fas fa-comment-dots"></i> بازخورد کارمند:</strong>
                                <p><?= nl2br(hr_e($enrollment['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($enrollment['employee_notes'])): ?>
                            <div class="hr-mb-3">
                                <strong><i class="fas fa-user"></i> یادداشت کارمند:</strong>
                                <p><?= nl2br(hr_e($enrollment['employee_notes'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($enrollment['manager_notes'])): ?>
                            <div>
                                <strong><i class="fas fa-user-tie"></i> یادداشت مدیر:</strong>
                                <p><?= nl2br(hr_e($enrollment['manager_notes'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ستون راست: نتایج -->
        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check"></i> نتایج و ارزیابی</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr>
                                <th style="width: 140px;">وضعیت</th>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($enrollment['status']) ?>">
                                        <?= hr_e($statusOptions[$enrollment['status']] ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if ($enrollment['attendance_percent'] !== null): ?>
                                <tr>
                                    <th>درصد حضور</th>
                                    <td><strong><?= hr_num($enrollment['attendance_percent']) ?>%</strong></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($enrollment['score'] !== null): ?>
                                <tr>
                                    <th>امتیاز نهایی</th>
                                    <td><strong><?= hr_num($enrollment['score']) ?></strong></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($enrollment['rating'])): ?>
                                <tr>
                                    <th>رتبه کیفی</th>
                                    <td>
                                        <span class="hr-status-badge <?= hr_rating_class($enrollment['rating']) ?>">
                                            <?= hr_e($ratingOptions[$enrollment['rating']] ?? '') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($enrollment['completed_at'])): ?>
                                <tr>
                                    <th>تاریخ اتمام</th>
                                    <td><?= hr_date($enrollment['completed_at'], 'Y/m/d') ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>گواهی‌نامه</th>
                                <td>
                                    <?php if (!empty($enrollment['certificate_issued'])): ?>
                                        <span class="hr-status-badge hr-status-active">
                                            <i class="fas fa-certificate"></i> صادر شده
                                        </span>
                                        <?php if (!empty($enrollment['certificate_number'])): ?>
                                            <br><small>شماره: <code><?= hr_e($enrollment['certificate_number']) ?></code></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="hr-text-muted">صادر نشده</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>