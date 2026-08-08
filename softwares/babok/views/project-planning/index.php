<?php
$pageTitle = 'برنامه‌ریزی: ' . htmlspecialchars($project['name']) . ' - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-calendar-check" style="color: var(--success-color);"></i>
        برنامه‌ریزی: <?= htmlspecialchars($project['name']) ?>
    </h2>
    <a href="/babok/public/?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<!-- اطلاعات پروژه -->
<div class="alert alert-info">
    <div class="row">
        <div class="col-md-4">
            <strong>متدلوژی:</strong> <span class="badge badge-primary"><?= ucfirst($project['methodology']) ?></span>
        </div>
        <div class="col-md-4">
            <strong>فاز فعلی:</strong> <span class="badge badge-secondary"><?= match($project['phase']) {
                'initiation' => 'شروع',
                'planning' => 'برنامه‌ریزی',
                'analysis' => 'تحلیل',
                'design' => 'طراحی',
                'implementation' => 'پیاده‌سازی',
                'evaluation' => 'ارزیابی',
                default => $project['phase']
            } ?></span>
        </div>
        <div class="col-md-4">
            <strong>ذی‌نفعان:</strong> <?= $project['stakeholder_count'] ?> نفر
        </div>
    </div>
</div>

<div class="row">
    <!-- ستون راست: وظایف انتخاب‌شده -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-list-check"></i> وظایف انتخاب‌شده
                </div>
                <span class="badge badge-primary"><?= count($selectedTasks) ?> وظیفه</span>
            </div>

            <?php if (empty($selectedTasks)): ?>
                <div class="text-muted text-center" style="padding: 30px 0;">
                    <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p>هیچ وظیفه‌ای انتخاب نشده است.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>وظیفه</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedTasks as $task): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($task['task_code']) ?></strong></td>
                                <td><?= htmlspecialchars($task['task_name']) ?></td>
                                <td>
                                    <select class="form-select form-select-sm status-select" 
                                            data-project="<?= $project['id'] ?>" 
                                            data-task="<?= $task['task_id'] ?>"
                                            onchange="updateStatus(this)"
                                            style="padding: 2px 8px; font-size: 0.8rem; border-radius: 6px; border: 1px solid #ddd;">
                                        <option value="not_started" <?= $task['status'] == 'not_started' ? 'selected' : '' ?>>انجام نشده</option>
                                        <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                                        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                                        <option value="deferred" <?= $task['status'] == 'deferred' ? 'selected' : '' ?>>به تعویق افتاده</option>
                                    </select>
                                </td>
                                <td>
                                    <form action="/babok/public/?route=planning_remove_task" method="POST" style="display:inline">
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این وظیفه اطمینان دارید؟')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ستون چپ: افزودن وظیفه -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-plus-circle"></i> افزودن وظیفه جدید
                </div>
            </div>
            <div class="card-body">
                <form action="/babok/public/?route=planning_add_task" method="POST">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">وظیفه:</label>
                        <select class="form-select" name="task_id" required style="padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="">انتخاب وظیفه...</option>
                            <?php foreach ($allTasks as $task): ?>
                                <option value="<?= $task['id'] ?>">
                                    <?= htmlspecialchars($task['code']) ?> - <?= htmlspecialchars($task['name']) ?>
                                    (<?= htmlspecialchars($task['knowledge_area_code'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle"></i> افزودن به پروژه
                    </button>
                </form>
            </div>
        </div>

        <!-- وظایف پیشنهادی -->
        <div class="card mt-3">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-lightbulb"></i> وظایف پیشنهادی
                </div>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-success w-100" onclick="getRecommendedTasks()">
                    <i class="fas fa-magic"></i> دریافت پیشنهادات
                </button>
                <div id="recommendedTasks" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(select) {
    const projectId = select.dataset.project;
    const taskId = select.dataset.task;
    const status = select.value;
    
    fetch('/babok/public/?route=planning_update_status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `project_id=${projectId}&task_id=${taskId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // به‌روزرسانی صفحه
            location.reload();
        } else {
            alert(data.error || 'خطا در به‌روزرسانی وضعیت');
        }
    })
    .catch(error => {
        alert('خطا در ارتباط با سرور');
    });
}

function getRecommendedTasks() {
    const projectId = <?= $project['id'] ?>;
    const container = document.getElementById('recommendedTasks');
    
    container.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> در حال بارگذاری...</div>';
    
    fetch(`/babok/public/?route=planning_recommended&id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            if (!data.recommended || data.recommended.length === 0) {
                container.innerHTML = '<p class="text-muted">هیچ پیشنهادی وجود ندارد.</p>';
                return;
            }
            
            let html = '<div class="list-group">';
            data.recommended.forEach(task => {
                const isSelected = data.current && data.current.includes(task.id);
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${task.code}</strong> - ${task.name}
                            <br><small class="text-muted">${task.knowledge_area_code || ''}</small>
                        </div>
                        ${isSelected ? 
                            '<span class="badge badge-success">افزوده شده</span>' :
                            `<form action="/babok/public/?route=planning_add_task" method="POST" style="display:inline">
                                <input type="hidden" name="project_id" value="${projectId}">
                                <input type="hidden" name="task_id" value="${task.id}">
                                <button type="submit" class="btn btn-sm btn-primary">افزودن</button>
                            </form>`
                        }
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">خطا در دریافت پیشنهادات</div>';
        });
}
</script>