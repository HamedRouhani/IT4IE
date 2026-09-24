<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-line"></i> ارزیابی <?= hr_e($review['employee_name'] ?? '') ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                دوره: <code><?= hr_e($review['review_period']) ?></code>
                <span class="hr-status-badge <?= hr_status_class($review['status']) ?>">
                    <?= hr_e(\App\Software\Hr\Models\PerformanceReview::getStatusOptions()[$review['status']] ?? '') ?>
                </span>
                <?php if (!empty($review['rating'])): ?>
                    <span class="hr-status-badge <?= hr_rating_class($review['rating']) ?>">
                        <?= hr_e($ratingOptions[$review['rating']] ?? '') ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('performance', 'edit', ['id' => $review['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('performance') ?>" class="btn-hr-outline">
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

    <!-- کارت‌های امتیاز -->
    <div class="hr-stats-grid hr-mb-4">
        <?php if ($review['overall_score'] !== null): ?>
            <div class="hr-stat-card success">
                <small>امتیاز کلی</small>
                <h3><?= hr_num($review['overall_score']) ?> / ۵</h3>
            </div>
        <?php endif; ?>
        <?php if ($review['goals_score'] !== null): ?>
            <div class="hr-stat-card warning">
                <small>امتیاز اهداف</small>
                <h3><?= hr_num($review['goals_score']) ?> / ۵</h3>
            </div>
        <?php endif; ?>
        <div class="hr-stat-card">
            <small>بازه دوره</small>
            <h3 style="font-size: 1rem;">
                <?= hr_date($review['period_start'], 'Y/m/d') ?> تا <?= hr_date($review['period_end'], 'Y/m/d') ?>
            </h3>
        </div>
    </div>

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> امتیازها</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <?php foreach ($scoreFields as $field => $label): ?>
                                <tr>
                                    <th style="width: 200px;"><?= hr_e($label) ?></th>
                                    <td>
                                        <?php $val = $review[$field] ?? null; ?>
                                        <?php if ($val !== null && $val !== ''): ?>
                                            <strong><?= hr_num($val) ?></strong> / ۵
                                        <?php else: ?>
                                            <span class="hr-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات ارزیابی</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 200px;">کارمند</th><td>
                                <a href="<?= hr_url('employee', 'show', ['id' => $review['employee_id']]) ?>">
                                    <?= hr_e($review['employee_name'] ?? '—') ?>
                                </a>
                            </td></tr>
                            <tr><th>کد پرسنلی</th><td><?= hr_e($review['employee_code'] ?? '—') ?></td></tr>
                            <tr><th>دپارتمان</th><td><?= hr_e($review['department_name'] ?? '—') ?></td></tr>
                            <tr><th>پست</th><td><?= hr_e($review['position_title'] ?? '—') ?></td></tr>
                            <tr><th>ارزیاب</th><td><?= hr_e($review['reviewer_name'] ?? '—') ?></td></tr>
                            <tr><th>نوع ارزیابی</th><td><?= hr_e($typeOptions[$review['review_type']] ?? '') ?></td></tr>
                            <?php if (!empty($review['recommendation'])): ?>
                                <tr><th>توصیه</th><td>
                                    <span class="hr-status-badge hr-status-info">
                                        <?= hr_e($recommendationOptions[$review['recommendation']] ?? '') ?>
                                    </span>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <?php if (!empty($review['strengths'])): ?>
                <div class="card hr-mb-3" style="border-right: 4px solid var(--hr-success);">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-thumbs-up"></i> نقاط قوت</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['strengths'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($review['weaknesses'])): ?>
                <div class="card hr-mb-3" style="border-right: 4px solid var(--hr-danger);">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-thumbs-down"></i> نقاط ضعف</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['weaknesses'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($review['goals_achievement'])): ?>
                <div class="card hr-mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullseye"></i> دستاورد اهداف</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['goals_achievement'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($review['training_needs'])): ?>
                <div class="card hr-mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> نیازهای آموزشی</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['training_needs'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($review['manager_comments'])): ?>
                <div class="card hr-mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-tie"></i> نظر مدیر</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['manager_comments'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($review['employee_comments'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user"></i> نظر کارمند</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($review['employee_comments'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>