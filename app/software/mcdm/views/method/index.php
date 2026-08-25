<h1 class="h3 mb-4">روش‌های تصمیم‌گیری چندمعیاره</h1>

<div class="row g-3">
    <?php foreach ($methods as $m): ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= mcdm_e($m['name_fa'] ?? $m['name']) ?></h5>
                    <span class="badge bg-secondary mb-2"><?= mcdm_getMethodCategoryLabel($m['category'] ?? '') ?></span>
                    <p class="card-text small text-muted"><?= mcdm_truncateText($m['description'] ?? '', 90) ?></p>
                    <a href="<?= mcdm_url('controller=method&action=show&id=' . (int)$m['id']) ?>" class="btn btn-sm btn-outline-primary">جزئیات</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($methods)): ?>
        <div class="col-12"><div class="alert alert-info">روشی ثبت نشده است.</div></div>
    <?php endif; ?>
</div>