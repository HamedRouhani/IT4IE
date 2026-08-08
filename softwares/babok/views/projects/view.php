<?php
$pageTitle = htmlspecialchars($project['name']) . ' - BABOK Analyzer';
$activePage = 'projects';

$totalTasks = $progress['total'] ?? 0;
$completedTasks = $progress['completed'] ?? 0;
$inProgressTasks = $progress['in_progress'] ?? 0;
$completionPercentage = $progress['completion_percentage'] ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-folder-open" style="color: var(--secondary-color);"></i>
        <?= htmlspecialchars($project['name']) ?>
    </h2>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/babok/public/?route=planning&id=<?= $project['id'] ?>" class="btn btn-success">
            <i class="fas fa-calendar-check"></i> برنامه‌ریزی
        </a>
        <a href="/babok/public/?route=projects_edit&id=<?= $project['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> ویرایش
        </a>
        <a href="/babok/public/?route=projects" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>توضیحات:</strong> <?= nl2br(htmlspecialchars($project['description'] ?? 'بدون توضیحات')) ?></p>
                        <p><strong>متدلوژی:</strong> <span class="badge badge-primary"><?= ucfirst($project['methodology']) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>فاز فعلی:</strong> <span class="badge badge-secondary"><?= match($project['phase']) {
                            'initiation' => 'شروع',
                            'planning' => 'برنامه‌ریزی',
                            'analysis' => 'تحلیل',
                            'design' => 'طراحی',
                            'implementation' => 'پیاده‌سازی',
                            'evaluation' => 'ارزیابی',
                            default => $project['phase']
                        } ?></span></p>
                        <p><strong>تعداد ذی‌نفعان:</strong> <?= $project['stakeholder_count'] ?></p>
                        <p><strong>تاریخ ایجاد:</strong> <?= date('Y-m-d H:i', strtotime($project['created_at'])) ?></p>
                        <p><strong>آخرین بروزرسانی:</strong> <?= date('Y-m-d H:i', strtotime($project['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3 text-center">
            <div class="card-body">
                <h5>پیشرفت پروژه</h5>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color);">
                    <?= $completionPercentage ?>%
                </div>
                <div class="progress" style="height: 20px; margin: 10px 0;">
                    <div class="progress-bar bg-success" style="width: <?= $completionPercentage ?>%;">
                        <?= $completionPercentage ?>%
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <small class="text-muted">کل</small>
                        <br><strong><?= $totalTasks ?></strong>
                    </div>
                    <div class="col-4">
                        <small class="text-success">تکمیل</small>
                        <br><strong><?= $completedTasks ?></strong>
                    </div>
                    <div class="col-4">
                        <small class="text-warning">در حال انجام</small>
                        <br><strong><?= $inProgressTasks ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف پروژه
        </div>
        <div>
            <a href="/babok/public/?route=planning&id=<?= $project['id'] ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus-circle"></i> مدیریت وظایف
            </a>
            <span class="badge badge-primary"><?= count($tasks ?? []) ?> وظیفه</span>
        </div>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هیچ وظیفه‌ای به این پروژه اضافه نشده است.</p>
            <a href="/babok/public/?route=planning&id=<?= $project['id'] ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> افزودن وظیفه
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
                        <td><strong><?= htmlspecialchars($task['task_code']) ?></strong></td>
                        <td><?= htmlspecialchars($task['task_name']) ?></td>
                        <td><?= htmlspecialchars($task['knowledge_area_name'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= match($task['status']) {
                                'not_started' => 'badge-secondary',
                                'in_progress' => 'badge-warning',
                                'completed' => 'badge-success',
                                'deferred' => 'badge-danger',
                                default => 'badge-secondary'
                            } ?>">
                                <?= match($task['status']) {
                                    'not_started' => 'انجام نشده',
                                    'in_progress' => 'در حال انجام',
                                    'completed' => 'تکمیل شده',
                                    'deferred' => 'به تعویق افتاده',
                                    default => $task['status']
                                } ?>
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