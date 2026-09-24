<?php
/**
 * HR Analyzer - داشبورد
 * مسیر: app/software/hr/views/dashboard/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <!-- هدر -->
    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-users"></i> داشبورد منابع انسانی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                سیستم فعال: <strong><?= hr_e($activeSystem['company_name'] ?? 'شرکت من') ?></strong>
                <?php if (!empty($activeSystem['company_size'])): ?>
                    <span class="hr-status-badge hr-status-info" style="margin-right: 0.5rem;">
                        <?= hr_e(hr_company_size_label($activeSystem['company_size'])) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('employee', 'create') ?>" class="btn-hr-primary">
                <i class="fas fa-user-plus"></i> افزودن کارمند
            </a>
            <a href="<?= hr_url('system', 'edit') ?>" class="btn-hr-outline">
                <i class="fas fa-cog"></i> تنظیمات
            </a>
        </div>
    </div>

    <!-- پیام Flash -->
    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- هشدارها -->
    <?php if (!empty($alerts)): ?>
        <div class="hr-mb-4">
            <?php foreach ($alerts as $alert): ?>
                <div class="hr-alert <?= hr_e($alert['type']) ?> hr-mb-2">
                    <i class="<?= hr_e($alert['icon']) ?>"></i>
                    <div style="flex: 1;">
                        <?= hr_e($alert['message']) ?>
                    </div>
                    <?php if (!empty($alert['url'])): ?>
                        <a href="<?= hr_e($alert['url']) ?>" class="btn-hr-outline btn-sm">
                            مشاهده <i class="fas fa-arrow-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های آماری اصلی -->
    <div class="hr-stats-grid hr-mb-4">

        <div class="hr-stat-card">
            <small><i class="fas fa-user-tie"></i> کل کارکنان</small>
            <h3><?= hr_num($stats['total_employees']) ?></h3>
        </div>

        <div class="hr-stat-card success">
            <small><i class="fas fa-user-check"></i> شاغل</small>
            <h3><?= hr_num($stats['active_employees']) ?></h3>
        </div>

        <div class="hr-stat-card warning">
            <small><i class="fas fa-building"></i> دپارتمان‌ها</small>
            <h3><?= hr_num($stats['total_departments']) ?></h3>
        </div>

        <div class="hr-stat-card critical">
            <small><i class="fas fa-briefcase"></i> پست‌های خالی</small>
            <h3><?= hr_num($stats['open_positions']) ?></h3>
        </div>

    </div>

    <!-- کارت‌های KPI -->
    <div class="hr-stats-grid hr-mb-4">
        <?php foreach ($kpiCards as $key => $card): ?>
            <div class="hr-stat-card" style="border-right-color: <?= hr_e($card['color']) ?>;">
                <small>
                    <i class="<?= hr_e($card['icon']) ?>" style="color: <?= hr_e($card['color']) ?>;"></i>
                    <?= hr_e($card['label']) ?>
                </small>
                <h3 style="color: <?= hr_e($card['color']) ?>;">
                    <?= hr_num($card['value']) ?>
                    <small style="font-size: 0.7rem; font-weight: normal; color: #666;">
                        <?= hr_e($card['unit']) ?>
                    </small>
                </h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- گرید اصلی -->
    <div class="hr-main-grid">

        <!-- ستون چپ: آخرین کارکنان -->
        <div>
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-clock"></i>
                        آخرین کارکنان اضافه‌شده
                    </h3>
                    <a href="<?= hr_url('employee') ?>" class="btn-hr-outline btn-sm">
                        مشاهده همه <i class="fas fa-arrow-left"></i>
                    </a>
                </div>

                <?php if (empty($recentEmployees)): ?>
                    <div class="hr-empty-state">
                        <i class="fas fa-user-tie"></i>
                        <h4>هنوز کارمندی ثبت نشده است</h4>
                        <p>اولین کارمند خود را اضافه کنید.</p>
                        <a href="<?= hr_url('employee', 'create') ?>" class="btn-hr-primary">
                            <i class="fas fa-user-plus"></i> افزودن اولین کارمند
                        </a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>کد</th>
                                    <th>نام</th>
                                    <th>دپارتمان</th>
                                    <th>پست</th>
                                    <th>تاریخ استخدام</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEmployees as $emp): ?>
                                    <tr>
                                        <td><code><?= hr_e($emp['employee_code']) ?></code></td>
                                        <td>
                                            <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>"
                                               style="color: var(--hr-primary-dark); font-weight: 600;">
                                                <?= hr_e(hr_full_name($emp)) ?>
                                            </a>
                                        </td>
                                        <td><?= hr_e($emp['department_name'] ?? '—') ?></td>
                                        <td><?= hr_e($emp['position_title'] ?? '—') ?></td>
                                        <td><?= hr_date($emp['hire_date'], 'Y/m/d') ?></td>
                                        <td>
                                            <span class="hr-status-badge <?= hr_status_class($emp['employment_status']) ?>">
                                                <?= hr_employment_status_label($emp['employment_status']) ?>
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

        <!-- ستون راست: توزیع‌ها -->
        <div>
            <!-- توزیع دپارتمان‌ها -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        توزیع دپارتمان‌ها
                    </h3>
                </div>
                <div class="card-body" style="padding: 1rem;">
                    <?php
                    $totalDeptEmployees = array_sum(array_column($departmentDistribution, 'count'));
                    if ($totalDeptEmployees > 0):
                    ?>
                        <?php foreach ($departmentDistribution as $dept): ?>
                            <?php
                            $percent = $totalDeptEmployees > 0 
                                ? round(($dept['count'] / $totalDeptEmployees) * 100, 1) 
                                : 0;
                            ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-2">
                                    <span><?= hr_e($dept['name']) ?></span>
                                    <span>
                                        <strong><?= hr_num($dept['count']) ?></strong>
                                        <small class="hr-text-muted">(<?= hr_num($percent) ?>٪)</small>
                                    </span>
                                </div>
                                <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?= $percent ?>%; height: 100%; background: var(--hr-primary); transition: width 0.5s;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="hr-empty-state" style="padding: 1rem;">
                            <p class="hr-text-muted">هنوز دپارتمانی تعریف نشده است.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- توزیع وضعیت اشتغال -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        وضعیت اشتغال
                    </h3>
                </div>
                <div class="card-body" style="padding: 1rem;">
                    <?php
                    $totalEmp = array_sum($employmentDistribution);
                    if ($totalEmp > 0):
                        $statusLabels = [
                            'active'     => ['label' => 'شاغل', 'class' => 'hr-status-active'],
                            'on_leave'   => ['label' => 'مرخصی', 'class' => 'hr-status-warning'],
                            'suspended'  => ['label' => 'تعلیق', 'class' => 'hr-status-warning'],
                            'terminated' => ['label' => 'خاتمه', 'class' => 'hr-status-danger'],
                            'retired'    => ['label' => 'بازنشسته', 'class' => 'hr-status-inactive'],
                        ];
                        foreach ($statusLabels as $key => $info):
                            $count = $employmentDistribution[$key] ?? 0;
                            $percent = $totalEmp > 0 ? round(($count / $totalEmp) * 100, 1) : 0;
                    ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-2">
                                    <span class="hr-status-badge <?= $info['class'] ?>">
                                        <?= $info['label'] ?>
                                    </span>
                                    <span>
                                        <strong><?= hr_num($count) ?></strong>
                                        <small class="hr-text-muted">(<?= hr_num($percent) ?>٪)</small>
                                    </span>
                                </div>
                                <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?= $percent ?>%; height: 100%; background: var(--hr-primary); transition: width 0.5s;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="hr-empty-state" style="padding: 1rem;">
                            <p class="hr-text-muted">هنوز کارمندی ثبت نشده است.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>