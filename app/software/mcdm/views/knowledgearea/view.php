<h1 class="h3 mb-4"><?= mcdm_e($area['name_fa'] ?? $area['name']) ?></h1>
<p class="text-muted"><?= mcdm_e($area['description'] ?? '') ?></p>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>کد</th><th>روش</th><th>دسته</th><th>عملیات</th></tr></thead>
        <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <td><?= mcdm_e($m['code']) ?></td>
                    <td><?= mcdm_e($m['name_fa'] ?? $m['name']) ?></td>
                    <td><span class="badge bg-secondary"><?= mcdm_getMethodCategoryLabel($m['category'] ?? '') ?></span></td>
                    <td><a href="<?= mcdm_url('controller=method&action=show&id=' . (int)$m['id']) ?>" class="btn btn-sm btn-outline-primary">جزئیات</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div>