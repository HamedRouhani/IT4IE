<?php
/**
 * ویو مشاهده جزئیات وظیفه
 * مسیر: app/software/babok/views/tasks/view.php
 */
$pageTitle = $task['name'] . ' - BABOK Analyzer';
$activePage = 'tasks';
?>

<!-- هدر وظیفه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> 
            <span class="badge badge-secondary"><?= htmlspecialchars($task['code']) ?></span>
            <?= htmlspecialchars($task['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=recommendations_task&id=<?= $task['id'] ?>" class="btn btn-success">
                <i class="fas fa-lightbulb"></i> پیشنهاد تکنیک هوشمند
            </a>
            <a href="?route=tasks" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <!-- اطلاعات حوزه دانشی -->
    <?php if (!empty($knowledgeArea)): ?>
        <div style="padding: 15px; background: #f0f7ff; border-radius: 8px; margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px; color: var(--soft-primary);">
                <i class="fas fa-sitemap"></i> حوزه دانشی
            </h5>
            <a href="?route=knowledge_areas_view&id=<?= $knowledgeArea['id'] ?>" 
               style="color: var(--soft-secondary); text-decoration: none; font-weight: 600;">
                <?= htmlspecialchars($knowledgeArea['name']) ?>
            </a>
            <span class="badge badge-secondary" style="margin-right: 10px;"><?= htmlspecialchars($knowledgeArea['code']) ?></span>
        </div>
    <?php endif; ?>
    
    <!-- توضیحات -->
    <?php if (!empty($task['description'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-align-right"></i> توضیحات</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- ورودی‌ها -->
    <?php if (!empty($task['inputs'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-sign-in-alt"></i> ورودی‌ها</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($task['inputs'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- خروجی‌ها -->
    <?php if (!empty($task['outputs'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-sign-out-alt"></i> خروجی‌ها</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($task['outputs'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- ذی‌نفعان -->
    <?php if (!empty($task['stakeholders'])): ?>
        <div>
            <h5 style="margin-bottom: 8px;"><i class="fas fa-users"></i> ذی‌نفعان کلیدی</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($task['stakeholders'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- تکنیک‌های مرتبط با وظیفه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> تکنیک‌های مرتبط (<?= count($techniques) ?>)
        </div>
    </div>
    
    <?php 
    // بررسی دسترسی ادمین
    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $isLoggedIn = isset($_SESSION['user_id']);

    if (!$isLoggedIn): ?>
        <div class="alert-software info">
            <i class="fas fa-info-circle"></i>
            <span>
                برای مشاهده قابلیت‌های مدیریتی، ابتدا 
                <a href="/login" style="color: var(--soft-secondary);">وارد شوید</a>.
            </span>
        </div>
    <?php elseif (!$isAdmin): ?>
        <div class="alert-software info">
            <i class="fas fa-lock"></i>
            <span>
                تکنیک‌های استاندارد فقط توسط مدیر سیستم قابل ویرایش هستند.
            </span>
        </div>
    <?php endif; ?>
    
    <div id="techniquesList" class="techniques-grid">
        <?php if (empty($techniques)): ?>
            <p class="text-muted">هیچ تکنیکی به این وظیفه مرتبط نشده است.</p>
        <?php else: ?>
            <?php foreach ($techniques as $tech): ?>
                <div class="technique-card" data-technique-id="<?= $tech['id'] ?>">
                    <span class="technique-category category-<?= $tech['category'] ?>">
                        <?= \App\Software\Babok\Helpers\Utils::categoryLabel($tech['category'] ?? 'collaborative') ?>
                    </span>
                    <h4><?= htmlspecialchars($tech['name']) ?></h4>
                    <?php if (!empty($tech['purpose'])): ?>
                        <p style="font-size: 0.85rem; color: #666; margin-top: 8px;">
                            <?= mb_substr(htmlspecialchars($tech['purpose']), 0, 100) ?><?= mb_strlen($tech['purpose']) > 100 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>
                    <div style="margin-top: 10px; display: flex; gap: 8px;">
                        <a href="?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> مشاهده
                        </a>
                        <?php if ($isAdmin): ?>
                            <button class="btn btn-sm btn-danger remove-technique-btn" 
                                    data-task-id="<?= $task['id'] ?>" 
                                    data-technique-id="<?= $tech['id'] ?>"
                                    title="حذف تکنیک (فقط مدیر)">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- افزودن تکنیک جدید -->
    <?php if ($isAdmin && !empty($allTechniques)): ?>
        <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid var(--soft-light);">
            <h4 style="margin-bottom: 15px;"><i class="fas fa-plus-circle"></i> افزودن تکنیک جدید</h4>
            <div class="d-flex gap-2" style="align-items: flex-end;">
                <div style="flex: 1;">
                    <select id="newTechniqueSelect" class="form-control">
                        <option value="">انتخاب تکنیک...</option>
                        <?php foreach ($allTechniques as $tech): ?>
                            <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="addTechniqueBtn" class="btn btn-primary" data-task-id="<?= $task['id'] ?>">
                    <i class="fas fa-plus"></i> افزودن
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// حذف تکنیک از وظیفه (AJAX)
document.querySelectorAll('.remove-technique-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const taskId = this.dataset.taskId;
        const techniqueId = this.dataset.techniqueId;
        
        if (!confirm('آیا از حذف این تکنیک اطمینان دارید؟')) return;
        
        fetch('?route=tasks_remove_technique', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `task_id=${taskId}&technique_id=${techniqueId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // حذف کارت تکنیک از DOM
                const card = document.querySelector(`.technique-card[data-technique-id="${techniqueId}"]`);
                if (card) card.remove();
                alert(data.message);
            } else {
                alert(data.error || 'خطا در حذف تکنیک');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('خطا در ارتباط با سرور');
        });
    });
});

// افزودن تکنیک به وظیفه (AJAX)
const addBtn = document.getElementById('addTechniqueBtn');
if (addBtn) {
    addBtn.addEventListener('click', function() {
        const taskId = this.dataset.taskId;
        const techniqueId = document.getElementById('newTechniqueSelect').value;
        
        if (!techniqueId) {
            alert('لطفاً یک تکنیک انتخاب کنید.');
            return;
        }
        
        fetch('?route=tasks_add_technique', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `task_id=${taskId}&technique_id=${techniqueId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.error || 'خطا در افزودن تکنیک');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('خطا در ارتباط با سرور');
        });
    });
}
</script>