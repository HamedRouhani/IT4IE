<?php
$type_to_controller = [
    1 => 'transport',
    2 => 'assignment',
    3 => 'transship',
    4 => 'shortest',
    5 => 'simplex',
];
$target_controller = $type_to_controller[$problem_type['id']] ?? 'dashboard';
?>

<div class="container mt-4" dir="rtl">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?controller=problem_type&action=list">نوع مسئله</a>
            </li>
            <li class="breadcrumb-item active">
                <?= htmlspecialchars($problem_type['name_fa']) ?>
            </li>
        </ol>
    </nav>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">
                <i class="fas fa-tools me-2"></i>
                انتخاب روش حل برای مسئله: 
                <span class="badge bg-light text-dark"><?= htmlspecialchars($problem_type['name_fa']) ?></span>
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                <?= htmlspecialchars($problem_type['description'] ?? '') ?>
            </p>
            
            <?php if (empty($methods)): ?>
                <div class="alert alert-warning">
                    روشی برای این نوع مسئله تعریف نشده است.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($methods as $method): 
                        $is_selected = ($selected_method_id == $method['id']);
                    ?>
                        <div class="col-md-6">
                            <div class="card h-100 <?= $is_selected ? 'border-success border-3' : '' ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <span class="badge bg-info me-2"><?= $method['code'] ?></span>
                                            <?= htmlspecialchars($method['name_fa']) ?>
                                        </h5>
                                        <?php if ($is_selected): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> انتخاب شده
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        <?= htmlspecialchars($method['name_en']) ?>
                                    </small>
                                    <p class="card-text small">
                                        <?= mb_substr(htmlspecialchars($method['description'] ?? ''), 0, 150) ?>...
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent d-flex gap-2">
                                    <a href="?controller=method&action=show&id=<?= $method['id'] ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-info-circle"></i> جزئیات
                                    </a>
                                    <a href="?controller=<?= $target_controller ?>&action=create&method_id=<?= $method['id'] ?>&problem_type_id=<?= $problem_type['id'] ?>" 
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