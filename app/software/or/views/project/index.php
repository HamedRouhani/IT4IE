<?php
/**
 * لیست پروژه‌های OR
 * مسیر: app/software/or/views/project/index.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-folder-open text-primary"></i> پروژه‌های OR
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">پروژه‌ها</li>
                </ol>
            </nav>
        </div>
        <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> پروژه جدید
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هنوز پروژه‌ای ایجاد نکرده‌اید</h4>
                <p class="text-muted mb-4">برای شروع، اولین پروژه خود را ایجاد کنید</p>
                <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> ایجاد اولین پروژه
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>نام پروژه</th>
                                <th>نوع مسئله</th>
                                <th>روش حل</th>
                                <th>ابعاد</th>
                                <th>هزینه بهینه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <a href="<?= or_url('controller=project&action=show&id=' . $project['id']) ?>">
                                            <strong><?= htmlspecialchars($project['name']) ?></strong>
                                        </a>
                                        <?php if (!empty($project['description'])): ?>
                                            <br><small class="text-muted">
                                                <?= or_truncateText($project['description'], 50) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= or_getProblemTypeLabel($project['problem_type_code'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($project['method_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?= $project['variables_count'] ?? 0 ?> × <?= $project['constraints_count'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($project['optimal_value'] !== null): ?>
                                            <strong class="text-success">
                                                <?= number_format($project['optimal_value'], 2) ?>
                                            </strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= or_getStatusColor($project['status'] ?? 'draft') ?>">
                                            <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= or_showDate($project['updated_at'] ?? $project['created_at'] ?? '') ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= or_url('controller=project&action=show&id=' . $project['id']) ?>" 
                                               class="btn btn-outline-primary" title="مشاهده">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= or_url('controller=project&action=edit&id=' . $project['id']) ?>" 
                                               class="btn btn-outline-warning" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="حذف"
                                                    onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['name']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟')) {
        window.location.href = '<?= or_url('controller=project&action=delete&id=') ?>' + id;
    }
}
</script>