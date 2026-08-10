<?php
/**
 * ویو مشاهده جزئیات پروژه
 * مسیر: app/software/babok/views/projects/view.php
 */
$pageTitle = $project['name'] . ' - BABOK Analyzer';
$activePage = 'projects';
$progressPercentage = $progress['completion_percentage'] ?? 0;
?>

<!-- هدر پروژه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-folder-open"></i> <?= htmlspecialchars($project['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-success">
                <i class="fas fa-calendar-check"></i> برنامه‌ریزی وظایف
            </a>
            <a href="?route=projects_edit&id=<?= $project['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="?route=projects" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="card" style="margin-bottom: 0;">
            <h5 class="text-muted">فاز فعلی</h5>
            <span class="badge badge-secondary" style="font-size: 0.9rem;">
                <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?>
            </span>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 class="text-muted">متدولوژی</h5>
            <span class="badge methodology-<?= $project['methodology'] ?>" style="font-size: 0.9rem;">
                <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?>
            </span>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 class="text-muted">تعداد ذی‌نفعان</h5>
            <strong style="font-size: 1.5rem;"><?= $project['stakeholder_count'] ?></strong>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 class="text-muted">تاریخ ایجاد</h5>
            <strong><?= date('Y-m-d', strtotime($project['created_at'])) ?></strong>
        </div>
    </div>
    
    <?php if (!empty($project['description'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-align-right"></i> توضیحات</h5>
            <p style="margin: 0; line-height: 1.8;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- پیشرفت پروژه -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-chart-pie"></i> پیشرفت پروژه</h3>
    
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--soft-secondary);">
                <?= $progress['total'] ?? 0 ?>
            </div>
            <div class="text-muted">کل وظایف</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #d4edda; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--soft-success);">
                <?= $progress['completed'] ?? 0 ?>
            </div>
            <div class="text-muted">تکمیل شده</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--soft-warning);">
                <?= $progress['in_progress'] ?? 0 ?>
            </div>
            <div class="text-muted">در حال انجام</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #f8d7da; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--soft-danger);">
                <?= $progress['not_started'] ?? 0 ?>
            </div>
            <div class="text-muted">انجام نشده</div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span>پیشرفت کلی</span>
            <strong><?= $progressPercentage ?>%</strong>
        </div>
        <div class="progress" style="height: 12px;">
            <div class="progress-bar" style="width: <?= $progressPercentage ?>%; background: var(--soft-success);"></div>
        </div>
    </div>
</div>

<!-- لیست وظایف پروژه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف پروژه (<?= count($tasks) ?>)
        </div>
        <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> افزودن وظیفه
        </a>
    </div>
    
    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هنوز وظیفه‌ای به این پروژه اضافه نشده است.</p>
            <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> افزودن وظیفه
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام وظیفه</th>
                        <th>حوزه دانشی</th>
                        <th>وضعیت</th>
                        <th>تاریخ شروع</th>
                        <th>تاریخ تکمیل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($task['task_code']) ?></span></td>
                        <td>
                            <a href="?route=tasks_view&id=<?= $task['task_id'] ?>" style="color: var(--soft-secondary); text-decoration: none;">
                                <?= htmlspecialchars($task['task_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($task['knowledge_area_name']) ?></td>
                        <td>
                            <span class="badge status-<?= str_replace('_', '-', $task['status']) ?>">
                                <?= \App\Software\Babok\Helpers\Utils::statusLabel($task['status']) ?>
                            </span>
                        </td>
                        <td><?= $task['started_at'] ? date('Y-m-d', strtotime($task['started_at'])) : '-' ?></td>
                        <td><?= $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>