<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-line"></i> گزارش عملکرد و اهداف
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                میانگین پیشرفت اهداف: <strong><?= hr_num($goalStats['avg_progress']) ?>%</strong>
                — میانگین امتیاز ارزیابی: <strong><?= hr_num($reviewStats['avg_score']) ?> / ۵</strong>
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
            <small><i class="fas fa-bullseye"></i> کل اهداف</small>
            <h3><?= hr_num($goalStats['total']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-check"></i> تکمیل شده</small>
            <h3><?= hr_num($goalStats['completed']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-spinner"></i> فعال</small>
            <h3><?= hr_num($goalStats['active']) ?></h3>
        </div>
        <div class="hr-stat-card critical">
            <small><i class="fas fa-exclamation-triangle"></i> عقب‌افتاده</small>
            <h3><?= hr_num($goalStats['overdue']) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- اهداف -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-bullseye"></i> اهداف اخیر</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($goals)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">هدفی ثبت نشده است.</p></div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>کارمند</th>
                                        <th>عنوان</th>
                                        <th>پیشرفت</th>
                                        <th>مهلت</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($goals, 0, 50) as $g): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= hr_url('goal', 'show', ['id' => $g['id']]) ?>">
                                                    <?= hr_e(trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''))) ?>
                                                </a>
                                            </td>
                                            <td><?= hr_e(hr_truncate($g['title'], 40)) ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div style="flex: 1; background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden; min-width: 60px;">
                                                        <div style="width: <?= (int) $g['progress'] ?>%; background: var(--hr-primary); height: 100%;"></div>
                                                    </div>
                                                    <small><strong><?= hr_num($g['progress']) ?>%</strong></small>
                                                </div>
                                            </td>
                                            <td><?= hr_date($g['due_date'], 'Y/m/d') ?></td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($g['status']) ?>">
                                                    <?= hr_e($goalStatuses[$g['status']] ?? '') ?>
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

            <!-- ارزیابی‌ها -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> ارزیابی‌های اخیر</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($reviews)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">ارزیابی ثبت نشده است.</p></div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>کارمند</th>
                                        <th>دوره</th>
                                        <th>امتیاز</th>
                                        <th>رتبه</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($reviews, 0, 50) as $r): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= hr_url('performance', 'show', ['id' => $r['id']]) ?>">
                                                    <?= hr_e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?>
                                                </a>
                                            </td>
                                            <td><small><?= hr_e($r['review_period']) ?></small></td>
                                            <td>
                                                <?php if ($r['overall_score'] !== null): ?>
                                                    <strong><?= hr_num($r['overall_score']) ?></strong>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($r['rating'])): ?>
                                                    <span class="hr-status-badge <?= hr_rating_class($r['rating']) ?>">
                                                        <?= hr_e($ratingOptions[$r['rating']] ?? '') ?>
                                                    </span>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($r['status']) ?>">
                                                    <?= hr_e($reviewStatuses[$r['status']] ?? '') ?>
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
            <!-- توزیع رتبه‌ها -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-award"></i> توزیع رتبه‌ها</h3></div>
                <div class="card-body">
                    <?php if (empty($ratingDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $totalRatings = array_sum(array_column($ratingDist, 'count'));
                        foreach ($ratingDist as $rd):
                            $pct = round(($rd['count'] / $totalRatings) * 100);
                        ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-1">
                                    <span class="hr-status-badge <?= hr_rating_class($rd['rating']) ?>">
                                        <?= hr_e($ratingOptions[$rd['rating']] ?? $rd['rating']) ?>
                                    </span>
                                    <strong><?= hr_num($rd['count']) ?> <small class="hr-text-muted">(<?= hr_num($pct) ?>%)</small></strong>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="width: <?= $pct ?>%; background: var(--hr-warning); height: 100%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>