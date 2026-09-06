<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-line text-success me-2"></i>StatLab Analyzer</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/software">نرم‌افزارها</a></li>
                    <li class="breadcrumb-item active">StatLab</li>
                </ol>
            </nav>
        </div>
        <span class="badge bg-success fs-6">نسخه 1.0</span>
    </div>

    <!-- کارت‌های آماری -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted">پروژه‌های من</small>
                <h3 class="mb-0 text-primary"><?= (int)$stats['total_projects'] ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted">تکمیل شده</small>
                <h3 class="mb-0 text-success"><?= (int)$stats['completed'] ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted">مجموعه داده‌ها</small>
                <h3 class="mb-0 text-info"><?= (int)$stats['total_datasets'] ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted">تحلیل‌های انجام شده</small>
                <h3 class="mb-0 text-warning"><?= (int)$stats['total_results'] ?></h3>
            </div>
        </div>
    </div>

    <!-- دسترسی سریع -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="<?= stat_url('controller=descriptive') ?>" class="card border-0 shadow-sm p-4 text-decoration-none h-100">
                <i class="fas fa-chart-simple fa-2x text-primary mb-2"></i>
                <h5 class="mb-1">آمار توصیفی</h5>
                <p class="text-muted small mb-0">Mean, Median, Std, Skewness, Histogram, BoxPlot</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= stat_url('controller=distribution') ?>" class="card border-0 shadow-sm p-4 text-decoration-none h-100">
                <i class="fas fa-dice fa-2x text-info mb-2"></i>
                <h5 class="mb-1">توزیع‌های احتمال</h5>
                <p class="text-muted small mb-0">Normal, Poisson, Binomial, Exponential, Weibull</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= stat_url('controller=hypothesis') ?>" class="card border-0 shadow-sm p-4 text-decoration-none h-100">
                <i class="fas fa-scale-balanced fa-2x text-warning mb-2"></i>
                <h5 class="mb-1">آزمون فرض</h5>
                <p class="text-muted small mb-0">t-test, ANOVA, Chi-Square, Mann-Whitney</p>
            </a>
        </div>
    </div>

    <!-- آخرین پروژه‌ها -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>آخرین پروژه‌ها</h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentProjects)): ?>
                <p class="text-muted text-center py-4 mb-0">
                    هنوز پروژه‌ای ایجاد نکرده‌اید.
                    <a href="<?= stat_url('controller=project&action=create') ?>">ایجاد پروژه جدید</a>
                </p>
            <?php else: ?>
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>نام پروژه</th>
                            <th>نوع تحلیل</th>
                            <th>وضعیت</th>
                            <th>آخرین به‌روزرسانی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $p): ?>
                            <tr>
                                <td>
                                    <a href="<?= stat_url('controller=project&action=show&id=' . $p['id']) ?>">
                                        <?= stat_e($p['name']) ?>
                                    </a>
                                </td>
                                <td><?= stat_e($p['analysis_type_code'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'secondary' ?>">
                                        <?= stat_getStatusLabel($p['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= stat_e($p['updated_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>