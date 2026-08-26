<?php
/**
 * داشبورد ماژول OR Analyzer
 * مسیر: app/software/or/views/dashboard/index.php
 */
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
        <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> پروژه جدید
        </a>
    </div>

    <!-- کارت‌های آماری -->
    <div class="row g-3 mb-4">
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
        <!-- آخرین پروژه‌ها -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-muted"></i> آخرین پروژه‌ها
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentProjects)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">هنوز پروژه‌ای ایجاد نکرده‌اید</p>
                            <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> اولین پروژه
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>نام پروژه</th>
                                        <th>نوع مسئله</th>
                                        <th>روش حل</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProjects as $project): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= or_url('controller=project&action=show&id=' . $project['id']) ?>">
                                                    <strong><?= htmlspecialchars($project['name']) ?></strong>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= or_getProblemTypeLabel($project['problem_type_code'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($project['method_name'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= or_getStatusColor($project['status'] ?? 'draft') ?>">
                                                    <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?= or_showDate($project['updated_at'] ?? $project['created_at'] ?? '') ?>
                                            </td>
                                            <td>
                                                <a href="<?= or_url('controller=project&action=show&id=' . $project['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

        <!-- انواع مسائل و شروع سریع -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket text-primary"></i> شروع سریع
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= or_url('controller=project&action=create&type=TRANS') ?>" 
                           class="btn btn-outline-primary text-start">
                            <i class="fas fa-truck"></i> مسئله حمل و نقل
                        </a>
                        <a href="<?= or_url('controller=project&action=create&type=ASSIGN') ?>" 
                           class="btn btn-outline-info text-start">
                            <i class="fas fa-users"></i> مسئله تخصیص
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-cubes text-info"></i> انواع مسئله
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($problemTypes as $pt): ?>
                        <a href="<?= or_url('controller=project&action=create&type=' . $pt['code']) ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($pt['name_fa']) ?></span>
                                <span class="badge bg-light text-dark"><?= $pt['code'] ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>