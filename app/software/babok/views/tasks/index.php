<?php
/**
 * ویو لیست وظایف BABOK
 * مسیر: app/software/babok/views/tasks/index.php
 */
$pageTitle = 'وظایف BABOK - BABOK Analyzer';
$activePage = 'tasks';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف استاندارد BABOK (<?= count($tasks) ?>)
        </div>
        <div class="card-tools">
            <select id="filterKnowledgeArea" class="form-control" style="width: 250px;">
                <option value="">همه حوزه‌های دانشی</option>
                <?php foreach ($knowledgeAreas as $ka): ?>
                    <option value="<?= $ka['id'] ?>"><?= htmlspecialchars($ka['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 10px;">هیچ وظیفه‌ای یافت نشد.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="tasksTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">کد</th>
                        <th>نام وظیفه</th>
                        <th>حوزه دانشی</th>
                        <th style="width: 120px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr data-ka-id="<?= $task['knowledge_area_id'] ?>">
                        <td>
                            <span class="badge badge-secondary"><?= htmlspecialchars($task['code']) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($task['name']) ?></strong>
                            <?php if (!empty($task['description'])): ?>
                                <div class="text-muted" style="font-size: 0.8rem; margin-top: 3px;">
                                    <?= mb_substr(htmlspecialchars($task['description']), 0, 80) ?><?= mb_strlen($task['description']) > 80 ? '...' : '' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?route=knowledge_areas_view&id=<?= $task['knowledge_area_id'] ?>" 
                               style="color: var(--soft-secondary); text-decoration: none;">
                                <i class="fas fa-sitemap"></i>
                                <?= htmlspecialchars($task['knowledge_area_name'] ?? '') ?>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده جزئیات">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?route=recommendations_task&id=<?= $task['id'] ?>" class="btn btn-sm btn-success" title="پیشنهاد تکنیک">
                                    <i class="fas fa-lightbulb"></i>
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

<!-- راهنمای حوزه‌های دانشی -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 class="card-title"><i class="fas fa-info-circle"></i> ساختار وظایف BABOK v3</h4>
    <p class="text-muted" style="font-size: 0.9rem;">
        استاندارد BABOK v3 شامل <strong>۶ حوزه دانشی</strong> و <strong>۲۹ وظیفه</strong> است. 
        هر وظیفه با یک کد منحصر به فرد (مانند 3.1 یا 5.2) مشخص می‌شود که رقم اول آن شماره حوزه دانشی است.
    </p>
    <div class="row" style="margin-top: 15px;">
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">📋 حوزه ۳: برنامه‌ریزی و نظارت</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 3.1 تا 3.4</p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">🤝 حوزه ۴: الهام‌گیری و همکاری</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 4.1 تا 4.5</p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">🔄 حوزه ۵: مدیریت چرخه حیات نیازمندی‌ها</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 5.1 تا 5.5</p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">🎯 حوزه ۶: تحلیل استراتژیک</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 6.1 تا 6.4</p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">📐 حوزه ۷: تحلیل و تعریف طراحی نیازمندی‌ها</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 7.1 تا 7.6</p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.9rem;">📊 حوزه ۸: ارزیابی راه‌حل</h5>
            <p class="text-muted" style="font-size: 0.8rem;">وظایف 8.1 تا 8.5</p>
        </div>
    </div>
</div>

<script>
// فیلتر وظایف بر اساس حوزه دانشی
document.getElementById('filterKnowledgeArea').addEventListener('change', function() {
    const selectedKA = this.value;
    const rows = document.querySelectorAll('#tasksTable tbody tr');
    
    rows.forEach(row => {
        if (!selectedKA || row.dataset.kaId === selectedKA) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>