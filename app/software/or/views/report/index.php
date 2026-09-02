<?php
/**
 * OR Analyzer - صفحه اصلی گزارش‌ها
 * مسیر: app/software/or/views/report/index.php
 */
?>

<div class="container-fluid py-4">
    
    <!-- مسیر راهنما -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= or_url('') ?>"><i class="fas fa-home"></i> OR Analyzer</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">گزارش‌ها و آمار</li>
        </ol>
    </nav>

    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-chart-pie text-primary me-2"></i> گزارش‌های پروژه‌ها
        </h4>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> بازگشت به داشبورد
        </a>
    </div>

    <!-- جدول گزارشات -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($projects)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th>نام پروژه</th>
                                <th>نوع مسئله</th>
                                <th>وضعیت</th>
                                <th>مقدار بهینه (Z)</th>
                                <th>تاریخ ایجاد</th>
                                <th class="text-center" style="width: 100px;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $index => $project): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?= $index + 1 ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($project['name']) ?></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?= htmlspecialchars($project['problem_type_name'] ?: 'نامشخص') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'secondary';
                                        $statusText = $project['status'];
                                        if ($project['status'] === 'solved') { 
                                            $statusClass = 'success'; 
                                            $statusText = 'حل شده'; 
                                        } elseif ($project['status'] === 'infeasible') { 
                                            $statusClass = 'danger'; 
                                            $statusText = 'غیرموجه'; 
                                        } elseif ($project['status'] === 'draft') { 
                                            $statusClass = 'warning'; 
                                            $statusText = 'پیش‌نویس'; 
                                        } elseif ($project['status'] === 'solving') {
                                            $statusClass = 'primary';
                                            $statusText = 'در حال حل';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        <?= $project['optimal_value'] !== null ? orFormatNumber($project['optimal_value']) : '-' ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('Y/m/d', strtotime($project['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= or_url('controller=report&action=show&id=' . $project['id']) ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="مشاهده گزارش تفصیلی">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- حالت خالی بودن جدول -->
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                    <p class="mb-3">هیچ پروژه‌ای برای نمایش گزارش یافت نشد.</p>
                    <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> ایجاد پروژه جدید
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>