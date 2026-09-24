<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-plus"></i> <?= hr_e($recruitment['title']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                شماره: <code><?= hr_e($recruitment['request_number']) ?></code>
                <span class="hr-status-badge <?= hr_status_class($recruitment['status']) ?>" style="margin-right: 0.5rem;">
                    <?= hr_e(\App\Software\Hr\Models\Recruitment::getStatusOptions()[$recruitment['status']] ?? $recruitment['status']) ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('candidate', 'create', ['recruitment_id' => $recruitment['id']]) ?>"
               class="btn-hr-primary">
                <i class="fas fa-user-plus"></i> افزودن متقاضی
            </a>
            <a href="<?= hr_url('recruitment', 'edit', ['id' => $recruitment['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('recruitment') ?>" class="btn-hr-outline">
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
            <h3><?= hr_num($recruitment['headcount']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-check"></i> استخدام‌شده</small>
            <h3><?= hr_num($recruitment['filled_count']) ?></h3>
        </div>
        <div class="hr-stat-card <?= $recruitment['open_count'] > 0 ? 'critical' : '' ?>">
            <small><i class="fas fa-user-plus"></i> ظرفیت خالی</small>
            <h3><?= hr_num($recruitment['open_count']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-user-tie"></i> متقاضیان</small>
            <h3><?= hr_num(count($candidates)) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">

        <div>
            <!-- اطلاعات نیاز -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات نیاز</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 200px;">شماره درخواست</th><td><code><?= hr_e($recruitment['request_number']) ?></code></td></tr>
                            <tr><th>پست</th><td><?= hr_e($recruitment['position_title'] ?? '—') ?></td></tr>
                            <tr><th>دپارتمان</th><td><?= hr_e($recruitment['department_name'] ?? '—') ?></td></tr>
                            <tr><th>نوع استخدام</th><td><?= hr_employment_type_label($recruitment['employment_type']) ?></td></tr>
                            <tr><th>اولویت</th><td>
                                <span class="hr-status-badge <?= hr_priority_class($recruitment['priority']) ?>">
                                    <?= hr_priority_label($recruitment['priority']) ?>
                                </span>
                            </td></tr>
                            <tr><th>تاریخ باز شدن</th><td><?= hr_date($recruitment['opened_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>تاریخ هدف</th><td><?= hr_date($recruitment['target_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>حقوق پیشنهادی</th><td>
                                <?php if ($recruitment['min_salary'] || $recruitment['max_salary']): ?>
                                    <?= hr_money($recruitment['min_salary'] ?: 0) ?> تا <?= hr_money($recruitment['max_salary'] ?: 0) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td></tr>
                            <?php if (!empty($recruitment['description'])): ?>
                            <tr><th>توضیحات</th><td><?= nl2br(hr_e($recruitment['description'])) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($recruitment['requirements'])): ?>
                            <tr><th>شرایط احراز</th><td><?= nl2br(hr_e($recruitment['requirements'])) ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- لیست متقاضیان -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i>
                        متقاضیان (<?= hr_num(count($candidates)) ?>)
                    </h3>
                    <a href="<?= hr_url('candidate', 'create', ['recruitment_id' => $recruitment['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> افزودن
                    </a>
                </div>

                <?php if (empty($candidates)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-users"></i>
                        <p>هنوز متقاضی ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>کد</th>
                                    <th>نام</th>
                                    <th>موبایل</th>
                                    <th>وضعیت</th>
                                    <th>امتیاز</th>
                                    <th style="width: 120px; text-align: center;">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $c): ?>
                                    <tr>
                                        <td><code><?= hr_e($c['candidate_code'] ?? '—') ?></code></td>
                                        <td>
                                            <a href="<?= hr_url('candidate', 'show', ['id' => $c['id']]) ?>"
                                               style="color: var(--hr-primary-dark); font-weight: 600;">
                                                <?= hr_e(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?>
                                            </a>
                                        </td>
                                        <td><?= hr_e($c['mobile'] ?? '—') ?></td>
                                        <td>
                                            <span class="hr-status-badge <?= \App\Software\Hr\Models\Candidate::getStatusClass($c['status']) ?>">
                                                <?= hr_e(\App\Software\Hr\Models\Candidate::getStatusOptions()[$c['status']] ?? $c['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($c['rating'])): ?>
                                                <strong><?= hr_num($c['rating']) ?></strong> / ۵
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                                <a href="<?= hr_url('candidate', 'show', ['id' => $c['id']]) ?>"
                                                   class="btn-hr-outline btn-sm" title="مشاهده">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= hr_url('interview', 'create', ['candidate_id' => $c['id']]) ?>"
                                                   class="btn-hr-primary btn-sm" title="مصاحبه">
                                                    <i class="fas fa-comments"></i>
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

        <!-- ستون راست: خلاصه وضعیت -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> خلاصه وضعیت</h3>
                </div>
                <div class="card-body">
                    <?php
                    $statusCounts = [];
                    foreach ($candidates as $c) {
                        $statusCounts[$c['status']] = ($statusCounts[$c['status']] ?? 0) + 1;
                    }
                    ?>
                    <?php if (empty($statusCounts)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php foreach (\App\Software\Hr\Models\Candidate::getStatusOptions() as $key => $label): ?>
                            <?php if (!empty($statusCounts[$key])): ?>
                                <div class="hr-flex-between hr-mb-2">
                                    <span class="hr-status-badge <?= \App\Software\Hr\Models\Candidate::getStatusClass($key) ?>">
                                        <?= hr_e($label) ?>
                                    </span>
                                    <strong><?= hr_num($statusCounts[$key]) ?></strong>
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