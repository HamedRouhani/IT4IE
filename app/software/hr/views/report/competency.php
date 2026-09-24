<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-cubes"></i> گزارش شایستگی‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کل: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — هسته‌ای: <strong style="color: var(--hr-warning);"><?= hr_num($stats['core']) ?></strong>
                — تخصیص‌یافته: <strong><?= hr_num($stats['assigned']) ?></strong>
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
            <small><i class="fas fa-cubes"></i> کل شایستگی‌ها</small>
            <h3><?= hr_num($stats['total']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-check"></i> فعال</small>
            <h3><?= hr_num($stats['active']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-star"></i> هسته‌ای</small>
            <h3><?= hr_num($stats['core']) ?></h3>
        </div>
        <div class="hr-stat-card critical">
            <small><i class="fas fa-user-tag"></i> تخصیص‌یافته</small>
            <h3><?= hr_num($stats['assigned']) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- لیست شایستگی‌ها -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-list"></i> فهرست شایستگی‌ها</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($competencies)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">شایستگی‌ای ثبت نشده است.</p></div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>کد</th>
                                        <th>نام</th>
                                        <th>دسته</th>
                                        <th>نوع</th>
                                        <th>هسته‌ای</th>
                                        <th>تخصیص‌یافته</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($competencies as $c): ?>
                                        <tr>
                                            <td><code><?= hr_e($c['code'] ?? '—') ?></code></td>
                                            <td>
                                                <a href="<?= hr_url('competency', 'show', ['id' => $c['id']]) ?>">
                                                    <?= hr_e(hr_truncate($c['name'], 40)) ?>
                                                </a>
                                                <?php if (!empty($c['parent_name'])): ?>
                                                    <br><small class="hr-text-muted">↳ <?= hr_e($c['parent_name']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="hr-status-badge <?= \App\Software\Hr\Models\Competency::getCategoryClass($c['category']) ?>">
                                                    <?= hr_e($categoryOptions[$c['category']] ?? '') ?>
                                                </span>
                                            </td>
                                            <td><small><?= hr_e($typeOptions[$c['competency_type']] ?? '') ?></small></td>
                                            <td>
                                                <?php if (!empty($c['is_core'])): ?>
                                                    <span class="hr-status-badge hr-status-danger"><i class="fas fa-star"></i></span>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                            <td><strong><?= hr_num($c['assigned_count']) ?></strong></td>
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
            <!-- توزیع دسته‌بندی -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tags"></i> توزیع دسته‌بندی</h3></div>
                <div class="card-body">
                    <?php if (empty($categoryDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $totalCat = array_sum(array_column($categoryDist, 'count'));
                        foreach ($categoryDist as $cd):
                            $pct = round(($cd['count'] / $totalCat) * 100);
                        ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-1">
                                    <span class="hr-status-badge <?= \App\Software\Hr\Models\Competency::getCategoryClass($cd['category']) ?>">
                                        <?= hr_e($categoryOptions[$cd['category']] ?? $cd['category']) ?>
                                    </span>
                                    <strong><?= hr_num($cd['count']) ?> <small class="hr-text-muted">(<?= hr_num($pct) ?>%)</small></strong>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="width: <?= $pct ?>%; background: var(--hr-primary); height: 100%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- فاصله مهارتی -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> فاصله مهارتی (Skill Gap)</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($skillGaps)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;">
                            <i class="fas fa-check-circle" style="color: var(--hr-success);"></i>
                            <p class="hr-text-muted">هیچ فاصله مهارتی‌ای ثبت نشده است.</p>
                        </div>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 1rem; max-height: 500px; overflow-y: auto;">
                            <?php foreach ($skillGaps as $sg): ?>
                                <li style="padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb;">
                                    <div>
                                        <strong><?= hr_e(trim(($sg['first_name'] ?? '') . ' ' . ($sg['last_name'] ?? ''))) ?></strong>
                                        <br>
                                        <small class="hr-text-muted"><?= hr_e($sg['competency_name']) ?></small>
                                    </div>
                                    <div class="hr-flex-between hr-mt-2">
                                        <small>
                                            فعلی: <strong><?= hr_num($sg['current_level']) ?></strong> /
                                            موردنیاز: <strong><?= hr_num($sg['required_level']) ?></strong>
                                        </small>
                                        <span class="hr-status-badge hr-status-danger" style="font-size: 0.7rem;">
                                            فاصله: <?= hr_num($sg['required_level'] - $sg['current_level']) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>