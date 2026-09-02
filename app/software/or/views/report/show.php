<?php
/**
 * OR Analyzer - گزارش تفصیلی یک پروژه
 * مسیر: app/software/or/views/report/show.php
 */
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= or_url('') ?>"><i class="fas fa-home"></i> OR Analyzer</a></li>
            <li class="breadcrumb-item"><a href="<?= or_url('controller=report') ?>">گزارش‌ها</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($project['name']) ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i> گزارش تفصیلی پروژه</h5>
            <a href="<?= or_url('controller=report') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> بازگشت
            </a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted small text-uppercase fw-bold">نام پروژه</h6>
                    <p class="fw-bold fs-5"><?= htmlspecialchars($project['name']) ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted small text-uppercase fw-bold">نوع مسئله</h6>
                    <p class="fw-bold fs-5"><?= htmlspecialchars($project['problem_type_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted small text-uppercase fw-bold">وضعیت</h6>
                    <span class="badge bg-<?= $project['status'] === 'solved' ? 'success' : 'warning' ?> fs-6">
                        <?= $project['status'] === 'solved' ? 'حل شده' : $project['status'] ?>
                    </span>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted small text-uppercase fw-bold">مقدار بهینه (Z)</h6>
                    <p class="fw-bold fs-4 text-primary">
                        <?= $project['optimal_value'] !== null ? number_format((float)$project['optimal_value'], 4) : 'محاسبه نشده' ?>
                    </p>
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3"><i class="fas fa-database me-2"></i> داده‌های خام حل (JSON)</h6>
            <?php if (!empty($project['solution_data'])): ?>
                <pre class="bg-light p-3 rounded border"><code><?= htmlspecialchars(json_encode(json_decode($project['solution_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
            <?php else: ?>
                <p class="text-muted">داده‌ای برای این پروژه ثبت نشده است.</p>
            <?php endif; ?>
        </div>
    </div>
</div>