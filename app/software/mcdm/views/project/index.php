<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">پروژه‌های تصمیم‌گیری</h1>
    <a href="<?= mcdm_url('controller=project&action=create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> پروژه جدید
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نام پروژه</th>
                    <th>روش</th>
                    <th>فاز</th>
                    <th>معیارها</th>
                    <th>گزینه‌ها</th>
                    <th>به‌روزرسانی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">پروژه‌ای وجود ندارد.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $i => $prj): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= mcdm_e($prj['name']) ?></td>
                            <td><?= mcdm_e($prj['method_name'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= mcdm_getPhaseColor($prj['phase']) ?>">
                                    <?= mcdm_getPhaseLabel($prj['phase']) ?>
                                </span>
                            </td>
                            <td><?= (int)($prj['criteria_count'] ?? 0) ?></td>
                            <td><?= (int)($prj['alternatives_count'] ?? 0) ?></td>
                            <td><?= mcdm_showDate($prj['updated_at'] ?? null) ?></td>
                            <td>
                                <a href="<?= mcdm_url('controller=project&action=show&id=' . (int)$prj['id']) ?>"
                                   class="btn btn-sm btn-outline-primary">مشاهده</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>