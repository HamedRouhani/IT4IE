<?php
/**
 * لیست پروژه‌های حل‌شده برای تحلیل حساسیت
 * مسیر: app/software/or/views/sensitivity/index.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-sliders-h text-primary"></i> تحلیل حساسیت (Sensitivity Analysis)
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">تحلیل حساسیت</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        در این بخش می‌توانید پروژه‌هایی که قبلاً حل شده‌اند را انتخاب کرده و تأثیر تغییرات در ضرایب تابع هدف یا سمت راست محدودیت‌ها را بر جواب بهینه بررسی کنید.
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هیچ پروژه حل‌شده‌ای یافت نشد</h4>
                <p class="text-muted mb-4">برای انجام تحلیل حساسیت، ابتدا باید یک مسئله (مانند برنامه‌ریزی خطی یا حمل و نقل) را تعریف و حل کنید.</p>
                <a href="<?= or_url('controller=problem_type') ?>" class="btn btn-or-primary btn-lg">
                    <i class="fas fa-plus"></i> انتخاب نوع مسئله و شروع
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="or-matrix">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام پروژه</th>
                                <th>نوع مسئله</th>
                                <th>مقدار بهینه</th>
                                <th>تاریخ حل</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <?php 
                                $solution = json_decode($project['solution_data'] ?? '{}', true);
                                $optimalValue = $project['optimal_value'] ?? ($solution['optimal_value'] ?? 0);
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><strong><?= or_e($project['name']) ?></strong></td>
                                    <td><span class="badge bg-info"><?= or_e($project['problem_type_name'] ?? 'نامشخص') ?></span></td>
                                    <td class="text-success fw-bold"><?= number_format($optimalValue, 2) ?></td>
                                    <td class="text-muted small"><?= or_showDate($project['updated_at']) ?></td>
                                    <td class="text-center">
                                        <a href="<?= or_url('controller=sensitivity&action=report&id=' . $project['id']) ?>" 
                                           class="btn btn-or-primary btn-sm">
                                            <i class="fas fa-chart-bar"></i> مشاهده گزارش حساسیت
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>