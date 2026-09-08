<?php
/**
 * لیست انواع مسئله - صفحه اصلی
 * مسیر: app/software/or/views/problem_type/index.php
 */

// نگاشت کد نوع مسئله به کنترلر اختصاصی آن
$controllerMap = [
    'LP'        => 'simplex',
    'TRANS'     => 'transport',
    'ASSIGN'    => 'assignment',
    'TRANSSHIP' => 'transship',
    'SHORTEST'  => 'shortest',
    'QUEUEING'  => 'queueing',
    'MONTE_CARLO' => 'monte_carlo',
    'MARKOV'    => 'markov',
    'GAME_THEORY' => 'game_theory',
    'DUAL'      => 'dual',
    'ILP'       => 'ilp',
];

// آیکون‌های اختصاصی برای هر نوع مسئله
$iconMap = [
    'LP'        => 'fas fa-chart-line text-danger',
    'TRANS'     => 'fas fa-truck text-primary',
    'ASSIGN'    => 'fas fa-users-cog text-info',
    'TRANSSHIP' => 'fas fa-project-diagram text-warning',
    'SHORTEST'  => 'fas fa-route text-success',
    'QUEUEING'  => 'fas fa-people-line text-primary',
    'MONTE_CARLO' => 'fas fa-dice text-warning',
    'MARKOV'    => 'fas fa-project-diagram text-info',
    'GAME_THEORY' => 'fas fa-chess text-danger',
    'DUAL'      => 'fas fa-balance-scale-right text-success',
    'ILP'       => 'fas fa-cubes text-dark',
];
?>

<div class="container-fluid py-3 py-md-4">
    <!-- هدر صفحه -->
    <div class="or-page-header">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-cubes text-primary me-2"></i> انواع مسئله
            </h3>
            <small class="text-muted">OR Analyzer - انتخاب نوع مسئله برای ایجاد مدل</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="alert alert-info py-2 small mb-4">
        <i class="fas fa-info-circle me-1"></i>
        نوع مسئله مورد نظر خود را انتخاب کنید تا به فرم ایجاد مدل اختصاصی آن هدایت شوید.
    </div>

    <div class="row g-3 g-md-4">
        <?php foreach ($problemTypes as $pt): ?>
            <?php 
            $code = $pt['code'] ?? '';
            $targetController = $controllerMap[$code] ?? null;
            $iconClass = $iconMap[$code] ?? 'fas fa-cube text-secondary';
            
            // اگر کنترلر اختصاصی برای این نوع مسئله وجود داشت، آن را نمایش بده
            if ($targetController): 
            ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <!-- در حلقه foreach که کارت‌ها را می‌سازد، لینک را اینگونه قرار دهید: -->
                    <a href="<?= or_url('controller=problem_type&action=create&type=' . $code) ?>" 
                        class="text-decoration-none or-problem-card-link">
                        <div class="card border-0 shadow-sm h-100 or-problem-card">
                            <div class="card-body text-center p-3 p-md-4">
                                <div class="or-problem-icon mb-3">
                                    <i class="<?= $iconClass ?> fa-3x"></i>
                                </div>
                                <h5 class="card-title mb-2 text-dark fw-bold">
                                    <?= or_e($pt['name_fa']) ?>
                                </h5>
                                <p class="card-text text-muted small mb-3">
                                    <?= or_e($pt['description'] ?? 'برای شروع روی این کارت کلیک کنید') ?>
                                </p>
                                <span class="badge bg-light text-dark border mb-3 d-inline-block">
                                    کد: <?= or_e($code) ?>
                                </span>
                                <div class="mt-2">
                                    <span class="btn btn-sm btn-or-primary">
                                        <i class="fas fa-plus me-1"></i> ایجاد مدل جدید
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