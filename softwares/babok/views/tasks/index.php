<?php
$pageTitle = 'وظایف BABOK - BABOK Analyzer';
$activePage = 'tasks';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف BABOK
        </div>
        <div>
            <span class="badge badge-primary"><?= count($tasks) ?> وظیفه</span>
        </div>
    </div>

    <!-- جستجو و فیلتر -->
    <div class="d-flex gap-2 flex-wrap" style="margin-bottom: 15px;">
        <input type="text" id="searchTask" placeholder="جستجوی وظیفه..." onkeyup="filterTasks()" 
               style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; min-width: 150px;">
        <select id="filterArea" onchange="filterTasks()" 
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; min-width: 150px;">
            <option value="all">همه حوزه‌ها</option>
            <?php foreach ($knowledgeAreas as $area): ?>
                <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" onclick="resetFilters()">
            <i class="fas fa-undo"></i> حذف فیلتر
        </button>
    </div>

    <div class="table-responsive">
        <table class="table" id="tasksTable">
            <thead>
                <tr>
                    <th style="width: 80px;">کد</th>
                    <th style="width: 200px;">نام وظیفه</th>
                    <th style="width: 120px;">حوزه دانشی</th>
                    <th style="min-width: 300px;">توضیحات</th>
                    <th style="width: 120px;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr data-area="<?= $task['knowledge_area_id'] ?>" data-name="<?= strtolower($task['name']) ?>">
                    <td><strong><?= htmlspecialchars($task['code']) ?></strong></td>
                    <td><?= htmlspecialchars($task['name']) ?></td>
                    <td>
                        <span class="badge badge-primary"><?= htmlspecialchars($task['knowledge_area_code'] ?? '') ?></span>
                    </td>
                    <td>
                        <?php 
                        $description = $task['description'] ?? '';
                        if (!empty($description)) {
                            // اگر توضیحات طولانی است، با دکمه "نمایش کامل" نمایش بده
                            if (strlen($description) > 150) {
                                echo '<div style="max-height: 60px; overflow: hidden; position: relative;" class="desc-short">';
                                echo '<span>' . htmlspecialchars(substr($description, 0, 150)) . '...</span>';
                                echo '<button onclick="this.parentElement.style.maxHeight=\'none\'; this.style.display=\'none\'" 
                                        style="background: none; border: none; color: var(--secondary-color); cursor: pointer; font-size: 0.8rem; display: block; margin-top: 5px;">
                                        <i class="fas fa-chevron-down"></i> نمایش کامل
                                      </button>';
                                echo '</div>';
                                echo '<div style="display: none;" class="desc-full">' . nl2br(htmlspecialchars($description)) . '</div>';
                            } else {
                                echo nl2br(htmlspecialchars($description));
                            }
                        } else {
                            echo '<span class="text-muted">بدون توضیحات</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/babok/public/?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده جزئیات">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/babok/public/?route=recommendations_task&id=<?= $task['id'] ?>" class="btn btn-sm btn-success" title="پیشنهاد تکنیک‌ها">
                                <i class="fas fa-lightbulb"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTasks() {
    const area = document.getElementById('filterArea').value;
    const search = document.getElementById('searchTask').value.toLowerCase();
    const rows = document.querySelectorAll('#tasksTable tbody tr');
    
    rows.forEach(row => {
        const rowArea = row.dataset.area;
        const rowName = row.dataset.name;
        let show = true;
        
        if (area !== 'all' && rowArea != area) show = false;
        if (search && !rowName.includes(search)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('filterArea').value = 'all';
    document.getElementById('searchTask').value = '';
    filterTasks();
}
</script>

<style>
/* استایل برای نمایش بهتر توضیحات طولانی */
.desc-short {
    transition: max-height 0.3s ease;
}
.desc-full {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    margin-top: 5px;
    border-right: 3px solid var(--secondary-color);
}
/* استایل برای نمایش بهتر در موبایل */
@media (max-width: 768px) {
    .table th, .table td {
        padding: 8px 5px;
        font-size: 0.75rem;
    }
    .table th {
        white-space: nowrap;
    }
    .btn-sm {
        padding: 2px 6px;
        font-size: 0.7rem;
    }
}
</style>