<?php
/**
 * لیست پروژه‌های حمل و نقل (Transportation)
 * مسیر: app/software/or/views/transport/index.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-truck text-primary"></i> مسئله حمل و نقل (Transportation)
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">حمل و نقل</li>
                </ol>
            </nav>
        </div>
        <!-- ✅ لینک به فرم اختصاصی ایجاد پروژه حمل و نقل -->
        <a href="<?= or_url('controller=transport&action=create') ?>" class="btn btn-or-primary">
            <i class="fas fa-plus"></i> پروژه جدید حمل و نقل
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <!-- حالت خالی -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-truck fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هنوز پروژه حمل و نقلی ایجاد نکرده‌اید</h4>
                <p class="text-muted mb-4">برای شروع، اولین مدل حمل و نقل خود را با تعریف مبادی (عرضه) و مقاصد (تقاضا) ایجاد کنید.</p>
                <a href="<?= or_url('controller=transport&action=create') ?>" class="btn btn-or-primary btn-lg">
                    <i class="fas fa-plus"></i> ایجاد اولین پروژه
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- جدول پروژه‌ها -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="or-matrix">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام پروژه</th>
                                <th>عرضه کل</th>
                                <th>تقاضای کل</th>
                                <th>وضعیت توازن</th>
                                <th>هزینه بهینه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <?php 
                                $solution = json_decode($project['solution_data'] ?? '{}', true);
                                $isBalanced = $project['is_balanced'] ?? 0;
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <!-- ✅ لینک مشاهده به کنترلر transport -->
                                        <a href="<?= or_url('controller=transport&action=show&id=' . $project['id']) ?>">
                                            <strong><?= or_e($project['name']) ?></strong>
                                        </a>
                                        <?php if (!empty($project['description'])): ?>
                                            <br><small class="text-muted"><?= or_truncateText($project['description'], 40) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-primary fw-bold"><?= number_format($project['total_supply'] ?? 0) ?></span></td>
                                    <td><span class="text-info fw-bold"><?= number_format($project['total_demand'] ?? 0) ?></span></td>
                                    <td>
                                        <?php if ($isBalanced): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> متوازن</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> نامتوازن</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($solution['optimal_cost']) || !empty($project['optimal_value'])): ?>
                                            <strong class="text-success"><?= number_format($solution['optimal_cost'] ?? $project['optimal_value'], 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="or-badge <?= $project['status'] ?? 'draft' ?>"><?= or_getStatusLabel($project['status'] ?? 'draft') ?></span></td>
                                    <td class="text-muted small"><?= or_showDate($project['updated_at'] ?? $project['created_at']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- ✅ لینک مشاهده به transport/show -->
                                            <a href="<?= or_url('controller=transport&action=show&id=' . $project['id']) ?>" class="btn btn-outline-primary" title="مشاهده و حل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- ✅ لینک ویرایش به transport/edit -->
                                            <a href="<?= or_url('controller=transport&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- ✅ لینک حذف به transport/delete -->
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
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        // ✅ هدایت به کنترلر عمومی project برای حذف
        window.location.href = '<?= or_url("controller=project&action=delete&id=") ?>' + id;
    }
}
</script>