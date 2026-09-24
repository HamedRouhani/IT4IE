<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-money-bill-wave"></i> گزارش جبران خدمات
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کل احکام: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong><?= hr_num($stats['active']) ?></strong>
                — کل payroll: <strong><?= hr_money($stats['total_payroll']) ?></strong>
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
            <small><i class="fas fa-calculator"></i> میانگین حقوق</small>
            <h3 style="font-size: 1.1rem;"><?= hr_money($stats['avg_salary']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-arrow-up"></i> حداکثر</small>
            <h3 style="font-size: 1.1rem;"><?= hr_money($stats['max_salary']) ?></h3>
        </div>
        <div class="hr-stat-card critical">
            <small><i class="fas fa-arrow-down"></i> حداقل</small>
            <h3 style="font-size: 1.1rem;"><?= hr_money($stats['min_salary']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-money-check"></i> کل payroll</small>
            <h3 style="font-size: 1.1rem;"><?= hr_money($stats['total_payroll']) ?></h3>
        </div>
    </div>

    <!-- حقوق بر اساس دپارتمان -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> حقوق بر اساس دپارتمان</h3></div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($byDepartment)): ?>
                <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">اطلاعاتی موجود نیست.</p></div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="hr-table">
                        <thead>
                            <tr>
                                <th>دپارتمان</th>
                                <th>تعداد</th>
                                <th>میانگین حقوق</th>
                                <th>جمع حقوق</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byDepartment as $d): ?>
                                <tr>
                                    <td><strong><?= hr_e($d['department_name'] ?? '—') ?></strong></td>
                                    <td><?= hr_num($d['count']) ?></td>
                                    <td><?= hr_money($d['avg_salary']) ?></td>
                                    <td><strong><?= hr_money($d['total_salary']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- لیست احکام -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list"></i> احکام حقوقی</h3></div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($compensations)): ?>
                <div class="hr-empty-state" style="padding: 2rem 1rem;"><p class="hr-text-muted">حکمی ثبت نشده است.</p></div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="hr-table">
                        <thead>
                            <tr>
                                <th>کارمند</th>
                                <th>دپارتمان</th>
                                <th>از تاریخ</th>
                                <th>تا تاریخ</th>
                                <th>حقوق پایه</th>
                                <th>جمع کل</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compensations as $c): ?>
                                <tr>
                                    <td>
                                        <a href="<?= hr_url('compensation', 'show', ['id' => $c['id']]) ?>">
                                            <?= hr_e(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?>
                                        </a>
                                    </td>
                                    <td><?= hr_e($c['department_name'] ?? '—') ?></td>
                                    <td><?= hr_date($c['effective_date'], 'Y/m/d') ?></td>
                                    <td>
                                        <?php if (!empty($c['end_date'])): ?>
                                            <?= hr_date($c['end_date'], 'Y/m/d') ?>
                                        <?php else: ?>
                                            <span class="hr-status-badge hr-status-active">فعلی</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= hr_money($c['base_salary']) ?></td>
                                    <td><strong><?= hr_money($c['total_fixed']) ?></strong></td>
                                    <td>
                                        <?php if (empty($c['end_date']) || $c['end_date'] >= date('Y-m-d')): ?>
                                            <span class="hr-status-badge hr-status-active">فعال</span>
                                        <?php else: ?>
                                            <span class="hr-status-badge hr-status-inactive">منقضی</span>
                                        <?php endif; ?>
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

<script src="/public/assets/js/software/hr.js"></script>