<?php
/**
 * ویو برنامه‌ریزی پروژه
 * مسیر: app/software/babok/views/project-planning/index.php
 */
$pageTitle = 'برنامه‌ریزی پروژه: ' . $project['name'] . ' - BABOK Analyzer';
$activePage = 'projects';

// گروه‌بندی وظایف بر اساس حوزه دانشی
$groupedTasks = [];
foreach ($allTasks as $task) {
    $kaCode = $task['knowledge_area_code'] ?? 'نامشخص';
    $kaName = $task['knowledge_area_name'] ?? 'نامشخص';
    $groupedTasks[$kaCode] = [
        'name' => $kaName,
        'tasks' => array_merge($groupedTasks[$kaCode]['tasks'] ?? [], [$task])
    ];
}

// لیست task_id های انتخاب شده
$selectedTaskIds = array_column($selectedTasks, 'task_id');
?>

<!-- هدر پروژه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-calendar-check"></i> برنامه‌ریزی پروژه: <?= htmlspecialchars($project['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> مشاهده پروژه
            </a>
            <a href="?route=projects" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <div class="d-flex gap-2 flex-wrap">
        <div style="padding: 10px 20px; background: #e8f4fd; border-radius: 8px;">
            <strong><?= count($selectedTasks) ?></strong> وظیفه انتخاب شده
        </div>
        <div style="padding: 10px 20px; background: #d4edda; border-radius: 8px;">
            <strong><?= count(array_filter($selectedTasks, fn($t) => $t['status'] === 'completed')) ?></strong> تکمیل شده
        </div>
        <div style="padding: 10px 20px; background: #fff3cd; border-radius: 8px;">
            <strong><?= count(array_filter($selectedTasks, fn($t) => $t['status'] === 'in_progress')) ?></strong> در حال انجام
        </div>
    </div>
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="alert-software warning">
        <i class="fas fa-info-circle"></i>
        <span>
            شما در حالت مهمان هستید. برای افزودن وظایف یا تغییر وضعیت، ابتدا 
            <a href="/login" style="color: var(--soft-secondary);">وارد شوید</a>.
        </span>
    </div>
<?php endif; ?>

<!-- وظایف انتخاب شده -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-check-square"></i> وظایف پروژه
        </div>
    </div>
    
    <?php if (empty($selectedTasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i>
            <p>هنوز وظیفه‌ای به این پروژه اضافه نشده است. از بخش زیر وظایف مورد نظر را اضافه کنید.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">کد</th>
                        <th>نام وظیفه</th>
                        <th>حوزه دانشی</th>
                        <th style="width: 180px;">وضعیت</th>
                        <th style="width: 120px;">عملیات</th>
                    </tr>
                </thead>
                <tbody id="selectedTasksTable">
                    <?php foreach ($selectedTasks as $task): ?>
                    <tr data-task-id="<?= $task['task_id'] ?>">
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($task['task_code']) ?></span></td>
                        <td>
                            <a href="?route=tasks_view&id=<?= $task['task_id'] ?>" style="color: var(--soft-secondary); text-decoration: none;">
                                <?= htmlspecialchars($task['task_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($task['knowledge_area_name']) ?></td>
                        <td>
                            <select class="form-control status-select" 
                                    data-project-id="<?= $project['id'] ?>" 
                                    data-task-id="<?= $task['task_id'] ?>"
                                    style="padding: 6px 10px; font-size: 0.85rem;">
                                <option value="not_started" <?= $task['status'] === 'not_started' ? 'selected' : '' ?>>انجام نشده</option>
                                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                                <option value="deferred" <?= $task['status'] === 'deferred' ? 'selected' : '' ?>>به تعویق افتاده</option>
                            </select>
                        </td>
                        <td>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-sm btn-danger remove-task-btn" 
                                        data-project-id="<?= $project['id'] ?>" 
                                        data-task-id="<?= $task['task_id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- افزودن وظایف جدید -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-plus-circle"></i> افزودن وظایف به پروژه
        </div>
    </div>
    
    <p class="text-muted">وظایف استاندارد BABOK بر اساس حوزه‌های دانشی نمایش داده می‌شوند. وظایف مناسب پروژه خود را انتخاب و اضافه کنید.</p>
    
    <?php foreach ($groupedTasks as $kaCode => $group): ?>
        <div style="margin-bottom: 25px;">
            <h4 style="margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid var(--soft-light);">
                <span class="badge badge-primary"><?= htmlspecialchars($kaCode) ?></span>
                <?= htmlspecialchars($group['name']) ?>
            </h4>
            
            <div class="row" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 10px;">
                <?php foreach ($group['tasks'] as $task): ?>
                    <?php $isSelected = in_array($task['id'], $selectedTaskIds); ?>
                    <div class="card" style="margin-bottom: 0; padding: 15px; border: 2px solid <?= $isSelected ? '#27ae60' : '#e0e0e0' ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <span class="badge badge-secondary"><?= htmlspecialchars($task['code']) ?></span>
                                <h5 style="margin: 8px 0 5px 0; font-size: 0.95rem;"><?= htmlspecialchars($task['name']) ?></h5>
                                <?php if (!empty($task['description'])): ?>
                                    <p class="text-muted" style="font-size: 0.8rem; margin: 0;">
                                        <?= mb_substr(htmlspecialchars($task['description']), 0, 60) ?><?= mb_strlen($task['description']) > 60 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="margin-right: 10px;">
                                <?php if ($isSelected): ?>
                                    <span class="badge badge-success">✓ اضافه شده</span>
                                <?php elseif (isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-sm btn-primary add-task-btn" 
                                            data-project-id="<?= $project['id'] ?>" 
                                            data-task-id="<?= $task['id'] ?>">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
const isUserLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

// هشدار برای کاربر مهمان
function requireLogin() {
    if (!isUserLoggedIn) {
        alert('برای انجام این عملیات لطفاً وارد شوید.');
        window.location.href = '/login';
        return false;
    }
    return true;
}

// حذف وظیفه از پروژه
document.querySelectorAll('.remove-task-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!requireLogin()) return;
        
        const projectId = this.dataset.projectId;
        const taskId = this.dataset.taskId;
        
        if (!confirm('آیا از حذف این وظیفه از پروژه اطمینان دارید؟')) return;
        
        fetch('?route=planning_remove_task', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `project_id=${projectId}&task_id=${taskId}`
        })
        .then(res => res.text())
        .then(() => {
            location.reload();
        })
        .catch(err => {
            console.error('Error:', err);
            alert('خطا در حذف وظیفه');
        });
    });
});

// افزودن وظیفه به پروژه
document.querySelectorAll('.add-task-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!requireLogin()) return;
        
        const projectId = this.dataset.projectId;
        const taskId = this.dataset.taskId;
        
        fetch('?route=planning_add_task', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `project_id=${projectId}&task_id=${taskId}`
        })
        .then(res => res.text())
        .then(() => {
            location.reload();
        })
        .catch(err => {
            console.error('Error:', err);
            alert('خطا در افزودن وظیفه');
        });
    });
});

// به‌روزرسانی وضعیت وظیفه
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        if (!requireLogin()) {
            location.reload();
            return;
        }
        
        const projectId = this.dataset.projectId;
        const taskId = this.dataset.taskId;
        const status = this.value;
        
        fetch('?route=planning_update_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `project_id=${projectId}&task_id=${taskId}&status=${status}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // نمایش پیام موفقیت کوتاه
                const toast = document.createElement('div');
                toast.className = 'alert-software success';
                toast.style.position = 'fixed';
                toast.style.top = '100px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.zIndex = '9999';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> وضعیت به‌روزرسانی شد';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            } else if (data.requires_auth) {
                alert(data.error);
                window.location.href = data.redirect;
            } else {
                alert(data.error || 'خطا در به‌روزرسانی وضعیت');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('خطا در به‌روزرسانی وضعیت');
        });
    });
});
</script>