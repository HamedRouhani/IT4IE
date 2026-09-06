<?php
/**
 * لیست پروژه‌های آماری
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-folder-open text-primary me-2"></i>پروژه‌های آماری من</h3>
        <a href="<?= stat_url('controller=project&action=create') ?>" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> پروژه جدید
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">هنوز پروژه‌ای ایجاد نکرده‌اید</h5>
                <p class="text-muted">برای شروع تحلیل آماری، یک پروژه جدید ایجاد کنید.</p>
                <a href="<?= stat_url('controller=project&action=create') ?>" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> ایجاد پروژه جدید
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($projects as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="<?= stat_url('controller=project&action=show&id=' . $p['id']) ?>" class="text-decoration-none">
                                        <?= stat_e($p['name']) ?>
                                    </a>
                                </h5>
                                <span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'secondary' ?>">
                                    <?= stat_getStatusLabel($p['status']) ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-3">
                                <?= stat_e(mb_substr($p['description'] ?? 'بدون توضیحات', 0, 100)) ?>
                            </p>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="fas fa-database me-1"></i><?= (int)($p['dataset_count'] ?? 0) ?> مجموعه داده</span>
                                <span><i class="fas fa-chart-bar me-1"></i><?= (int)($p['result_count'] ?? 0) ?> تحلیل</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= stat_e($p['updated_at']) ?></small>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= stat_url('controller=project&action=show&id=' . $p['id']) ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteProject(<?= $p['id'] ?>, '<?= stat_e($p['name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟\nاین عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= stat_url('controller=project&action=delete&id=') ?>' + id;
    }
}
</script>