<?php
$title = 'گزارش‌ها';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-chart-bar"></i> گزارش‌های پروژه‌ها</h2>
        <div class="breadcrumb">
            <a href="<?= CURRENT_MODULE_URL ?>">داشبورد</a> / گزارش‌ها
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">پروژه‌های تکمیل‌شده</h3>
    
    <?php if (empty($projects)): ?>
    <div class="empty-state">
        <i class="fas fa-file-alt"></i>
        <p>هنوز پروژه‌ای برای گزارش‌گیری وجود ندارد.</p>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=create" class="btn btn-primary">
            ایجاد پروژه جدید
        </a>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>نام پروژه</th>
                    <th>روش</th>
                    <th>فاز</th>
                    <th>معیارها</th>
                    <th>گزینه‌ها</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($project['name']) ?></strong>
                        <br>
                        <small style="color: #666;"><?= htmlspecialchars(mb_substr($project['description'] ?? '', 0, 50)) ?></small>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?= htmlspecialchars($project['method_name'] ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $project['phase'] === 'decision' ? 'success' : 'warning' ?>">
                            <?= htmlspecialchars($project['phase']) ?>
                        </span>
                    </td>
                    <td><?= (int)($project['criteria_count'] ?? 0) ?></td>
                    <td><?= (int)($project['alternatives_count'] ?? 0) ?></td>
                    <td><?= date('Y/m/d', strtotime($project['created_at'])) ?></td>
                    <td>
                        <a href="<?= CURRENT_MODULE_URL ?>?controller=report&action=projectReport&id=<?= $project['id'] ?>" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-file-alt"></i> گزارش
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>