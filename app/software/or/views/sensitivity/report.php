<?php
/**
 * گزارش تحلیل حساسیت یکپارچه
 * مسیر: app/software/or/views/sensitivity/report.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-area text-primary"></i> تحلیل حساسیت
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=sensitivity') ?>">تحلیل حساسیت</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?= or_url('controller=sensitivity') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    <?php if (($analysis['status'] ?? '') !== 'success'): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <?= $analysis['message'] ?? 'تحلیل حساسیت برای این مسئله موجود نیست.' ?>
        </div>
    <?php else: ?>
        
        <!-- هدر اطلاعات پروژه -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">نام پروژه</h6>
                        <h5 class="mb-0"><?= or_e($project['name']) ?></h5>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">نوع مسئله</h6>
                        <span class="badge bg-info fs-6"><?= $analysis['type'] ?? $problemType ?></span>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">مقدار بهینه</h6>
                        <h5 class="mb-0 text-success"><?= number_format($project['optimal_value'] ?? 0, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        // ✅ استفاده از include به جای renderPartial
        $partialFile = __DIR__ . '/_lp_analysis.php';
        ?>

        <?php if ($problemType === 'LP'): ?>
            <!-- تحلیل حساسیت برنامه‌ریزی خطی -->
            <?php include __DIR__ . '/_lp_analysis.php'; ?>
            
        <?php elseif ($problemType === 'TRANS' || $problemType === 'TRANSSHIP'): ?>
            <!-- تحلیل حساسیت حمل و نقل / ترانشیپمنت -->
            <?php include __DIR__ . '/_transport_analysis.php'; ?>
            
        <?php elseif ($problemType === 'ASSIGN'): ?>
            <!-- تحلیل حساسیت تخصیص -->
            <?php include __DIR__ . '/_assignment_analysis.php'; ?>
            
        <?php elseif ($problemType === 'SHORTEST'): ?>
            <!-- تحلیل حساسیت کوتاه‌ترین مسیر -->
            <?php include __DIR__ . '/_shortest_analysis.php'; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                تحلیل حساسیت برای نوع مسئله <strong><?= or_e($problemType) ?></strong> هنوز پیاده‌سازی نشده است.
            </div>
        <?php endif; ?>

        <!-- دکمه‌های عملیات -->
        <div class="d-flex justify-content-between mt-4">
            <a href="<?= or_url('controller=sensitivity') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> بازگشت به لیست
            </a>
            <button class="btn btn-or-primary" onclick="window.print()">
                <i class="fas fa-print"></i> چاپ گزارش
            </button>
        </div>

    <?php endif; ?>
</div>