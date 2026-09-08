<?php
/**
 * لیست مسائل برای یک روش خاص
 * مسیر: app/software/or/views/problem_type/list.php
 */

$type_to_controller = [
    1 => 'transport',
    2 => 'assignment',
    3 => 'transship',
    4 => 'shortest',
    5 => 'simplex',
];
?>

<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-sitemap text-primary me-2"></i>
                انتخاب نوع مسئله
            </h3>
            <?php if (!empty($method_info)): ?>
                <small class="text-muted d-block">
                    مسائل سازگار با روش: <?= or_e($method_info['name_fa']) ?>
                </small>
            <?php endif; ?>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <?php if (empty($problem_types)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            نوع مسئله‌ای تعریف نشده است.
        </div>
    <?php else: ?>
        <div class="row g-3 g-md-4">
            <?php foreach ($problem_types as $type): 
                $is_recommended = ($filtered_type == $type['id']);
                $controller = $type_to_controller[$type['id']] ?? 'dashboard';
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 <?= $is_recommended ? 'or-problem-recommended' : '' ?>">
                        <?php if ($is_recommended): ?>
                            <div class="card-header bg-success text-white text-center py-2">
                                <small><i class="fas fa-star me-1"></i>پیشنهادی برای روش انتخابی</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body p-3 p-md-4">
                            <h5 class="card-title mb-3">
                                <span class="badge bg-secondary me-2"><?= or_e($type['code']) ?></span>
                                <?= or_e($type['name_fa']) ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <?= or_e($type['description'] ?? '') ?>
                            </p>
                        </div>
                        
                        <div class="card-footer bg-transparent p-3 pt-0">
                            <!-- گام بعدی: انتخاب روش از بین روش‌های این مسئله -->
                            <a href="<?= or_url('controller=problem_type&action=methods&problem_type_id=' . (int)$type['id'] . '&method_id=' . (int)($method_id ?? 0)) ?>" 
                               class="btn <?= $is_recommended ? 'btn-success' : 'btn-outline-primary' ?> w-100">
                                <i class="fas fa-arrow-left me-2"></i>
                                مشاهده روش‌های حل
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-2"></i>
            بازگشت به داشبورد
        </a>
    </div>
</div>