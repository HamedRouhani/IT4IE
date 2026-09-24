<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-graduation-cap"></i> گزارش آموزش
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کل دوره‌ها: <strong><?= hr_num($trainingStats['total']) ?></strong>
                — ساعات آموزش: <strong><?= hr_num($trainingStats['total_hours']) ?></strong>
                — هزینه کل: <strong><?= hr_money($trainingStats['total_cost']) ?></strong>
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

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-book"></i> کل دوره‌ها</small>
            <h3><?= hr_num($trainingStats['total']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-check"></i> تکمیل شده</small>
            <h3><?= hr_num($trainingStats['completed']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-spinner"></i> در حال اجرا</small>
            <h3><?= hr_num($trainingStats['in_progress']) ?></h3>
        </div>
        <div class="hr-stat-card critical">
            <small><i class="fas fa-money-bill"></i> هزینه کل</small>
            <h3 style="font-size: 1rem;"><?= hr_money($trainingStats['total_cost']) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- دوره‌ها -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-book"></i> دوره‌ها (<?= hr_num(count($trainings)) ?>)</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($trainings)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">دوره‌ای ثبت نشده است.</p></div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>عنوان</th>
                                        <th>نوع</th>
                                        <th>بازه</th>
                                        <th>مدت</th>
                                        <th>ثبت‌نام</th>
                                        <th>هزینه</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trainings as $t): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= hr_url('training', 'show', ['id' => $t['id']]) ?>">
                                                    <?= hr_e(hr_truncate($t['title'], 35)) ?>
                                                </a>
                                            </td>
                                            <td><small><?= hr_e(\App\Software\Hr\Models\Training::getTypeOptions()[$t['training_type']] ?? '') ?></small></td>
                                            <td><small><?= hr_date($t['start_date'], 'Y/m/d') ?></small></td>
                                            <td><?= hr_num($t['duration_hours'] ?? 0) ?> ساعت</td>
                                            <td>
                                                <strong><?= hr_num($t['enrollments_count']) ?></strong>
                                                <?php if ($t['max_participants']): ?>
                                                    / <?= hr_num($t['max_participants']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($t['total_cost']): ?>
                                                    <small><?= hr_money($t['total_cost']) ?></small>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($t['status']) ?>">
                                                    <?= hr_e($trainingStatuses[$t['status']] ?? '') ?>
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

            <!-- ثبت‌نام‌ها -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user-check"></i> ثبت‌نام‌های اخیر</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($enrollments)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">ثبت‌نامی وجود ندارد.</p></div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>کارمند</th>
                                        <th>دوره</th>
                                        <th>تاریخ ثبت‌نام</th>
                                        <th>امتیاز</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($enrollments, 0, 50) as $en): ?>
                                        <tr>
                                            <td><?= hr_e(trim(($en['first_name'] ?? '') . ' ' . ($en['last_name'] ?? ''))) ?></td>
                                            <td><small><?= hr_e(hr_truncate($en['training_title'] ?? '', 40)) ?></small></td>
                                            <td><small><?= hr_date($en['enrolled_date'], 'Y/m/d') ?></small></td>
                                            <td>
                                                <?php if ($en['score'] !== null): ?>
                                                    <strong><?= hr_num($en['score']) ?></strong>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($en['status']) ?>">
                                                    <?= hr_e($enrollStatuses[$en['status']] ?? '') ?>
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
        </div>

        <div>
            <!-- توزیع وضعیت ثبت‌نام -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie"></i> توزیع ثبت‌نام‌ها</h3></div>
                <div class="card-body">
                    <?php if (empty($enrollmentDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $totalEnr = array_sum(array_column($enrollmentDist, 'count'));
                        foreach ($enrollmentDist as $ed):
                            $pct = round(($ed['count'] / $totalEnr) * 100);
                        ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span class="hr-status-badge <?= hr_status_class($ed['status']) ?>">
                                    <?= hr_e($enrollStatuses[$ed['status']] ?? $ed['status']) ?>
                                </span>
                                <strong><?= hr_num($ed['count']) ?> <small class="hr-text-muted">(<?= hr_num($pct) ?>%)</small></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>