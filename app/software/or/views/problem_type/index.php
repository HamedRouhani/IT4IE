<?php
/**
 * لیست انواع مسئله
 * مسیر: app/software/or/views/problem_type/index.php
 */

// نگاشت کد نوع مسئله به کنترلر اختصاصی آن
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
                <i class="fas fa-cubes text-primary"></i> انواع مسئله
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">انواع مسئله</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i> 
        نوع مسئله مورد نظر خود را انتخاب کنید تا به فرم ایجاد مدل اختصاصی آن هدایت شوید.
    </div>

    <div class="row g-4">
        <?php foreach ($problemTypes as $pt): ?>
            <?php 
            $code = $pt['code'] ?? '';
            $targetController = $controllerMap[$code] ?? null;
            $iconClass = $iconMap[$code] ?? 'fas fa-cube text-secondary';
            
            // اگر کنترلر اختصاصی برای این نوع مسئله وجود داشت، آن را نمایش بده
            if ($targetController): 
            ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= or_url('controller=' . $targetController . '&action=create') ?>" 
                       class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 hover-card transition-all">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="<?= $iconClass ?> fa-3x"></i>
                                </div>
                                <h5 class="card-title mb-2 text-dark">
                                    <?= or_e($pt['name_fa']) ?>
                                </h5>
                                <p class="card-text text-muted small mb-3">
                                    <?= or_e($pt['description'] ?? 'برای شروع روی این کارت کلیک کنید') ?>
                                </p>
                                <span class="badge bg-light text-dark border">
                                    کد: <?= $code ?>
                                </span>
                                <div class="mt-3">
                                    <span class="btn btn-sm btn-or-primary">
                                        <i class="fas fa-plus"></i> ایجاد مدل جدید
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>