<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">داشبورد تصمیم‌گیری چندمعیاره</h1>
    <?php if ($isAuthenticated): ?>
        <a href="<?= mcdm_url('controller=project&action=create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> پروژه جدید
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">حوزه‌های دانشی</div>
            <div class="h4 mb-0"><?= (int)$stats['knowledge_areas'] ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">روش‌ها</div>
            <div class="h4 mb-0"><?= (int)$stats['methods'] ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">تکنیک‌ها</div>
            <div class="h4 mb-0"><?= (int)$stats['techniques'] ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">پروژه‌های من</div>
            <div class="h4 mb-0"><?= (int)$stats['projects'] ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">حوزه‌های دانشی</div>
            <div class="list-group list-group-flush">
                <?php foreach ($knowledgeAreas as $ka): ?>
                    <a href="<?= mcdm_url('controller=knowledgearea&action=show&id=' . (int)$ka['id']) ?>"
                       class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span><?= mcdm_e($ka['name_fa'] ?? $ka['name']) ?></span>
                        <span class="badge bg-primary"><?= (int)$ka['method_count'] ?> روش</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">پروژه‌های اخیر</div>
            <div class="list-group list-group-flush">
                <?php if (empty($recentProjects)): ?>
                    <div class="list-group-item text-muted text-center py-4">
                        <?php if ($isAuthenticated): ?>
                            هنوز پروژه‌ای ایجاد نکرده‌اید.
                        <?php else: ?>
                            برای مشاهده پروژه‌ها وارد شوید.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentProjects as $prj): ?>
                        <a href="<?= mcdm_url('controller=project&action=show&id=' . (int)$prj['id']) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between">
                            <span><?= mcdm_e($prj['name']) ?></span>
                            <span class="badge bg-<?= mcdm_getPhaseColor($prj['phase']) ?>">
                                <?= mcdm_getPhaseLabel($prj['phase']) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>