<?php
/**
 * لیست مدل‌های برنامه‌ریزی خطی
 * مسیر: app/software/or/views/simplex/index.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-line text-primary"></i> برنامه‌ریزی خطی (Simplex)</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">برنامه‌ریزی خطی</li>
                </ol>
            </nav>
        </div>
        <a href="<?= or_url('controller=simplex&action=create') ?>" class="btn btn-or-primary">
            <i class="fas fa-plus"></i> مدل جدید
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هنوز مدلی ایجاد نکرده‌اید</h4>
                <a href="<?= or_url('controller=simplex&action=create') ?>" class="btn btn-or-primary btn-lg">
                    <i class="fas fa-plus"></i> ایجاد اولین مدل
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
                                <th>نام مدل</th>
                                <th>هدف</th>
                                <th>متغیرها</th>
                                <th>محدودیت‌ها</th>
                                <th>مقدار بهینه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <?php 
                                $modelData = json_decode($project['model_data'] ?? '{}', true);
                                $solution = json_decode($project['solution_data'] ?? '{}', true);
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <!-- ✅ اصلاح: لینک نام پروژه به action=result -->
                                        <a href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=result') ?>">
                                            <strong><?= or_e($project['name']) ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $project['objective'] === 'maximize' ? 'Max' : 'Min' ?></span>
                                    </td>
                                    <td><?= count($modelData['c'] ?? []) ?></td>
                                    <td><?= count($modelData['b'] ?? []) ?></td>
                                    <td>
                                        <?php if (!empty($project['optimal_value']) || !empty($solution['optimal_value'])): ?>
                                            <strong class="text-success"><?= number_format($project['optimal_value'] ?? $solution['optimal_value'], 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="or-badge <?= $project['status'] ?? 'draft' ?>">
                                            <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= or_showDate($project['updated_at'] ?? $project['created_at']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= or_url('controller=simplex&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=result') ?>" class="btn btn-outline-success" title="نتایج">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="حذف" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
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
    if (confirm('آیا از حذف مدل "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= or_url("controller=simplex&action=delete&id=") ?>' + id;
    }
}
</script>