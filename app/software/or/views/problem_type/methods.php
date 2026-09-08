<?php
/**
 * لیست روش‌های حل برای یک نوع مسئله
 * مسیر: app/software/or/views/problem_type/methods.php
 */

$type_to_controller = [
    1 => 'transport',
    2 => 'assignment',
    3 => 'transship',
    4 => 'shortest',
    5 => 'simplex',
];
$target_controller = $type_to_controller[$problem_type['id']] ?? 'dashboard';
?>

<div class="container-fluid py-3 py-md-4">
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= or_url('controller=problem_type') ?>">انواع مسئله</a>
            </li>
            <li class="breadcrumb-item active">
                <?= or_e($problem_type['name_fa']) ?>
            </li>
        </ol>
    </nav>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 py-md-3">
            <h3 class="mb-0 h6">
                <i class="fas fa-tools text-primary me-2"></i>
                انتخاب روش حل برای مسئله: 
                <span class="badge bg-primary"><?= or_e($problem_type['name_fa']) ?></span>
            </h3>
        </div>
        <div class="card-body p-3 p-md-4">
            <p class="text-muted small mb-4">
                <?= or_e($problem_type['description'] ?? '') ?>
            </p>
            
            <?php if (empty($methods)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    روشی برای این نوع مسئله تعریف نشده است.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($methods as $method): 
                        $is_selected = ($selected_method_id == $method['id']);
                    ?>
                        <div class="col-12 col-md-6">
                            <div class="card h-100 <?= $is_selected ? 'or-method-selected' : '' ?>">
                                <div class="card-body p-3 p-md-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0 h6">
                                            <span class="badge bg-info me-2"><?= or_e($method['code']) ?></span>
                                            <?= or_e($method['name_fa']) ?>
                                        </h5>
                                        <?php if ($is_selected): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i> انتخاب شده
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted d-block mb-2 fst-italic">
                                        <?= or_e($method['name_en']) ?>
                                    </small>
                                    <p class="card-text small text-muted mb-0">
                                        <?= mb_substr(or_e($method['description'] ?? ''), 0, 150) ?>...
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent p-3 pt-0 d-flex gap-2">
                                    <a href="<?= or_url('controller=method&action=show&id=' . (int)$method['id']) ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="d-none d-md-inline ms-1">جزئیات</span>
                                    </a>
                                    <a href="<?= or_url('controller=' . $target_controller . '&action=create&method_id=' . (int)$method['id'] . '&problem_type_id=' . (int)$problem_type['id']) ?>" 
                                       class="btn btn-sm <?= $is_selected ? 'btn-success' : 'btn-primary' ?> flex-grow-1">
                                        <i class="fas fa-plus-circle me-1"></i>
                                        ایجاد پروژه با این روش
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>