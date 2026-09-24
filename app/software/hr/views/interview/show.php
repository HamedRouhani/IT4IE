<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-comments"></i> مصاحبه —
                <?= hr_e($interview['candidate_name'] ?? '') ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <span class="hr-status-badge hr-status-info">
                    دور <?= hr_num($interview['round']) ?>
                </span>
                <span class="hr-status-badge <?= hr_status_class($interview['status']) ?>">
                    <?= hr_e(\App\Software\Hr\Models\Interview::getStatusOptions()[$interview['status']] ?? '') ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('interview', 'edit', ['id' => $interview['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('interview') ?>" class="btn-hr-outline">
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
    <?php if ($interview['overall_score'] || $interview['technical_score'] || $interview['communication_score']): ?>
        <div class="hr-stats-grid hr-mb-4">
            <?php if ($interview['technical_score']): ?>
                <div class="hr-stat-card">
                    <small>فنی</small>
                    <h3><?= hr_num($interview['technical_score']) ?></h3>
                </div>
            <?php endif; ?>
            <?php if ($interview['communication_score']): ?>
                <div class="hr-stat-card success">
                    <small>ارتباطی</small>
                    <h3><?= hr_num($interview['communication_score']) ?></h3>
                </div>
            <?php endif; ?>
            <?php if ($interview['culture_fit_score']): ?>
                <div class="hr-stat-card warning">
                    <small>تناسب فرهنگی</small>
                    <h3><?= hr_num($interview['culture_fit_score']) ?></h3>
                </div>
            <?php endif; ?>
            <?php if ($interview['overall_score']): ?>
                <div class="hr-stat-card critical">
                    <small>امتیاز کلی</small>
                    <h3><?= hr_num($interview['overall_score']) ?></h3>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات مصاحبه</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 200px;">متقاضی</th><td>
                                <a href="<?= hr_url('candidate', 'show', ['id' => $interview['candidate_id']]) ?>">
                                    <?= hr_e($interview['candidate_name'] ?? '') ?>
                                </a>
                            </td></tr>
                            <tr><th>آگهی</th><td>
                                <?php if (!empty($interview['recruitment_id'])): ?>
                                    <a href="<?= hr_url('recruitment', 'show', ['id' => $interview['recruitment_id']]) ?>">
                                        <?= hr_e($interview['request_number'] ?? '') ?>
                                    </a>
                                <?php else: ?>—<?php endif; ?>
                            </td></tr>
                            <tr><th>نوع</th><td><?= hr_e(\App\Software\Hr\Models\Interview::getTypeOptions()[$interview['interview_type']] ?? '') ?></td></tr>
                            <tr><th>دور</th><td><?= hr_num($interview['round']) ?></td></tr>
                            <tr><th>مصاحبه‌گر</th><td><?= hr_e($interview['interviewer_name'] ?? '—') ?></td></tr>
                            <tr><th>زمان</th><td><?= hr_date($interview['scheduled_date'], 'Y/m/d H:i') ?></td></tr>
                            <tr><th>مدت</th><td><?= hr_num($interview['duration_minutes']) ?> دقیقه</td></tr>
                            <tr><th>مکان</th><td><?= hr_e($interview['location'] ?? '—') ?></td></tr>
                            <?php if (!empty($interview['meeting_link'])): ?>
                                <tr><th>لینک جلسه</th><td>
                                    <a href="<?= hr_e($interview['meeting_link']) ?>" target="_blank">
                                        <i class="fas fa-video"></i> ورود به جلسه
                                    </a>
                                </td></tr>
                            <?php endif; ?>
                            <?php if (!empty($interview['recommendation'])): ?>
                                <tr><th>توصیه</th><td>
                                    <span class="hr-status-badge hr-status-<?= $interview['recommendation'] === 'hire' ? 'active' : ($interview['recommendation'] === 'reject' ? 'danger' : 'warning') ?>">
                                        <?= hr_e(\App\Software\Hr\Models\Interview::getRecommendationOptions()[$interview['recommendation']] ?? '') ?>
                                    </span>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <?php if (!empty($interview['strengths'])): ?>
                <div class="card hr-mb-3" style="border-right: 4px solid var(--hr-success);">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-thumbs-up"></i> نقاط قوت</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($interview['strengths'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($interview['weaknesses'])): ?>
                <div class="card hr-mb-3" style="border-right: 4px solid var(--hr-danger);">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-thumbs-down"></i> نقاط ضعف</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($interview['weaknesses'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($interview['notes'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> یادداشت</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($interview['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>