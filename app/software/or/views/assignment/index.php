<?php
/**
 * لیست پروژه‌های تخصیص (Assignment)
 * مسیر: app/software/or/views/assignment/index.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-users-cog text-primary"></i> مسئله تخصیص (Assignment)
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">تخصیص</li>
                </ol>
            </nav>
        </div>
        <!-- ✅ لینک به فرم اختصاصی ایجاد پروژه تخصیص -->
        <a href="<?= or_url('controller=assignment&action=create') ?>" class="btn btn-or-primary">
            <i class="fas fa-plus"></i> پروژه جدید تخصیص
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <!-- حالت خالی -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-users-cog fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هنوز پروژه تخصیصی ایجاد نکرده‌اید</h4>
                <p class="text-muted mb-4">برای شروع، اولین مدل تخصیص خود را با تعریف عوامل (Agents) و وظایف (Tasks) ایجاد کنید.</p>
                <a href="<?= or_url('controller=assignment&action=create') ?>" class="btn btn-or-primary btn-lg">
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
                                <th>هدف مسئله</th>
                                <th>تعداد عوامل</th>
                                <th>تعداد وظایف</th>
                                <th>هزینه/سود بهینه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $idx => $project): ?>
                                <?php 
                                $solution = json_decode($project['solution_data'] ?? '{}', true);
                                $objLabel = ($project['objective'] ?? 'minimize') === 'minimize' ? 'کمینه‌سازی' : 'بیشینه‌سازی';
                                $objIcon = ($project['objective'] ?? 'minimize') === 'minimize' ? 'fa-arrow-down' : 'fa-arrow-up';
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <!-- ✅ لینک مشاهده به کنترلر اختصاصی assignment -->
                                        <a href="<?= or_url('controller=assignment&action=show&id=' . $project['id']) ?>">
                                            <strong><?= or_e($project['name']) ?></strong>
                                        </a>
                                        <?php if (!empty($project['description'])): ?>
                                            <br><small class="text-muted"><?= or_truncateText($project['description'], 45) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="fas <?= $objIcon ?>"></i> <?= $objLabel ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">
                                            <?= number_format($project['total_supply'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-info fw-bold">
                                            <?= number_format($project['total_demand'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($solution['total_cost']) || !empty($project['optimal_value'])): ?>
                                            <strong class="text-success">
                                                <?= number_format($solution['total_cost'] ?? $project['optimal_value'], 2) ?>
                                            </strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="or-badge <?= $project['status'] ?? 'draft' ?>">
                                            <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= or_showDate($project['updated_at'] ?? $project['created_at']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- ✅ لینک مشاهده به assignment/show -->
                                            <a href="<?= or_url('controller=assignment&action=show&id=' . $project['id']) ?>" 
                                               class="btn btn-outline-primary" title="مشاهده و حل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- ✅ لینک ویرایش به assignment/edit -->
                                            <a href="<?= or_url('controller=assignment&action=edit&id=' . $project['id']) ?>" 
                                               class="btn btn-outline-warning" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- ✅ لینک حذف به assignment/delete -->
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="حذف"
                                                    onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
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
        // ✅ هدایت به کنترلر اختصاصی assignment برای حذف
        window.location.href = '<?= or_url("controller=assignment&action=delete&id=") ?>' + id;
    }
}
</script>