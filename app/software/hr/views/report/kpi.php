<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-bar"></i> گزارش KPI
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کل شاخص‌ها: <strong><?= hr_num($stats['total_kpis']) ?></strong>
                — با مقدار: <strong><?= hr_num($stats['with_values']) ?></strong>
                — کل مقادیر: <strong><?= hr_num($stats['total_values']) ?></strong>
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

    <!-- شاخص‌ها -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> شاخص‌ها</h3></div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($kpis)): ?>
                <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">شاخصی موجود نیست.</p></div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="hr-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>نام</th>
                                <th>دسته</th>
                                <th>واحد</th>
                                <th>آخرین مقدار</th>
                                <th>هدف</th>
                                <th>دستیابی</th>
                                <th>تعداد مقادیر</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kpis as $k): ?>
                                <?php
                                $ach = null;
                                if ($k['latest_value'] !== null && $k['latest_target'] !== null && (float) $k['latest_target'] > 0) {
                                    $ach = round(((float) $k['latest_value'] / (float) $k['latest_target']) * 100, 1);
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div style="width: 32px; height: 32px; border-radius: 6px; background: <?= hr_e($k['color'] ?? '#1E40AF') ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                            <i class="<?= hr_e($k['icon'] ?? 'fas fa-chart-bar') ?>"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= hr_url('kpi', 'show', ['id' => $k['id']]) ?>">
                                            <?= hr_e($k['name']) ?>
                                        </a>
                                        <br><small class="hr-text-muted"><code><?= hr_e($k['code']) ?></code></small>
                                    </td>
                                    <td><small><?= hr_e($categoryOptions[$k['category']] ?? '') ?></small></td>
                                    <td><?= hr_e($k['unit'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($k['latest_value'] !== null): ?>
                                            <strong><?= hr_num(number_format((float) $k['latest_value'], 2)) ?></strong>
                                        <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($k['latest_target'] !== null): ?>
                                            <?= hr_num(number_format((float) $k['latest_target'], 2)) ?>
                                        <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ach !== null): ?>
                                            <span class="hr-status-badge <?= \App\Software\Hr\Models\KPIValue::achievementClass($ach) ?>">
                                                <?= hr_num($ach) ?>%
                                            </span>
                                        <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td><strong><?= hr_num($k['values_count']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- توزیع دسته -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tags"></i> توزیع دسته‌بندی</h3></div>
                <div class="card-body">
                    <?php if (empty($categoryDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php foreach ($categoryDist as $cd): ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span><?= hr_e($categoryOptions[$cd['category']] ?? $cd['category']) ?></span>
                                <strong><?= hr_num($cd['count']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <!-- مقادیر اخیر -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-history"></i> مقادیر اخیر</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($recentValues)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">مقداری ثبت نشده است.</p></div>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 1rem;">
                            <?php foreach (array_slice($recentValues, 0, 15) as $v): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                    <div class="hr-flex-between">
                                        <div>
                                            <strong><?= hr_e($v['kpi_name']) ?></strong>
                                            <br>
                                            <small class="hr-text-muted">
                                                دوره: <?= hr_e($v['period']) ?> — <?= hr_date($v['period_date'], 'Y/m/d') ?>
                                            </small>
                                        </div>
                                        <div style="text-align: left;">
                                            <strong><?= hr_num(number_format((float) $v['value'], 2)) ?></strong>
                                            <br>
                                            <small class="hr-text-muted"><?= hr_e($v['unit'] ?? '') ?></small>
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

</div>

<script src="/public/assets/js/software/hr.js"></script>