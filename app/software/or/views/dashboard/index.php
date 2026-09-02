<?php
/**
 * داشبورد ماژول OR Analyzer
 * مسیر: app/software/or/views/dashboard/index.php
 */

// نگاشت کد نوع مسئله به کنترلر اختصاصی
$controllerMap = [
    'LP'        => 'simplex',
    'TRANS'     => 'transport',
    'ASSIGN'    => 'assignment',
    'TRANSSHIP' => 'transship',
    'SHORTEST'  => 'shortest',
];

// آیکون‌های اختصاصی برای هر نوع مسئله
$iconMap = [
    'LP'        => 'fas fa-chart-line text-danger',
    'TRANS'     => 'fas fa-truck text-primary',
    'ASSIGN'    => 'fas fa-users-cog text-info',
    'TRANSSHIP' => 'fas fa-project-diagram text-warning',
    'SHORTEST'  => 'fas fa-route text-success',
];
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-square-root-alt text-primary"></i>
                داشبورد OR Analyzer
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">داشبورد</li>
                </ol>
            </nav>
        </div>
        <div> 
            <!-- ✅ حذف دکمه پروژه جدید (چون کنترلر project حذف شده) -->
            <a href="<?= or_url('controller=problem_type') ?>" class="btn btn-or-primary">
                <i class="fas fa-cubes"></i> انتخاب نوع مسئله
            </a>
        </div> 
    </div>

    <!-- کارت‌های آماری -->
    <div class="row g-3 mb-4">
        <div class="card border-0 shadow-sm" style="cursor: pointer;" onclick="window.location.href='?controller=smart_modeler'">
            <div class="card-body text-center">
                <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                <h5>مدلسازی هوشمند</h5>
                <p class="text-muted small mb-0">توصیف مسئله به فارسی و تشخیص خودکار</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-folder-open fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h6 class="text-muted mb-1">کل پروژه‌ها</h6>
                            <h3 class="mb-0"><?= $stats['total_projects'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h6 class="text-muted mb-1">حل شده</h6>
                            <h3 class="mb-0"><?= $stats['solved_projects'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-cubes fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h6 class="text-muted mb-1">انواع مسئله</h6>
                            <h3 class="mb-0"><?= $stats['problem_types'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-calculator fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 me-3">
                            <h6 class="text-muted mb-1">روش‌های حل</h6>
                            <h3 class="mb-0"><?= $stats['methods'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- آخرین پروژه‌های حل‌شده -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-muted"></i> آخرین پروژه‌های حل‌شده
                    </h5>
                    <a href="<?= or_url('controller=sensitivity') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-area"></i> تحلیل حساسیت
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentProjects)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">هنوز پروژه حل‌شده‌ای ندارید</p>
                            <small class="text-muted">پس از حل اولین مسئله، نتایج در اینجا نمایش داده می‌شود</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>نام پروژه</th>
                                        <th>نوع مسئله</th>
                                        <th>مقدار بهینه</th>
                                        <th>تاریخ</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProjects as $project): 
                                        $problemCode = $project['problem_type_code'] ?? '';
                                        $targetController = $controllerMap[$problemCode] ?? null;
                                        $iconClass = $iconMap[$problemCode] ?? 'fas fa-cube text-secondary';
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= or_e($project['name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="<?= $iconClass ?> me-1"></i>
                                                    <?= or_e($project['problem_type_name'] ?? $problemCode) ?>
                                                </span>
                                            </td>
                                            <td class="text-success fw-bold">
                                                <?= number_format($project['optimal_value'] ?? 0, 2) ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?= or_showDate($project['updated_at'] ?? $project['created_at'] ?? '') ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($targetController): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= or_url('controller=' . $targetController . '&action=show&id=' . $project['id'] . '&tab=result') ?>" 
                                                           class="btn btn-outline-success" title="مشاهده نتایج">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </a>
                                                        <a href="<?= or_url('controller=sensitivity&action=report&id=' . $project['id']) ?>" 
                                                           class="btn btn-outline-info" title="تحلیل حساسیت">
                                                            <i class="fas fa-sliders-h"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
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

        <!-- ستون سمت راست: شروع سریع + انواع مسئله -->
        <div class="col-lg-5">
            <!-- شروع سریع -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket text-primary"></i> شروع سریع
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= or_url('controller=simplex&action=create') ?>" class="btn btn-outline-danger text-start">
                            <i class="fas fa-chart-line"></i> برنامه‌ریزی خطی (Simplex)
                        </a>
                        <a href="<?= or_url('controller=transport&action=create') ?>" class="btn btn-outline-primary text-start">
                            <i class="fas fa-truck"></i> مسئله حمل و نقل
                        </a>
                        <a href="<?= or_url('controller=assignment&action=create') ?>" class="btn btn-outline-info text-start">
                            <i class="fas fa-users-cog"></i> مسئله تخصیص
                        </a>
                        <a href="<?= or_url('controller=transship&action=create') ?>" class="btn btn-outline-warning text-start">
                            <i class="fas fa-project-diagram"></i> ترانشیپمنت
                        </a>
                        <a href="<?= or_url('controller=shortest&action=create') ?>" class="btn btn-outline-success text-start">
                            <i class="fas fa-route"></i> کوتاه‌ترین مسیر
                        </a>
                    </div>
                </div>
            </div>

            <!-- انواع مسئله -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cubes text-info"></i> انواع مسئله
                    </h5>
                    <a href="<?= or_url('controller=problem_type') ?>" class="btn btn-sm btn-outline-secondary">
                        مشاهده همه
                    </a>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($problemTypes as $pt): 
                        $code = $pt['code'] ?? '';
                        $targetController = $controllerMap[$code] ?? null;
                        $iconClass = $iconMap[$code] ?? 'fas fa-cube text-secondary';
                        
                        // فقط مواردی که کنترلر دارند نمایش داده شوند
                        if ($targetController):
                    ?>
                        <a href="<?= or_url('controller=' . $targetController . '&action=create') ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="<?= $iconClass ?> me-2"></i>
                                    <?= htmlspecialchars($pt['name_fa']) ?>
                                </span>
                                <span class="badge bg-light text-dark"><?= $code ?></span>
                            </div>
                        </a>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04);
}
</style>