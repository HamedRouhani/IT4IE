<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= mcdm_url('controller=method') ?>">روش‌ها</a></li>
        <li class="breadcrumb-item active"><?= mcdm_e($method['name']) ?></li>
    </ol>
</nav>

<div class="card mb-3"><div class="card-body">
    <h1 class="h3"><?= mcdm_e($method['name_fa'] ?? $method['name']) ?></h1>
    <span class="badge bg-secondary mb-2"><?= mcdm_getMethodCategoryLabel($method['category'] ?? '') ?></span>
    <p><?= nl2br(mcdm_e($method['description'] ?? '')) ?></p>
</div></div>

<?php if (!empty($steps)): ?>
    <div class="card"><div class="card-header">گام‌های اجرا</div>
        <ol class="list-group list-group-numbered list-group-flush">
            <?php foreach ($steps as $s): ?>
                <li class="list-group-item">
                    <div class="fw-bold"><?= mcdm_e($s['name']) ?></div>
                    <div class="small text-muted"><?= mcdm_e($s['description'] ?? '') ?></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
<?php endif; ?>