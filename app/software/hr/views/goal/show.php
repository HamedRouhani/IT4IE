<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-bullseye"></i> <?= hr_e($goal['title']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <span class="hr-status-badge <?= hr_status_class($goal['status']) ?>">
                    <?= hr_e(\App\Software\Hr\Models\Goal::getStatusOptions()[$goal['status']] ?? $goal['status']) ?>
                </span>
                <span class="hr-status-badge <?= hr_priority_class($goal['priority']) ?>">
                    <?= hr_priority_label($goal['priority']) ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('goal', 'edit', ['id' => $goal['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('goal') ?>" class="btn-hr-outline">
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
            <small>پیشرفت</small>
            <h3><?= hr_num($goal['progress']) ?>%</h3>
        </div>
        <?php if ($goal['target_value']): ?>
            <div class="hr-stat-card success">
                <small>هدف / فعلی</small>
                <h3><?= hr_num($goal['current_value']) ?> / <?= hr_num($goal['target_value']) ?></h3>
            </div>
        <?php endif; ?>
        <div class="hr-stat-card warning">
            <small>مهلت</small>
            <h3><?= hr_date($goal['due_date'], 'Y/m/d') ?></h3>
        </div>
        <div class="hr-stat-card">
            <small>وزن</small>
            <h3><?= hr_num($goal['weight']) ?>%</h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات هدف</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">کارمند</th><td><?= hr_e($goal['employee_name'] ?? '—') ?></td></tr>
                            <tr><th>دپارتمان</th><td><?= hr_e($goal['department_name'] ?? '—') ?></td></tr>
                            <tr><th>نوع</th><td><?= hr_e(\App\Software\Hr\Models\Goal::getTypeOptions()[$goal['goal_type']] ?? $goal['goal_type']) ?></td></tr>
                            <tr><th>دسته‌بندی</th><td><?= hr_e($goal['category'] ?? '—') ?></td></tr>
                            <?php if (!empty($goal['parent_title'])): ?>
                                <tr><th>هدف والد</th><td>
                                    <a href="<?= hr_url('goal', 'show', ['id' => $goal['parent_goal_id']]) ?>">
                                        <?= hr_e($goal['parent_title']) ?>
                                    </a>
                                </td></tr>
                            <?php endif; ?>
                            <tr><th>تاریخ شروع</th><td><?= hr_date($goal['start_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>تاریخ پایان</th><td><?= hr_date($goal['due_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>دوره ارزیابی</th><td><?= hr_e($goal['review_period'] ?? '—') ?></td></tr>
                            <?php if (!empty($goal['description'])): ?>
                                <tr><th>توضیحات</th><td><?= nl2br(hr_e($goal['description'])) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($goal['notes'])): ?>
                                <tr><th>یادداشت</th><td><?= nl2br(hr_e($goal['notes'])) ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($children)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sitemap"></i> اهداف فرزند (<?= hr_num(count($children)) ?>)
                        </h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>عنوان</th>
                                    <th>پیشرفت</th>
                                    <th>مهلت</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $c): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= hr_url('goal', 'show', ['id' => $c['id']]) ?>">
                                                <?= hr_e(hr_truncate($c['title'], 50)) ?>
                                            </a>
                                        </td>
                                        <td><strong><?= hr_num($c['progress']) ?>%</strong></td>
                                        <td><?= hr_date($c['due_date'], 'Y/m/d') ?></td>
                                        <td>
                                            <span class="hr-status-badge <?= hr_status_class($c['status']) ?>">
                                                <?= hr_e(\App\Software\Hr\Models\Goal::getStatusOptions()[$c['status']] ?? $c['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie"></i> وضعیت کلی</h3></div>
                <div class="card-body">
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="font-size: 3rem; font-weight: bold; color: var(--hr-primary);">
                            <?= hr_num($goal['progress']) ?>%
                        </div>
                        <small class="hr-text-muted">پیشرفت کلی</small>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 8px; height: 12px; overflow: hidden;">
                        <div style="width: <?= (int) $goal['progress'] ?>%; background: var(--hr-primary); height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>