<?php
/**
 * ویو لیست پروژه‌ها
 * مسیر: app/software/babok/views/projects/index.php
 */
$pageTitle = 'مدیریت پروژه‌ها - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-folder-open"></i> مدیریت پروژه‌ها
        </div>
        <a href="?route=projects_create" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> پروژه جدید
        </a>
    </div>
    
    <?php if (empty($projects)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 10px;">هیچ پروژه‌ای تعریف نشده است.</p>
            <a href="?route=projects_create" class="btn btn-primary">ایجاد اولین پروژه</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام پروژه</th>
                        <th>متدولوژی</th>
                        <th>فاز</th>
                        <th>ذی‌نفعان</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $index => $project): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><strong><?= htmlspecialchars($project['name']) ?></strong></td>
                        <td>
                            <span class="badge methodology-<?= $project['methodology'] ?>">
                                <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?>
                            </span>
                        </td>
                        <td><?= $project['stakeholder_count'] ?></td>
                        <td><?= date('Y-m-d', strtotime($project['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-sm btn-success" title="برنامه‌ریزی">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                                <a href="?route=projects_edit&id=<?= $project['id'] ?>" class="btn btn-sm btn-warning" title="ویرایش">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?route=projects_delete&id=<?= $project['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="حذف"
                                   onclick="return confirm('آیا از حذف این پروژه اطمینان دارید؟ این عملیات قابل بازگشت نیست.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>