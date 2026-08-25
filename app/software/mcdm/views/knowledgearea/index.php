<h1 class="h3 mb-4">حوزه‌های دانشی</h1>
<div class="row g-3">
    <?php foreach ($areas as $ka): ?>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <h5><?= mcdm_e($ka['name_fa'] ?? $ka['name']) ?></h5>
                <p class="small text-muted"><?= mcdm_truncateText($ka['description'] ?? '', 80) ?></p>
                <a href="<?= mcdm_url('controller=knowledgearea&action=show&id=' . (int)$ka['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <?= (int)$ka['method_count'] ?> روش
                </a>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>