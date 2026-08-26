<?php
/**
 * لیست انواع مسئله در ماژول OR
 * مسیر: app/software/or/views/problem_type/index.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-cubes text-primary"></i> انواع مسئله (Problem Types)
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">انواع مسئله</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4">
        <?php if (!empty($problemTypes)): ?>
            <?php foreach ($problemTypes as $pt): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3 ms-3">
                                    <i class="fas fa-project-diagram fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($pt['name_fa']) ?></h5>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($pt['code']) ?></span>
                                </div>
                            </div>
                            
                            <p class="text-muted mb-3" style="min-height: 60px;">
                                <?= htmlspecialchars($pt['description']) ?>
                            </p>

                            <?php if (!empty($pt['methods'])): ?>
                                <h6 class="text-muted small mb-2">روش‌های حل پشتیبانی‌شده:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($pt['methods'] as $method): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($method['name_fa']) ?> 
                                            <small class="text-muted">(<?= $method['code'] ?>)</small>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">هنوز روشی تعریف نشده است</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <a href="<?= or_url('controller=project&action=create&type=' . $pt['code']) ?>" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> ایجاد پروژه جدید در این دسته
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> هیچ نوع مسئله‌ای در سیستم ثبت نشده است.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>