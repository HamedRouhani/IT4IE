<?php ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-route text-primary"></i> کوتاه‌ترین مسیر</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">کوتاه‌ترین مسیر</li>
                </ol>
            </nav>
        </div>
        <a href="<?= or_url('controller=shortest&action=create') ?>" class="btn btn-or-primary">
            <i class="fas fa-plus"></i> پروژه جدید
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-route fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هنوز پروژه‌ای ایجاد نکرده‌اید</h4>
                <p class="text-muted mb-4">یافتن کم‌هزینه‌ترین مسیر بین گره‌ها در یک شبکه.</p>
                <a href="<?= or_url('controller=shortest&action=create') ?>" class="btn btn-or-primary btn-lg">
                    <i class="fas fa-plus"></i> ایجاد اولین پروژه
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="or-matrix">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام پروژه</th>
                                <th>تعداد گره‌ها</th>
                                <th>تعداد یال‌ها</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <?php 
                                $modelData = json_decode($project['model_data'] ?? '{}', true);
                                $nodesCount = count($modelData['nodes'] ?? []);
                                $edgesCount = count($modelData['edges'] ?? []);
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <a href="<?= or_url('controller=shortest&action=show&id=' . $project['id']) ?>">
                                            <strong><?= or_e($project['name']) ?></strong>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-primary"><?= $nodesCount ?></span></td>
                                    <td><span class="badge bg-info"><?= $edgesCount ?></span></td>
                                    <td><span class="or-badge <?= $project['status'] ?? 'draft' ?>"><?= or_getStatusLabel($project['status'] ?? 'draft') ?></span></td>
                                    <td class="text-muted small"><?= or_showDate($project['updated_at'] ?? $project['created_at']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= or_url('controller=shortest&action=show&id=' . $project['id']) ?>" class="btn btn-outline-primary" title="مشاهده"><i class="fas fa-eye"></i></a>
                                            <a href="<?= or_url('controller=shortest&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning" title="ویرایش"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-outline-danger" title="حذف" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
function deleteProject(id, name) {
    if (confirm('آیا از حذف "' + name + '" مطمئن هستید؟')) {
        window.location.href = '<?= or_url("controller=shortest&action=delete&id=") ?>' + id;
    }
}
</script>